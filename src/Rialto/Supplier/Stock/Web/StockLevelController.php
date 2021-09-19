<?php

namespace Rialto\Supplier\Stock\Web;

use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Level\CompleteStockLevel;
use Rialto\Supplier\Web\SupplierController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows the CM to view stock levels at their facility.
 *
 * @Route("/supplier")
 */
class StockLevelController extends SupplierController
{
    const ACTIVE_TAB = 'stocklevel';

    private $stockItemRepo;

    private $purchasingDataRepo;

    /**
     * Show stock levels by SKU at the supplier's facility.
     *
     * @Route("/{id}/stock-level/", name="supplier_stock_level")
     * @Template("supplier/stock/levels.html.twig")
     */
    public function listAction(Supplier $supplier, Request $request)
    {
        $this->checkDashboardAccess($supplier);

        $form = $this->createForm(StockLevelFilterType::class);
        $form->submit($request->query->all());
        $filters = $form->getData();

        $builder = CompleteStockLevel::createBuilder($this->manager())
            ->byFacility($supplier->getFacility())
            ->isInStock()
            ->groupByVersion()
            ->orderBySku();
        if ($filters['sku'] ?? null) {
            $builder->bySkuSubstring($filters['sku']);
        }
        $levels = $builder->getStockLevels();

        $this->stockItemRepo = $this->dbm->getRepository(StockItem::class);
        $this->purchasingDataRepo = $this->dbm->getRepository(PurchasingData::class);

        $itemsToPurchasingData = [];

        foreach ($levels as $level) {
            $sku = $level->getSku();

            $query = $this->stockItemRepo->createQueryBuilder('si')
                ->andWhere('si.stockCode = :sku')
                ->setParameter('sku', $sku);

            /** @var StockItem $selectedBySKU */
            $selectedBySKU = $query->getQuery()->getOneOrNullResult();
            $itemsToPurchasingData[$level->getSku()] = $selectedBySKU->getPreferredPurchasingData();
        }
        return [
            'levels' => $levels,
            'form' => $form->createView(),
            'supplier' => $supplier,
            'facility' => $supplier->getFacility(),
            'activeTab' => self::ACTIVE_TAB,
            'itemsToPD' => $itemsToPurchasingData,
        ];
    }

}
