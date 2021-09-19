<?php

namespace Rialto\Sales\Stats;

use DateTime;
use Doctrine\ORM\EntityManager;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\AssemblyStockItem;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\ItemIndex;


/**
 * Database mapper for the SalesStat class.
 */
class SalesStatMapper
{
    /** @var EntityManager */
    private $em;

    /** @var Facility */
    private $location;

    public function __construct(DoctrineDbManager $dbm, Facility $location)
    {
        $this->em = $dbm->getEntityManager();
        $this->location = $location;
    }

    /** @return SalesStat[] */
    public function findByOptions(SalesStatOptions $options)
    {
        $stats = $this->findByDateAndType(
            $options->getStartDate(),
            $options->getSalesType());
        foreach ($stats as $stat) {
            $options->configureStat($stat);
        }
        return $stats;
    }


    /** @return SalesStat[] */
    public function findByDateAndType(DateTime $startDate, SalesType $salesType = null)
    {
        $startDate->setTime(0, 0, 0);
        $index = new ItemIndex();

        $results = $this->loadPhysicalItems($startDate, $salesType);
        $this->addResultsToIndex($results, $index);

        $results = $this->loadAssemblyComponents($startDate, $salesType);
        $this->addResultsToIndex($results, $index);

        $index->sort();
        return $index->toArray();
    }

    private function addResultsToIndex(array $results, ItemIndex $index)
    {
        foreach ($results as $result) {
            $item = $result[0];
            $stat = $index->get($item);
            if (!$stat) {
                $stat = new SalesStat($item, $this->location);
                $index->add($stat);
            }
            $stat->addQtyShipped($result['qtyInvoiced']);
            $stat->addQtyBacklog($result['backlog']);
        }
    }

    private function loadPhysicalItems(DateTime $startDate, SalesType $salesType = null)
    {
        $purchased = PurchasedStockItem::class;
        $manufactured = ManufacturedStockItem::class;
        $qb = $this->em->createQueryBuilder();
        $qb->select('item')
            ->addSelect('sum(sod.qtyInvoiced) as qtyInvoiced')
            ->addSelect('sum(IF(sod.completed > 0, 0, sod.qtyOrdered - sod.qtyInvoiced)) as backlog')
            ->from(StockItem::class, 'item')
            ->where("item instance of $purchased or item instance of $manufactured")
            ->join(SalesOrderDetail::class, 'sod', "WITH",
                'sod.stockItem = item')
            ->join('sod.salesOrder', 'so')
            ->andWhere('so.dateOrdered >= :startDate')
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->andWhere('so.salesStage = :salesStage')
            ->setParameter('salesStage', SalesOrder::ORDER)
            ->groupBy('item.stockCode')
            ->orderBy('item.stockCode');
        if ($salesType) {
            $qb->andWhere('so.salesType = :type')
                ->setParameter('type', $salesType->getId());
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }

    private function loadAssemblyComponents(DateTime $startDate, SalesType $salesType = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('item')
            ->addSelect('sum(sod.qtyInvoiced * bomItem.quantity) as qtyInvoiced')
            ->addSelect('sum(IF(sod.completed > 0, 0, (sod.qtyOrdered - sod.qtyInvoiced) * bomItem.quantity)) as backlog')
            ->from(StockItem::class, 'item')
            ->join(BomItem::class, 'bomItem', 'WITH',
                'bomItem.component = item')
            ->join('bomItem.parent', 'version')
            ->join(AssemblyStockItem::class, 'assembly', 'WITH',
                'version.stockItem = assembly')
            ->join(SalesOrderDetail::class, 'sod', "WITH",
                'sod.stockItem = assembly and ' .
                "version.version = IF(sod.version = :any, assembly.shippingVersion, sod.version)")
            ->setParameter('any', Version::ANY)
            ->join('sod.salesOrder', 'so')
            ->andWhere('so.dateOrdered >= :startDate')
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->andWhere('so.salesStage = :salesStage')
            ->setParameter('salesStage', SalesOrder::ORDER)
            ->groupBy('item.stockCode')
            ->orderBy('item.stockCode');
        if ($salesType) {
            $qb->andWhere('so.salesType = :type')
                ->setParameter('type', $salesType->getId());
        }

        $query = $qb->getQuery();
        return $query->getResult();
    }
}
