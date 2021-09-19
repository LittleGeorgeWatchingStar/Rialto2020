<?php

namespace Rialto\Manufacturing\WorkOrder;

use ArrayIterator;
use Countable;
use DateTime;
use IteratorAggregate;
use Rialto\Allocation\Status\RequirementStatus;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Facility\Facility;

class WorkOrderCollection implements IteratorAggregate, Countable
{
    /** @var WorkOrder[] */
    protected $members;

    /**
     * Factory method
     */
    public static function fromWorkOrder(WorkOrder $wo)
    {
        return new self([$wo]);
    }

    /**
     * Factory method
     */
    public static function fromPurchaseOrder(PurchaseOrder $po)
    {
        return new self($po->getWorkOrders());
    }

    protected function __construct(array $workOrders)
    {
        $this->members = $workOrders;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->members);
    }

    public function count()
    {
        return count($this->members);
    }

    /**
     * The IDs of every work order in the collection. Useful for DB queries.
     * @return int[]
     */
    public function getIDs(): array
    {
        $ids = [];
        foreach ($this->members as $member) {
            $id = $member->getId();
            $ids[$id] = $id;
        }
        return $ids;
    }

    public function __toString()
    {
        return sprintf('work orders %s', join(', ', $this->getIDs()));
    }

    /**
     * The allocation status for all work orders in the collection.
     */
    public function getAllocationStatus(): RequirementStatus
    {
        $status = new RequirementStatus($this->getLocation());
        foreach ($this->members as $wo) {
            foreach ($wo->getRequirements() as $woReq) {
                if ($woReq->isProvidedByChild()) {
                    continue;
                }
                $status->addRequirement($woReq);
            }
        }
        return $status;
    }

    private function getLocation(): Facility
    {
        foreach ($this->members as $wo) {
            return $wo->getLocation();
        }
        throw new \LogicException("empty work order collection");
    }

    public function hasReworkOrder(): bool
    {
        foreach ($this->members as $wo) {
            if ($wo->isRework()) {
                return true;
            }
        }
        return false;
    }

    /**
     * True if any of the work orders is a turnkey build.
     */
    public function hasTurnkeyBuild(): bool
    {
        foreach ($this->members as $wo) {
            if ($wo->isTurnkey()) {
                return true;
            }
        }
        return false;
    }

    /**
     * True if any member of the collection has been issued.
     */
    public function isIssued(): bool
    {
        foreach ($this->members as $member) {
            if ($member->isIssued()) {
                return true;
            }
        }
        return false;
    }

    public function isFullyIssued(): bool
    {
        foreach ($this->members as $member) {
            if (! $member->isFullyIssued()) {
                return false;
            }
        }
        return true;
    }

    public function isClosed(): bool
    {
        foreach ($this->members as $member) {
            /* @var $member WorkOrder */
            if (! $member->isClosed()) {
                return false;
            }
        }
        return true;
    }

    public function canBeReceived(): bool
    {
        foreach ($this->members as $wo) {
            if ($wo->hasChild()) {
                continue;
            } elseif ($wo->canBeReceived()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return PurchaseOrder[] A list of POs being shipped to $destination that
     *   will deliver parts required for this build.
     */
    public function getOutstandingPOs(Facility $destination): array
    {
        $filter = function (StockProducer $producer) use ($destination) {
            $po = $producer->getPurchaseOrder();
            $deliveryLocation = $po->hasSupplier() ? $po->getDeliveryLocation() : null;
            return $destination->equals($deliveryLocation);
        };
        $orders = [];
        foreach ($this->getOutstandingProducers($filter) as $p) {
            $id = $p->getOrderNumber();
            $orders[$id] = $p->getPurchaseOrder();
        }
        return array_values($orders);
    }

    /**
     * @return PurchaseOrder[] Unsent POs which are delivering parts needed
     *  by these work orders.
     */
    public function getUnsentPOs(): array
    {
        $filter = function (StockProducer $producer) {
            $po = $producer->getPurchaseOrder();
            return $po->hasSupplier() && (! $po->isSent());
        };
        return array_map(function (StockProducer $p) {
            return $p->getPurchaseOrder();
        }, $this->getOutstandingProducers($filter));
    }

    /**
     * @return WorkOrder[] A list of work orders that need to be done at
     * $location in preparation for this build.
     */
    public function getOutstandingPrepWork(Facility $location): array
    {
        $filter = function (StockProducer $producer) use ($location) {
            /* @var $producer WorkOrder */
            return $producer->isWorkOrder() && $producer->isLocation($location);
        };
        return $this->getOutstandingProducers($filter);
    }

    /**
     * @param callable $filter A filter function which takes a StockProducer
     *   and returns true if that producer should be included in the results.
     * @return StockProducer[]
     */
    private function getOutstandingProducers(callable $filter): array
    {
        $producers = [];
        foreach ($this->members as $wo) {
            foreach ($wo->getRequirements() as $woReq) {
                if ($woReq->isProvidedByChild()) {
                    continue;
                }
                foreach ($woReq->getAllocations() as $alloc) {
                    if ($alloc->isDelivered()) {
                        continue;
                    }
                    if (! $alloc->isFromProducer()) {
                        continue;
                    }
                    $producer = $alloc->getSource();
                    /* @var $producer StockProducer */
                    assertion($producer instanceof StockProducer);

                    if ($producer->isClosed()) {
                        continue;
                    }
                    if ($filter($producer)) {
                        $producers[] = $producer;
                    }
                }
            }
        }
        return $producers;
    }

    /**
     * True if all work orders have requirements.
     */
    public function hasRequirements(): bool
    {
        foreach ($this->members as $wo) {
            if (! $wo->hasRequirements()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return DateTime|null
     */
    public function getNextOutstandingCommitmentDate()
    {
        $min = null;
        foreach ($this->members as $wo) {
            if ($wo->isClosed()) {
                continue;
            }
            $date = $wo->getCommitmentDate();
            if (null === $date) {
                continue;
            }
            if (null === $min) {
                $min = $date;
            } elseif ($date < $min) {
                $min = $date;
            }
        }
        return $min;
    }
}
