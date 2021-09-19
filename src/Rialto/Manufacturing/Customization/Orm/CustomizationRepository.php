<?php

namespace Rialto\Manufacturing\Customization\Orm;

use Gumstix\Doctrine\HighLevelFilter;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Quotation\QuotationRequestItem;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Bin\StockBin;

class CustomizationRepository extends FilteringRepositoryAbstract
{
    public function createBuilder()
    {
        return new CustomizationQueryBuilder($this);
    }

    public function queryByFilters(array $params)
    {
        $filter = new HighLevelFilter($this->createBuilder());
        $filter->add('name', function (CustomizationQueryBuilder $qb, $name) {
            $qb->byName($name);
        });
        $filter->add('sku', function (CustomizationQueryBuilder $qb, $sku) {
            $qb->bySku($sku);
        });
        $filter->add('substitution', function (CustomizationQueryBuilder $qb, $sub) {
            $qb->bySubstitution($sub);
        });
        $filter->add('_order', function (CustomizationQueryBuilder $qb, $field) {
            $qb->orderBy($field);
        });
        return $filter->buildQuery($params);
    }

    /**
     * @return bool True if the customization has been used anywhere.
     */
    public function isUsed(Customization $cmz)
    {
        static $related = [
            Requirement::class => 'customization',
            WorkOrder::class => 'customization',
            SalesOrderDetail::class => 'customization',
            StockBin::class => 'customization',
            QuotationRequestItem::class => 'customization',
        ];
        foreach ($related as $class => $field) {
            $count = $this->_em->createQueryBuilder()
                ->select('count(entity)')
                ->from($class, 'entity')
                ->where("entity.$field = :cmz")
                ->setParameter('cmz', $cmz)
                ->getQuery()
                ->getSingleScalarResult();
            if ($count > 0) {
                return true;
            }
        }
        return false;
    }
}
