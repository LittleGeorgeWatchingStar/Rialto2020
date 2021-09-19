<?php

namespace Rialto\Stock\Item\Web;

use FOS\RestBundle\View\View;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Stock\Category\CategoryChange;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\ChangeNotice\Web\ChangeNoticeList;
use Rialto\Stock\ChangeNotice\Web\ChangeNoticeListType;
use Rialto\Stock\Item;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemDeleteService;
use Rialto\Stock\Item\StockItemFactory;
use Rialto\Stock\Item\StockItemSummary;
use Rialto\Stock\Item\StockItemTemplate;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Stock\Item\Version\Orm\StockItemPackageGateway;
use Rialto\Stock\Level\Orm\StockLevelStatusRepository;
use Rialto\Stock\Level\StockLevelStatus;
use Rialto\Stock\Web\StockRouter;
use Rialto\Time\Web\DateType;
use Rialto\Util\Lock\BlockingSemaphore;
use Rialto\Util\Lock\FileSemaphore;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for creating and editing stock items.
 */
class StockItemController extends RialtoController
{
    const STOCKCODE_LOCKFILE = '/tmp/rialto_stockcode_lockfile';

    /**
     * @var StockItemRepository
     */
    private $repo;

    /**
     * @var StockRouter
     */
    private $router;

