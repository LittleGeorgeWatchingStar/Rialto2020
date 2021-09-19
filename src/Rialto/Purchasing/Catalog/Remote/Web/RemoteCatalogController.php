<?php

namespace Rialto\Purchasing\Catalog\Remote\Web;

use Rialto\PcbNg\Command\CreateManufacturedStockItemPcbNgPurchasingDataCommand;
use Rialto\PcbNg\Service\PcbNgClient;
use Rialto\Port\CommandBus\CommandQueue;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\Remote\CatalogResult;
use Rialto\Purchasing\Catalog\Remote\OctopartCatalog;
use Rialto\Purchasing\Catalog\Remote\OctopartQuery;
use Rialto\Security\Role\Role;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Web\StockRouter;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * For interacting with supplier catalog APIs.
 */
class RemoteCatalogController extends RialtoController
{
    /**
     * @var StockRouter
     */
    private $router;

    public function __construct(StockRouter $router)
    {
        $this->router = $router;
    }

    /**
     * Create a new stock item by querying Octopart and using that info.
     *
     * @Route("/stock/item-from-catalog/", name="stock_item_from_catalog")
     * @Template("stock/item/from-catalog.html.twig")
     */
    public function createStockItem(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        /** @var $referer string */
        $referer = $request->headers->get('referer');
        $allowRefer = $request->get('allowRefer', 'yes') == 'yes';

        $query = new OctopartQuery();
        $queryForm = $this->createForm(OctopartQueryType::class, $query);
        $queryForm->submit($request->query->all());
        if (!$queryForm->isValid()) {
            $this->logErrors($queryForm->getErrors());
            return $referer && $allowRefer ?
                $this->redirect($referer) :
                $this->redirectToRoute('stock_item_create');
        }

        /** @var $octopart OctopartCatalog */
        $octopart = $this->get(OctopartCatalog::class);
        $results = $octopart->findMatchingItems($query);
        if (count($results) != 1) {
            $msg = count($results) == 0
                ? "Your query matched no results."
                : "Your query matched multiple results. Please enter a more specific query.";
            $this->logError($msg);
            return $referer && $allowRefer ?
                $this->redirect($referer) :
                $this->redirectToRoute('stock_item_create');
        }
        /** @var $result CatalogResult */
        $result = reset($results);
        $result->loadExisting($this->dbm);
        /** @var $category StockCategory */
        $category = $this->getEntityFromRequest(StockCategory::class, 'category', $request);
        if ($category) {
            $result->setCategory($category);
        }
        /** @var $partValue string */
        $partValue = $request->query->get('partValue', '');
        if ($partValue) {
            $result->getItem()->setPartValue($partValue);
        }
        /** @var $package string */
        $package = $request->query->get('package', '');
        if ($package) {
            $result->getItem()->setPackage($package);
        }

        $createForm = $this->createForm(CatalogResultType::class, $result);

        $createForm->handleRequest($request);
        if ($createForm->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $result->persistAll($this->dbm);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice("Created $result successfully.");
            return $this->redirectToItem($result->getItem());
        }
        return [
            'queryForm' => $queryForm->createView(),
            'createForm' => $createForm->createView(),
            'result' => $result,
        ];
    }

    private function redirectToItem(Item $item)
    {
        $url = $this->router->itemView($item);
        return $this->redirect($url);
    }

    /**
     * Create new purchasing data for an existing stock item by querying
     * Octopart and using that info.
     *
     * @Route("/stock/item/{id}/catalog-purchasing-data/", name="stock_item_new_catalog_data")
     * @Template("stock/item/new-purchasing-data-from-catalog.html.twig")
     */
    public function createForStockItem(PurchasedStockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $result = new CatalogResult($item);
        $createForm = $this->createForm(ExistingCatalogResultType::class, $result);

        $query = new OctopartQuery();
        $queryForm = $this->createForm(OctopartQueryType::class, $query);
        $queryForm->submit($request->query->all());
        if (!$queryForm->isValid()) {
            return [
                'item' => $item,
                'queryForm' => $queryForm->createView(),
                'createForm' => $createForm->createView(),
                'result' => $result,
            ];
        }

        /** @var $octopart OctopartCatalog */
        $octopart = $this->get(OctopartCatalog::class);
        $results = $octopart->findMatchingItems($query, $item);
        if (count($results) != 1) {
            $msg = count($results) == 0
                ? "Your query matched no results."
                : "Your query matched multiple results. Please enter a more specific query.";
            $this->logError($msg);
            return [
                'item' => $item,
                'queryForm' => $queryForm->createView(),
                'createForm' => $createForm->createView(),
                'result' => $result,
            ];
        }
        /** @var $result CatalogResult */
        $result = reset($results);
        $result->loadExisting($this->dbm);

        $createForm = $this->createForm(ExistingCatalogResultType::class, $result);

        $createForm->handleRequest($request);
        if ($createForm->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $result->persistAll($this->dbm);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice("Created purchasing data for $item.");
            return $this->redirectToItem($result->getItem());
        }
        return [
            'item' => $item,
            'queryForm' => $queryForm->createView(),
            'createForm' => $createForm->createView(),
            'result' => $result,
        ];
    }

    /**
     * @Route("/stock/item/{id}/pcb-ng-purchasing-data/",
     *     name="stock_item_new_pcb_ng_purch_data")
     * @Template("stock/item/pcb-ng_purchasing-data-status.html.twig")
     */
    public function createFromPcbNgForStockItem(ManufacturedStockItem $item,
                                                PurchasingDataRepository $purchasingDataRepository,
                                                PcbNgClient $pcbNgClient,
                                                CommandQueue $commandQueue,
                                                Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        $itemVersion = $item->getAutoBuildVersion();

        $command = new CreateManufacturedStockItemPcbNgPurchasingDataCommand(
            $item->getSku(),
            $itemVersion->getVersionCode());

        if ($request->isMethod('POST')) {
            $prevJob = $commandQueue->findRecentJobForCommand($command);
            if ($prevJob && !$prevJob->isInFinalState()) {
                throw $this->badRequest('Purchasing data is still generating from previous request.');
            }

            $commandQueue->queue($command);
        }

        $job = $commandQueue->findRecentJobForCommand($command);

        if (!$job) {
            throw $this->notFound();
        }

        if ($job->isFinished()) {
            $purchData = $purchasingDataRepository->findPreferredBySupplierAndVersion(
                $pcbNgClient->getPcbNgSupplier(),
                $item,
                $itemVersion);

            if (!$purchData) {
                throw $this->notFound();
            }

            return $this->redirectToRoute('purchasing_data_edit', [
                'id' => $purchData->getId(),
            ]);
        }

        return [
            'item' => $item,
            'itemVersion' => $itemVersion,
            'job' => $job,
        ];
    }

}
