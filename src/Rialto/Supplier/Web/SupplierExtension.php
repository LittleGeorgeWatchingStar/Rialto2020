<?php

namespace Rialto\Supplier\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Twig\Extension\AbstractExtension;

class SupplierExtension extends AbstractExtension
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getFilters()
    {
        return [
            new \Twig_Filter('elapsed_time', [$this, 'elapsedTime']),
            new \Twig_Filter('overdue', [$this, 'overdue'], [
                'is_safe' => ['html'],
            ]),
            new \Twig_Filter('elapsedTimeForManufacturingDashboard', [$this, 'elapsedTimeForManufacturingDashboard']),
            new \Twig_Filter('overdueForManufacturingDashboard', [$this, 'overdueForManufacturingDashboard'], [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function elapsedTime(\DateTime $since = null)
    {
        if (! $since ) {
            return '';
        }

        $now = new \DateTime();
        $diff = $now->diff($since);
        if ( $diff->y > 0) {
            return $diff->format('%y years');
        } elseif ( $diff->m > 0) {
            return $diff->format('%m months');
        } elseif ( $diff->d > 0) {
            return $diff->format('%d days');
        } elseif ( $diff->h > 0) {
            return $diff->format('%h hours');
        } elseif ( $diff->m > 0) {
            return $diff->format('%i minutes');
        } else {
            return 'just now';
        }
    }

    /**
     * @deprecated
     * use @see overdueForManufacturingDashboard
     */
    public function overdue(\DateTime $since = null, $limit)
    {
        if (! $since ) {
            return '';
        }
        $limit = new \DateTime($limit);
        $elapsed = $this->elapsedTime($since);
        $class = 'elapsed';
        if ($since < $limit) {
            $class .= ' overdue';
        }
        return sprintf('<span class="%s">%s</span>', $class, $elapsed);
    }

    public function elapsedTimeForManufacturingDashboard(\DateTime $since = null)
    {
        if (! $since ) {
            return '';
        }

        $now = new \DateTime();
        $diff = $now->diff($since);
        if ( $diff->y > 0) {
            return $diff->format('%y y');
        } elseif ( $diff->m > 0) {
            return $diff->format('%m m');
        } elseif ( $diff->d > 0) {
            return $diff->format('%d d');
        } elseif ( $diff->h > 0) {
            return $diff->format('%h h');
        } elseif ( $diff->m > 0) {
            return $diff->format('%i m');
        } else {
            return 'just now';
        }
    }

    public function overdueForManufacturingDashboard(\DateTime $since = null, $limit)
    {
        if (! $since ) {
            return '';
        }
        $limit = new \DateTime($limit);
        $elapsed = $this->elapsedTimeForManufacturingDashboard($since);
        $class = 'elapsed';
        if ($since < $limit) {
            $class .= ' overdue';
        }
        return sprintf('<span class="%s">%s</span>', $class, $elapsed);
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('num_open_orders',
                [$this, 'numOpenOrders']
            ),
            new \Twig_Function('num_open_transfers',
                [$this, 'numOpenTransfers']
            ),
            new \Twig_Function('num_incoming_orders',
                [$this, 'numIncomingOrders']
            ),
            new \Twig_Function('allocation_details',
                [$this, 'getAllocationDetails']
            ),
        ];
    }

    public function numOpenOrders(Supplier $supplier, $rework)
    {
        return $this->orderRepo()->countBySupplier($supplier, $rework);
    }

    /** @return PurchaseOrderRepository */
    private function orderRepo()
    {
        return $this->om->getRepository(PurchaseOrder::class);
    }

    public function numOpenTransfers(Supplier $supplier)
    {
        return $this->transferRepo()->countOutstandingByDestination($supplier->getFacility());
    }

    /** @return TransferRepository */
    private function transferRepo()
    {
        return $this->om->getRepository(Transfer::class);
    }

    public function numIncomingOrders(Supplier $supplier)
    {
        return $this->orderRepo()->countOpenByDeliveryLocation($supplier->getFacility());
    }

    /**
     * Show the customer name, if it's for a sales order, or the manufacturer
     * name, if it's for a work order.
     * @return string
     */
    public function getAllocationDetails(StockAllocation $alloc)
    {
        $consumer = $alloc->getConsumer();
        if ( $consumer instanceof SalesOrderDetail ) {
            $order = $consumer->getSalesOrder();
            return sprintf('%s / %s',
                $order->getBillingName(),
                $order->getBillingCompany());
        } elseif ( $consumer instanceof WorkOrder ) {
            return $consumer->getLocation()->getName();
        }
        return '';  /* Don't need to render MissingStockConsumer. */
    }
}
