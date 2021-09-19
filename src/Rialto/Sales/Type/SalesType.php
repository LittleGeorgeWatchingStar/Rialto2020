<?php

namespace Rialto\Sales\Type;

use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Indicates what type of sale a sales order is.
 *
 * The type of sale can affect how the accounting is done for the sales
 * order via SalesGLPostings.
 *
 * @UniqueEntity(fields="id", message="That ID is already in use.");
 */
class SalesType implements RialtoEntity
{
    /**************\
     STATIC MEMBERS
    \**************/

    const ONLINE = 'OS';
    const DIRECT = 'DI';
    const REPLACEMENT = 'RM';

    /** @return SalesType */
    public static function fetchDirectSale(DbManager $dbm = null)
    {
        return self::fetch(self::DIRECT, $dbm);
    }

    /** @return SalesType */
    public static function fetchOnlineSale(DbManager $dbm = null)
    {
        return self::fetch(self::ONLINE, $dbm);
    }

    /** @return SalesType */
    public static function fetchReplacementSale(DbManager $dbm = null)
    {
        return self::fetch(self::REPLACEMENT, $dbm);
    }

    /** @return SalesType */
    private static function fetch($id, DbManager $dbm = null)
    {
        $dbm = $dbm ?: ErpDbManager::getInstance();
        return $dbm->need(self::class, $id);
    }


    /****************\
     INSTANCE MEMBERS
    \****************/

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=2,
     *   maxMessage="ID should be at most {{ limit }} characters long.")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=20,
     *   maxMessage="Name should be at most {{ limit }} characters long."))
     */
    private $name;

    /**
     * @var int
     */
    private $listOrder = 0;

    public function __construct($id)
    {
        $this->id = strtoupper(trim($id));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function isDirectSale()
    {
        return $this->equals(self::DIRECT);
    }

    public function isOnlineSale()
    {
        return $this->equals(self::ONLINE);
    }

    public function isReplacement()
    {
        return $this->equals(self::REPLACEMENT);
    }

    /**
     * @param SalesType|string $other
     * @return bool
     */
    public function equals($other)
    {
        if ($other instanceof self) {
            $other = $other->getId();
        }
        return $other == $this->id;
    }
}
