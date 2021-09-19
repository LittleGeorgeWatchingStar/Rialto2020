<?php

namespace Rialto\Stock\Item\Version\Web;

use FOS\RestBundle\View\View;
use Rialto\Database\Orm\EntityList;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Manufacturing\Bom\Orm\BomItemRepository;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\ItemVersionTemplate;
use Rialto\Stock\Item\Version\Orm\ItemVersionRepository;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionedItemSummary;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for managing item versions.
 */
class ItemVersionController extends RialtoController
{
    /**
     * @var ItemVersionRepository
     */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(ItemVersion::class);
    }

    /**
     * @Route("/stock/item-version/", name="item_version_list")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $form = $this->createForm(VersionListFilterType::class);
        $form->submit($request->query->all());
        $filters = $form->getData();
        $versions = new EntityList($this->repo, $filters);
        return View::create(ItemVersionSummary::fromList($versions))
            ->setTemplate("stock/version/list.html.twig")
            ->setTemplateData([
                'versions' => $versions,
                'form' => $form->createView(),
            ]);
    }

    /**
     * @Route("/Stock/StockItem/{stockCode}/version", name="Stock_ItemVersion_create")
     * @Method({"GET", "POST"})
     * @Template("stock/version/create.html.twig")
     */
    public function createAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        if (!$item->isVersioned()) {
            return $this->editAction($item, Version::NONE, $request);
        }
        $template = new ItemVersionTemplate();
        $template->setStockItem($item);
        $template->loadDefaultValues();
        $form = $this->createForm(ItemVersionTemplateType::class, $template);
        $returnUri = $this->itemView($item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $version = $template->create();
            $this->dbm->persist($version);

            $notices = $template->getNotices($version);
            $template->persistNotices($notices, $this->dbm, $this->dispatcher());

            $this->dbm->flush();
            $this->logNotice(sprintf('Created item version %s successfully.',
                $version->getFullSku()
            ));

            return $this->redirect($returnUri);
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'cancelUri' => $returnUri,
            'heading' => "Create new version of $item",
        ];
    }

    private function itemView(Item $item)
    {
        return $this->generateUrl('stock_item_view', ['item' => $item->getSku()]);
    }

    /**
     * @Route("/stock/item/{item}/version/{version}/",
     *     name="item_version_edit",
     *     requirements={"version" = "[^/]*"})
     * @Method({"GET", "POST"})
     * @Template("stock/version/edit.html.twig")
     */
    public function editAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $version = $this->getVersionOrNotFound($item, $version);
        $form = $this->createForm(ItemVersionType::class, $version);
        $returnUri = $this->getCurrentUri();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice(sprintf('%s updated successfully.',
                $version->getFullSku()));
            return $this->redirect($returnUri);
        }

        return [
            'entity' => $version,
            'version' => $version,
            'form' => $form->createView(),
        ];
    }

    private function getVersionOrNotFound(StockItem $item,
                                          string $version): ItemVersion
    {
        if ($item->hasVersion($version)) {
            return $item->getVersion($version);
        }
        throw $this->notFound("$item has no such version $version");
    }

    /**
     * To deactivate a version means to declare it to be bad and unusable.
     *
     * This action is only valid for versioned items, so the $version
     * parameter cannot be blank.
     *
     * @Route("/Stock/StockItem/{stockCode}/version/{version}/active/",
     *   name="Stock_ItemVersion_deactivate")
     * @Template("stock/version/deactivate.html.twig")
     */
    public function deactivateAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        if (count($item->getActiveVersions()) == 1) {
            throw $this->badRequest(
                "Cannot deactivate the only active version of $item");
        }

        $cancelUri = $this->itemView($item);
        $itemVersion = $this->getVersionOrNotFound($item, $version);

        $options = ['version' => $itemVersion];
        $form = $this->createForm(DeactivateVersionType::class, $item, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $itemVersion->deactivate();

            $noticeList = $form->get('notices')->getData();
            $notices = $noticeList->getNotices($itemVersion);
            $dispatcher = $this->get(EventDispatcherInterface::class);
            $noticeList->persistNotices($notices, $this->dbm, $dispatcher);

            $this->dbm->flush();
            $this->logNotice(sprintf('Deactivated %s successfully.',
                $itemVersion->getFullSku()
            ));
            return $this->redirect($cancelUri);
        }

        return [
            'version' => $itemVersion,
            'form' => $form->createView(),
            'cancelUri' => $cancelUri,
        ];
    }


    /**
     * REST API method for creating a new item version.
     *
     * @Route("/api/v2/stock/item/{stockCode}/version/")
     * @Method("POST")
     *
     * @api for Geppetto Server
     */
    public function postAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        $this->errorIfNotVersioned($item);
        $options = ['csrf_protection' => false];
        $template = new ItemVersionTemplate();
        $template->setStockItem($item);
        $form = $this->createForm(ItemVersionTemplateType::class, $template, $options);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $version = $template->create();
            $this->dbm->persist($version);
            $this->dbm->flush();
            return View::create(new VersionedItemSummary($version), Response::HTTP_CREATED);
        } else {
            return JsonResponse::fromInvalidForm($form);
        }
    }

    private function errorIfNotVersioned(StockItem $item)
    {
        if (!$item->isVersioned()) {
            throw $this->badRequest("Item $item is not versioned");
        }
    }

    /**
     * Returns the BomItem that is the child board of $item.
     *
     * @Route("/Stock/StockItem/{stockCode}/version/{version}/child/",
     *   name="Stock_ItemVersion_child",
     *   defaults={"format"="json"},
     *   options={"expose"=true})
     * @Method("GET")
     */
    public function childAction(StockItem $item, $version)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $parent = $this->getVersionOrNotFound($item, $version);

        /** @var BomItemRepository $repo */
        $repo = $this->dbm->getRepository(BomItem::class);
        $child = $repo->findComponentBoard($parent);
        if ($child) {
            return View::create(new VersionedItemSummary($child));
        }
        throw $this->notFound();
    }

    /**
     * List the full SKUs of all matching versions.
     *
     * @Route("/stock/version-sku/",
     *   name="stock_version_sku",
     *   options={"expose"=true})
     * @Method("GET")
     */
    public function skusAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $params = $request->query->all();
        if (isset($params['term'])) {
            $params['matching'] = $params['term'];
        }
        $query = $this->repo->queryByFilters($params);
        $versions = $query->getResult();
        $data = array_map(function (ItemVersion $v) {
            return $v->getFullSku();
        }, $versions);
        return View::create($data);
    }
}
