<?php

namespace Rialto\Geppetto\Design\Web;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use Rialto\Geppetto\Design\Design;
use Rialto\Geppetto\Design\DesignFactory;
use Rialto\Geppetto\Design\DesignRevision;
use Rialto\Geppetto\Design\DesignRevision2;
use Rialto\Geppetto\Design\DesignStockItemFactory;
use Rialto\Madison\MadisonClient;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemDeleteService;
use Rialto\Stock\Item\StockItemSummary;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\UrlPublication;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Controller for working with Geppetto designs.
 */
class DesignController extends RialtoController
{
    /**
     * @var DesignFactory
     */
    private $factory;

    /**
     * @var DesignStockItemFactory
     */
    private $designStockItemFactory;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * @var PublicationRepository
     */
    private $publicationRepo;

    /**
     * @var MadisonClient
     */
    private $madison;

    public function __construct(DesignFactory $factory,
                                DesignStockItemFactory $designStockItemFactory,
                                EntityManagerInterface $em,
                                MadisonClient $madison)
    {
        $this->factory = $factory;
        $this->designStockItemFactory = $designStockItemFactory;
        $this->em = $em;
        $this->publicationRepo = $this->em->getRepository(Publication::class);
        $this->madison = $madison;
    }

    /**
     * Create the stock items and other records needed to support a
     * new Geppetto design/design revision.
     *
     * @Route("/api/v2/geppetto/design-revision/")
     * @Method("POST")
     */
    public function postDesignRevAction(Request $request,
                                        StockItemDeleteService $stockItemDeleteService)
    {
        $this->denyAccessUnlessGranted(Role::GEPPETTO);
        $designRevision = new DesignRevision2();
        $designRevisionForm = $this->createForm(DesignRevision2Type::class, $designRevision);
        $designRevisionForm->handleRequest($request);

        if ($designRevisionForm->isValid()) {
            $this->em->beginTransaction();
            $stockItemExists = $designRevision->getBoard() !== null;
            try {
                $designCreateResult = $this->designStockItemFactory->createStockRecords($designRevision);
                $this->em->flush();
                $this->em->commit();
            } catch (\Exception $ex) {
                $this->em->rollBack();
                throw $ex;
            }

            try {
                $storeUrl = $this->madison->createOrUpdateBoardProduct(
                    $designCreateResult->getBoard(),
                    $designRevision);
            } catch (\Exception $ex) {
                if ($stockItemExists) {
                    $stockItemDeleteService->deleteItemVersion($designCreateResult->getBoard(), $designRevision->getVersionCode());
                    foreach ($designCreateResult->getDerivativeStockItems() as $stockItem) {
                        $stockItemDeleteService->deleteItemVersion($stockItem, $designRevision->getVersionCode());
                    }

                } else {
                    $stockItemDeleteService->deleteStockItem($designCreateResult->getBoard());
                    foreach ($designCreateResult->getDerivativeStockItems() as $stockItem) {
                        $stockItemDeleteService->deleteStockItem($stockItem);
                    }
                }
                throw $ex;
            }

            return View::create([
                'sku' => $designCreateResult->getBoard()->getSku(),
                'storeUrl' => $storeUrl,
            ], Response::HTTP_CREATED);
        }
        return View::create($designRevisionForm);
    }

    /**
     * @deprecated
     * use @see postDesignRevAction
     *
     * Create the stock items and other records needed to support a
     * new Geppetto design.
     *
     * @Route("/api/v2/geppetto/design/")
     * @Method("POST")
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::GEPPETTO);
        $design = new Design();
        $form = $this->createForm(DesignType::class, $design);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->em->beginTransaction();
            try {
                $board = $this->factory->create($design);
                $this->em->flush();
                $this->em->commit();
            } catch (\Exception $ex) {
                $this->em->rollBack();
                throw $ex;
            }

            return View::create(new StockItemSummary($board), Response::HTTP_CREATED);
        }
        return View::create($form);
    }

    /**
     * @deprecated
     * use @see postDesignRevAction
     *
     * Creates a new version of an existing stock item.
     *
     * @Route("/api/v2/geppetto/design/{stockCode}/revision/")
     * @Method("POST")
     */
    public function postVersionAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::GEPPETTO);
        $revision = new DesignRevision($item);
        $form = $this->createForm(DesignRevisionType::class, $revision);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->em->beginTransaction();
            try {
                $this->factory->createRevision($revision);
                $this->em->flush();
                $this->em->commit();
            } catch (\Exception $ex) {
                $this->em->rollBack();
                throw $ex;
            }

            return View::create([], Response::HTTP_CREATED);
        }
        return JsonResponse::fromInvalidForm($form);
    }

    /**
     * @deprecated no longer using CAD stock items.
     *
     * Create a CAD stock item.
     *
     * @Route("/api/v2/geppetto/design/CAD/{stockCode}/")
     * @Method("POST")
     */
    public function putCadAction(string $stockCode, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::GEPPETTO);

        /** @var StockItemRepository $stockItemRepo */
        $stockItemRepo = $this->em->getRepository(StockItem::class);

        /** @var StockItem $boardStockItem */
        $boardStockItem = $stockItemRepo->find("BRD$stockCode");
        if (!$boardStockItem) {
            throw $this->badRequest();
        }

        /** @var StockItem $cadStockItem */
        $cadStockItem = $stockItemRepo->find("CAD$stockCode");
        if ($cadStockItem) {
            throw new ConflictHttpException("Stock item CAD$stockCode already exists.");
        }

        $design = new Design();
        $form = $this->createForm(DesignType::class, $design);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->em->beginTransaction();
            try {
                $cadStockItem = $this->factory->createCad($design, $boardStockItem);
                $this->em->flush();
                $this->em->commit();
            } catch (\Exception $ex) {
                $this->em->rollBack();
                throw $ex;
            }

            return View::create(new StockItemSummary($cadStockItem), Response::HTTP_CREATED);
        }
        return View::create($form);
    }

    /**
     * @Route("/api/v2/geppetto/design/{item}/revision/{version}/datasheet/")
     * @Method("POST")
     */
    public function postDatasheetAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::GEPPETTO);
        if (! $item->hasVersion($version)) {
            throw $this->notFound("$item has no such version $version");
        }

        $description = "Geppetto datasheet R$version";
        $pub = $this->publicationRepo->createBuilder()
            ->byItem($item)
            ->byDescription($description)
            ->isUrl()
            ->getFirstResultOrNull();
        if (! $pub) {
            $pub = new UrlPublication($item);
            $pub->setDescription($description);
        }

        $options = ['csrf_protection' => false];
        $form = $this->createNamedBuilder('DesignRevision', $pub, $options)
            ->add('datasheetUrl', UrlType::class, [
                'property_path' => 'url',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->em->persist($pub);
            $this->em->flush();

            return View::create();
        }
        return JsonResponse::fromInvalidForm($form);
    }
}
