<?php

namespace Rialto\Sales\Customer;

use Rialto\Database\Orm\DbManager;
use Rialto\Entity\RialtoEntity;

class HoldReason implements RialtoEntity
{
    const GOOD_CREDIT_STATUS = 0;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    private $DissallowInvoices;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /** @return HoldReason */
    public static function findGoodCreditStatus(DbManager $dbm)
    {
        return $dbm->need(self::class, self::GOOD_CREDIT_STATUS);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->description;
    }
}
