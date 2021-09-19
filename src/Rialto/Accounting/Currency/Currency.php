<?php

namespace Rialto\Accounting\Currency;

use Rialto\Database\Orm\DbManager;
use Rialto\Entity\RialtoEntity;

/**
 * Represents a currency, such as USD.
 */
class Currency implements RialtoEntity
{
    const USD = 'USD';

    /** @return Currency */
    public static function findUSD(DbManager $dbm)
    {
        return $dbm->need(self::class, self::USD);
    }

    private $id;
    private $name;
    private $exchangeRate;
    private $Country;
    private $HundredsName;

    public function __construct($id, $name, $rate=1.0)
    {
        $this->id = strtoupper(trim($id));
        $this->name = trim($name);
        $this->exchangeRate = $rate;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->id;
    }

    /**
     * @return double
     */
    public function getRate()
    {
        return $this->exchangeRate;
    }
}

