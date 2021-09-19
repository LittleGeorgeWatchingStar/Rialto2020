<?php

namespace Rialto\Manufacturing\WorkOrder\Issue\Orm;

use Rialto\Accounting\AccountingEventRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

class WorkOrderIssueRepository
extends RialtoRepositoryAbstract
implements AccountingEventRepository
{
    public function findByWorkOrder(WorkOrder $order)
    {
        return $this->findBy(
            ['workOrder' => $order->getId()],
            ['dateIssued' => 'ASC']
        );
    }

    public function findByType(SystemType $sysType, $typeNo)
    {
        return [$this->find($typeNo)];
    }
}