    /** @var StockItemDeleteService */
    private $deleteService;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->dbm->getRepository(StockItem::class);
        $this->router = $container->get(StockRouter::class);
        $this->deleteService = $container->get(StockItemDeleteService::class);
    }

    /**
     * List and search all stock items.
     *
     * @Route("/stock/item/", name="stock_item_list")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $results = new EntityList($this->repo, $form->getData());
        return View::create(StockItemSummary::fromList($results))
            ->setTemplate("stock/item/list.html.twig")
            ->setTemplateData([
            'form' => $form->createView(),
            'items' => $results,
        ]);
    }

    /**
     * Jump to a stock item given by the "sku" query parameter.
     *
     * @Route("/stock/select-item/", name="stock_item_select")
     * @Method("GET")
     * @Template("stock/item/select.html.twig")
     */
    public function selectAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $session = $request->getSession();
        $sku = $request->get('sku', $session->get('current_stock_item'));
        if ($sku) {
            if ($this->repo->find($sku)) {
                $session->set('current_stock_item', $sku);
                return $this->redirectToRoute('stock_item_view', [
                    'item' => $sku,
                ]);
            }
            $this->logError("No such stock item $sku.");
        }
        return [];
    }

    /**
     * View a single stock item.
     *
     * @Route("/stock/item/{item}/", name="stock_item_view", options={"expose": true})
     * @Method("GET")
     */
    public function viewAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $actionsCtrl = ActionsController::class;
        return $this->summaryView($item)
            ->setTemplate("stock/item/view.html.twig")
            ->setTemplateData([
                'entity' => $item,
                'links' => "$actionsCtrl::linksAction",
            ]);
    }

    /**
     * API view of a single stock item.
     *
     * @Route("/api/v2/stock/item/{stockCode}/")
     * @Method("GET")
     *
     * @api for Geppetto Client, Madison
     */
    public function getAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        return $this->summaryView($item);
    }

    private function summaryView(StockItem $item, $statusCode = Response::HTTP_OK)
    {
        return View::create(new StockItemSummary($item), $statusCode);
    }

    /**
     * API for creating a new stock item.
     *
     * @Route("/api/v2/stock/item/")
     * @Method("POST")
     *
     * @api for Geppetto Client, Geppetto Server
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $template = new StockItemTemplate();
        $form = $this->createForm(StockItemTemplateApiType::class, $template);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $item = $this->createItemFromTemplate($template);
            return $this->summaryView($item, Response::HTTP_CREATED);
        } else {
            return JsonResponse::fromInvalidForm($form);
        }
    }

    /** @return StockItem */
    private function createItemFromTemplate(StockItemTemplate $template)
    {
        $factory = $this->itemFactory();
        $lock = new BlockingSemaphore(new FileSemaphore(self::STOCKCODE_LOCKFILE));
        if (!$lock->acquire()) {
            throw new RuntimeException("Unable to acquire stock code lock");
        }
        $this->dbm->beginTransaction();
        try {
            $item = $factory->create($template);
            $this->dbm->persist($item);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        } finally {
            $lock->release();
        }
        $this->updateStockLevels($item);
        $this->dbm->flush();
        return $item;
    }

    /** @return StockItemFactory|object */
    private function itemFactory()
    {
        return $this->get(StockItemFactory::class);
    }

    /**
     * Creates stock level records for a new stock item.
     */
    private function updateStockLevels(StockItem $stockItem)
    {
        if (!$stockItem instanceof PhysicalStockItem) {
            return;
        }
        /** @var $statusRepo StockLevelStatusRepository */
        $statusRepo = $this->dbm->getRepository(StockLevelStatus::class);
        $statusRepo->initializeStockLevels($stockItem);
    }

    /**
     * Menu for choosing which type of stock item to create.
     *
     * @Route("/stock/new-item/", name="stock_item_create")
     * @Template("stock/item/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        return [];
    }

    /**
     * @Route("/stock/part-item/", name="stock_item_create_part")
     * @Template("stock/item/create-part.html.twig")
     */
    public function createPartAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        return [
            'package' => $request->query->get('package'),
            'partValue' => $request->query->get('partValue'),
        ];
    }

    /**
     * @Route("/stock/manual-item/", name="stock_item_create_manually")
     * @Template("stock/item/create-manually.html.twig")
     */
    public function createManuallyAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $template = new StockItemTemplate();
        /* Prepopulate the form using query string parameters. This is needed
         * to guide users who might not know the correct values for some
         * immutable fields (eg, MBflag). */
        $prepopulate = $this->createForm(StockItemTemplateType::class, $template, [
            'method' => 'get',
        ]);
        $prepopulate->submit($request->query->all());

        $form = $this->createForm(StockItemTemplateType::class, $template);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $item = $this->createItemFromTemplate($template);
            $this->logNotice("Created item $item successfully.");
            return $this->redirectToItem($item);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function redirectToItem(Item $item)
    {
        $url = $this->router->itemView($item);
        return $this->redirect($url);
    }

    /**
     * Web form for editing an existing stock item.
     *
     * @Route("/Stock/StockItem/{stockCode}", name="Stock_StockItem_edit")
     * @Template("stock/item/edit.html.twig")
     */
    public function editAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createForm(EditType::class, $item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice("$item has been modified successfully.");
            return $this->redirectToItem($item);
        }
        return [
            'form' => $form->createView(),
            'stockItem' => $item,
        ];
    }

    /**
     * Action to change the stock category of an existing item.
     *
     * @Route("/Stock/StockItem/{stockCode}/category", name="Stock_StockItem_category")
     * @Template("form/minimal.html.twig")
     */
    public function categoryAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $data = ['category' => $item->getCategory()];
        $form = $this->createFormBuilder($data)
            ->setAction($this->getCurrentUri())
            ->add('category', EntityType::class, [
                'class' => StockCategory::class,
                'choice_label' => 'name',
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $returnUri = $this->redirectToItem($item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->get('category')->getData();
            $change = $this->get(CategoryChange::class);
            $transaction = $change->changeCategory($item, $category);
            if (null !== $transaction) {
                $this->dbm->persist($transaction);
            }
            $this->dbm->flush();
            $this->logNotice(sprintf('%s is now a %s.',
                $item->getId(),
                $item->getCategory()->getName()));
            return JsonResponse::javascriptRedirect($returnUri);
        }

        return [
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * Create a new stock item from an existing one.
     *
     * @Route("/Stock/StockItem/{stockCode}/clone/",
     *   name="Stock_StockItem_clone")
     * @Template("stock/item/clone.html.twig")
     */
    public function cloneAction(StockItem $source, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $clone = new StockItemClone($source);
        $form = $this->createForm(StockItemCloneType::class, $clone);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newItem = $clone->createClone();
            $this->dbm->persist($newItem);
            $this->dbm->flush();
            $this->logNotice("$newItem created successfully.");
            return $this->redirectToItem($newItem);
        }

        return [
            'source' => $source,
            'form' => $form->createView(),
        ];
    }


    /**
     * Create a packing item for a board.
     *
     * @Route("/stock/item/{stockCode}/package/",
     *   name="stock_item_create_package")
     * @Template("stock/item/package.html.twig")
     */
    public function packageAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        if (!$item->isBoard()) {
            throw $this->badRequest();
        }
        $packager = new StockItemPackage($item);
        $packager->loadComponents(new StockItemPackageGateway($this->dbm));

        $form = $this->createForm(StockItemPackageType::class, $packager);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parent = $packager->createPackage();
            $this->dbm->persist($parent);
            $this->updateStockLevels($parent);
            $this->dbm->flush();
            $this->logNotice("Created $parent successfully.");
            return $this->redirectToItem($parent);
        }

        /** @var $versionRepo ItemVersionRepository */
        $versionRepo = $this->getRepository(ItemVersion::class);

        return [
            'item' => $item,
            'packager' => $packager,
            'form' => $form->createView(),
            'existing' => $versionRepo->findByComponent($item),
        ];
    }


    /**
     * Where the user sets the phase-out date of $item and optionally
     * records a change notice.
     *
     * @Route("/Stock/StockItem/{stockCode}/phaseOut/",
     *   name="Stock_StockItem_phaseOut")
     * @Template("form/minimal.html.twig")
     */
    public function phaseOutAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createFormBuilder($item)
            ->setAction($this->getCurrentUri())
            ->add('phaseOut', DateType::class, [
                'label' => 'Phase-out date',
                'required' => false,
            ])
            ->add('noticeList', ChangeNoticeListType::class, [
                'label' => 'You may optionally add change notices to this phase-out:',
                'label_attr' => ['class' => 'section'],
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $noticeList = $form->get('noticeList')->getData();
            assert($noticeList instanceof ChangeNoticeList);
            $this->dbm->beginTransaction();
            try {
                $notices = $noticeList->getNotices($item);
                $noticeList->persistNotices($notices, $this->dbm, $this->dispatcher());
                $this->dbm->flushAndCommit();

                $this->logNotice("Phase-out date for $item set successfully.");
                $uri = $this->redirectToItem($item);
                return JsonResponse::javascriptRedirect($uri);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/api/v2/stock/item/{item}/", name="stock_item_delete")
     * @Method("DELETE")
     *
     * @api for Geppetto Server
     */
    public function deleteAction(StockItem $item)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        if (!$item->isCategory(StockCategory::MODULE)) {
            throw $this->badRequest("Only modules can be deleted at this time");
        }

        $this->deleteService->deleteStockItem($item);

        return Response::create();
    }
}
