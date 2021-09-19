<?php

namespace Rialto\Stock\Publication\Orm;

use Gumstix\Doctrine\HighLevelFilter;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\UploadPublication;
use Rialto\Stock\Publication\UrlPublication;

class PublicationRepository extends FilteringRepositoryAbstract
{
    /** @return Publication[] */
    public function queryByFilters(array $params)
    {
        $filter = new HighLevelFilter($this->createBuilder());
        $filter->add('stockItem', function (PublicationQueryBuilder $qb, $item) {
            $qb->byItem($item);
        });
        $filter->add('purpose', function (PublicationQueryBuilder $qb, $purpose) {
            $qb->byPurpose($purpose);
        });
        return $filter->buildQuery($params);
    }

    public function createBuilder()
    {
        return new PublicationQueryBuilder($this);
    }

    /** @return UploadPublication[] */
    public function findBySalesOrder(SalesOrder $order)
    {
        return $this->createBuilder()
            ->bySalesOrder($order)
            ->isUpload()
            ->byPurpose(Publication::PURPOSE_SHIP)
            ->getResult();
    }

    /** @return UploadPublication[] */
    public function findByWorkOrder(WorkOrder $wo)
    {
        return $this->createBuilder()
            ->byItem($wo)
            ->isUpload()
            ->byPurpose(Publication::PURPOSE_BUILD)
            ->getResult();
    }

    /** @return UrlPublication|null */
    public function findSpecSheet(StockItem $item)
    {
        return $this->createBuilder()
            ->byItem($item)
            ->isUrl()
            ->byPurpose(Publication::PURPOSE_PUBLIC)
            ->getFirstResultOrNull();
    }


    /**
     * @return Publication[]
     */
    public function findAllByItem(StockItem $item)
    {
        return $this->createBuilder()
            ->byItem($item)
            ->getResult();
    }
}
