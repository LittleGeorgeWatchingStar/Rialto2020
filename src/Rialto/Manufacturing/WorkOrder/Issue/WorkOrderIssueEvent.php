<?php

namespace Rialto\Manufacturing\WorkOrder\Issue;


use Rialto\Manufacturing\WorkOrder\WorkOrderEvent;

class WorkOrderIssueEvent extends WorkOrderEvent
{
    /** @var  WorkOrderIssue */
    private $issue;

    public function __construct(WorkOrderIssue $issue)
    {
        $this->issue = $issue;
        parent::__construct($issue->getWorkOrder());
    }

    /**
     * @return WorkOrderIssue
     */
    public function getIssue()
    {
        return $this->issue;
    }
}
