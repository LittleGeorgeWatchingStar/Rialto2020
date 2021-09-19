<?php

namespace Rialto\Accounting\Ledger\Account;

use Rialto\Entity\RialtoEntity;

class AccountGroup
implements RialtoEntity
{
    const CURRENT_ASSETS = 'Current Assets';
    const CURRENT_LIABILITIES = 'Current Liabilities';
    const SALES_ADJUSTMENTS = 'Sales Adjustments';

    /** @var string */
    private $groupName;

    /** @var AccountSection */
    private $section;

    /** @var bool */
    private $profitAndLoss;

    /** @var int */
    private $sequenceInTB;

    public function getId()
    {
        return $this->groupName;
    }

    public function getName()
    {
        return $this->groupName;
    }

    public function __toString()
    {
        return $this->groupName;
    }

    /** @return string */
    public function getSectionName()
    {
        return $this->section->getName();
    }

    public function isProfitAndLoss()
    {
        return $this->profitAndLoss;
    }

    public function getSignForReporting()
    {
        return $this->section->getSignForReporting();
    }
}
