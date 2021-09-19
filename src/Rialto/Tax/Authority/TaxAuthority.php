<?php

namespace Rialto\Tax\Authority;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NoResultException;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"id"})
 */
class TaxAuthority implements RialtoEntity
{
    const NO_TAX = 0;
    const CA_STATE_TAX = 1;

    public static function fetchNoTax(ObjectManager $dbm = null)
    {
        return self::fetch(self::NO_TAX, $dbm);
    }

    /** @return TaxAuthority */
    public static function fetchCaStateTax(ObjectManager $dbm)
    {
        return self::fetch(self::CA_STATE_TAX, $dbm);
    }

    /** @return TaxAuthority */
    private static function fetch($id, ObjectManager $dbm = null)
    {
        $dbm = $dbm ?: ErpDbManager::getInstance();
        /** @var TaxAuthority|null $auth */
        $auth = $dbm->find(self::class, $id);
        if ($auth) {
            return $auth;
        }
        throw new NoResultException();
    }


    /****************\
      INSTANCE MEMBERS
    \****************/

    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     */
    private $id;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    private $description = '';

    /**
     * @Assert\NotNull
     */
    private $account;

    /**
     * @Assert\NotNull
     */
    private $purchaseAccount;

    public function __construct($id)
    {
        $this->setId($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($taxID)
    {
        $this->id = $taxID;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = trim($description);
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * @return GLAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount(GLAccount $account)
    {
        $this->account = $account;
    }

    /**
     * @return GLAccount
     */
    public function getPurchaseAccount()
    {
        return $this->purchaseAccount;
    }

    public function setPurchaseAccount(GLAccount $account)
    {
        $this->purchaseAccount = $account;
    }
}
