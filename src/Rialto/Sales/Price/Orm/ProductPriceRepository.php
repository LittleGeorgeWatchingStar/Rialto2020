<?php

namespace Rialto\Sales\Price\Orm;

use Rialto\Accounting\Currency\Currency;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;

/**
 * Finds the price of a stock item.
 */
class ProductPriceRepository extends RialtoRepositoryAbstract
{
    /** @return float */
    public function findBySalesOrderDetail(SalesOrderDetail $detail)
    {
        $order = $detail->getSalesOrder();
        return $this->findHelper(
            $detail,
            $order->getSalesType(),
            $order->getCurrency()
        );
    }

    /** @return float */
    private function findHelper(
        Item $item,
        SalesType $salesType = null,
        Currency $currency = null)
    {
        $conn = $this->_em->getConnection();
        $qb = $conn->createQueryBuilder()
            ->from("Prices", 'p')
            ->select([
                'Price',
                '(TypeAbbrev = :salesType) as typeMatch',
                '(CurrAbrev = :currency) as currencyMatch',
            ])
            ->where('StockID = :stockItem');

        $params = $this->createParameterArray(
            $item,
            $salesType,
            $currency
        );
        $qb->setParameters($params);

        $qb->orderBy('typeMatch', 'desc');
        $qb->addOrderBy('currencyMatch', 'desc');

        $qb->setMaxResults(1);
        $stmt = $qb->execute();
        return $stmt->fetchColumn();
    }

    private function createParameterArray(
        Item $item,
        SalesType $salesType = null,
        Currency $currency = null)
    {
        $tyId = isset($salesType) ? $salesType->getId() : SalesType::ONLINE;
        $crId = isset($currency) ? $currency->getId() : Currency::USD;

        return [
            'stockItem' => $item->getSku(),
            'salesType' => $tyId,
            'currency' => $crId,
        ];
    }

    /** @return float */
    public function findBySalesReturnItem(SalesReturnItem $rmaItem)
    {
        $rma = $rmaItem->getSalesReturn();
        $order = $rma->getOriginalOrder();
        return $this->findHelper(
            $rmaItem,
            $order->getSalesType(),
            $order->getCurrency()
        );
    }

    /** @return float */
    public function findByItemAndSalesType(Item $item, SalesType $type = null)
    {
        return $this->findHelper($item, $type);
    }

    /**
     * Returns the existing product price record that matches
     * all of the parameters of the given one; or null if there
     * is no matching record.
     *
     * @param ProductPrice $template
     * @return ProductPrice|null
     */
    public function findExistingPrice(ProductPrice $template)
    {
        return $this->findExactMatchOrNull(
            $template->getStockItem(),
            $template->getSalesType(),
            $template->getCurrency());
    }

    /**
     * @return ProductPrice|null
     */
    private function findExactMatchOrNull(
        Item $item,
        SalesType $type,
        Currency $currency)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.stockItem  = :stockItem')
            ->andWhere('p.salesType = :salesType')
            ->andWhere('p.currency = :currency');

        $params = $this->createParameterArray($item, $type, $currency);
        $qb->setParameters($params);

        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }

    /**
     * @return ProductPrice
     */
    public function findOrCreate(
        StockItem $item,
        SalesType $type,
        Currency $currency)
    {
        $price = $this->findExactMatchOrNull($item, $type, $currency);
        return $price ?: new ProductPrice($item, $currency, $type);
    }

    /**
     * @return ProductPrice[]
     */
    public function findByStockItem(Item $item)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.stockItem = :item');
        $qb->setParameter('item', $item);

        $query = $qb->getQuery();
        return $query->getResult();
    }
}

