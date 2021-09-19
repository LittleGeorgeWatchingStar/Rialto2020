<?php

namespace Rialto\Stock\Transfer\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Shipping\Method\Orm\ShippingMethodRepository;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Transfer;

class TransferRepository
    extends FilteringRepositoryAbstract
    implements AccountingEventRepository
{
    const HAND_CARRIED = "HAND";
    const TRUCK = '1';

    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('transfer');
        $builder->leftJoin('transfer.lineItems', 'item');
        $builder->add('id', function (QueryBuilder $qb, $id) {
            $qb->andWhere('transfer.id = :id')
                ->setParameter('id', $id);
            return true; // Don't process any more filters.
        });
        $builder->add('destination', function (QueryBuilder $qb, $dest) {
            $qb->andWhere('transfer.destination = :destinationID')
                ->setParameter('destinationID', $dest);
        });
        $builder->add('startDate', function (QueryBuilder $qb, $startDate) {
            $qb->andWhere('transfer.dateShipped >= :startDate')
                ->setParameter('startDate', $startDate);
        });
        $builder->add('received', function (QueryBuilder $qb, $received) {
            if ($received == 'yes') {
                $qb->andWhere('transfer.dateReceived is not null');
            } elseif ($received == 'no') {
                $qb->andWhere('transfer.dateReceived is null');
            }
        });
        $builder->add('missingItems', function (QueryBuilder $qb, $missingItems) {
            if ($missingItems) {
                $qb->andWhere('transfer.dateReceived is not null')
                    ->andWhere('item.dateReceived is null');
            }
        });
        $builder->add('bin', function (QueryBuilder $qb, $binId) {
            $qb->andWhere('item.stockBin = :binId')
                ->setParameter('binId', $binId);
        });

        return $builder->buildQuery($params);
    }

    /** @return TransferQueryBuilder */
    public function createBuilder()
    {
        return new TransferQueryBuilder($this);
    }

    /**
     * @todo Should return unique result.
     * @return Transfer[]
     */
    public function findByType(SystemType $sysType, $typeNo)
    {
        return $this->findBy(['id' => $typeNo]);
    }

    /** @return boolean */
    public function hasMissingTransferItems(PurchaseOrder $order)
    {
        $count = $this->createBuilder()
            ->forPurchaseOrder($order)
            ->hasMissingItems()
            ->getRecordCount();
        return $count > 0;
    }

    /**
     * Filter the query builder to select transfers that were created
     * for the purchase order whose ID is given.
     *
     * @param QueryBuilder $qb
     * @param int|string $poId
     * @param string $transferAlias
     * @param string $binAlias
     */
    public static function selectByPurchaseOrderId(QueryBuilder $qb,
                                            $poId,
                                            $transferAlias = 'transfer',
                                            $binAlias = 'bin')
    {
        // transfers that are directly linked to the PO
        $qb->leftJoin("$transferAlias.purchaseOrders", 'po')
            // and transfers that are indirectly linked via allocations
            ->leftJoin("$binAlias.allocations", 'alloc')
            ->leftJoin(Requirement::class, 'woReq', Join::WITH,
                'alloc.requirement = woReq')
            ->leftJoin('woReq.workOrder', 'wo')

            ->andWhere('(wo.purchaseOrder = :id or po.id = :id)')
            ->setParameter('id', $poId);
    }

    public function findTransferRequiresTrackingNumber(Facility $origin, array $shippingMethods)
    {
        return $this->createBuilder()
            ->byOrigin($origin)
            ->kitted()
            ->notHasTrackingNumber()
            ->notSent()
            ->isTrackingNumberRequired($shippingMethods)
            ->notReceived()
            ->getResult();
    }

    /**
     * @param Facility $destination
     * @return Transfer[]
     */
    public function findOutstandingByDestination(Facility $destination)
    {
        return $this->createBuilder()
            ->sent()
            ->notReceived()
            ->byDestination($destination)
            ->getResult();
    }

    public function countOutstandingByDestination(Facility $destination)
    {
        return $this->createBuilder()
            ->sent()
            ->notReceived()
            ->byDestination($destination)
            ->getRecordCount();
    }

    /**
     * Find an unpopulated transfer (ie, no items) from $origin to $dest
     * (via $inTransit) or create a new one.
     *
     * @return Transfer
     */
    public function findEmptyOrCreate(Facility $origin, Facility $dest)
    {
        $transfer = $this->findEmpty($origin, $dest);
        if (!$transfer) {
            $transfer = Transfer::fromLocations($origin, $dest);
        }
        return $transfer;
    }

    private function findEmpty(Facility $origin, Facility $dest)
    {
        return $this->createBuilder()
            ->byOrigin($origin)
            ->byDestination($dest)
            ->notKitted()
            ->isEmpty()
            ->getFirstResultOrNull();
    }

    public function hasUnsentTransfers(PurchaseOrder $order)
    {
        $qb = $this->createQueryBuilder('transfer');
        $qb->select('count(transfer.id)')
            ->join('transfer.purchaseOrders', 'po')
            ->andWhere('po = :po')
            ->setParameter('po', $order)
            ->andWhere('transfer.dateShipped is null');
        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param Facility $destination
     * @return Transfer
     */
    public function findAwaitingPickup(Facility $dest)
    {
        return $this->createBuilder()
            ->byDestination($dest)
            ->kitted()
            ->notSent()
            ->notReceived()
            ->getResult();
    }
}
