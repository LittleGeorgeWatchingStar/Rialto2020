<?php

namespace Rialto\Security\Role;

use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a security role that grants privileges to a user.
 *
 * @UniqueEntity(fields={"name"})
 */
class Role implements RialtoEntity, RoleInterface
{
    const API_CLIENT = 'ROLE_API_CLIENT';
    const ACCOUNTING = 'ROLE_ACCOUNTING';
    /**
     * Users with the ACCOUNTING_OVERRIDE role can override validations related
     * to accounting (e.g. shipping a sales order with outstanding payments).
     */
    const ACCOUNTING_OVERRIDE = 'ROLE_ACCOUNTING_OVERRIDE';
    const ADMIN = 'ROLE_ADMIN';
    const CUSTOMER_SERVICE = 'ROLE_CUSTOMER_SERVICE';
    const EMPLOYEE = 'ROLE_EMPLOYEE';
    const ENGINEER = 'ROLE_ENGINEER';
    const GEPPETTO = 'ROLE_GEPPETTO';
    const MANUFACTURING = 'ROLE_MANUFACTURING';
    const PURCHASING = 'ROLE_PURCHASING';
    const PURCHASING_DATA = 'ROLE_PURCHASING_DATA';
    const RECEIVING = 'ROLE_RECEIVING';
    const SALES = 'ROLE_SALES';
    const SHIPPING = 'ROLE_SHIPPING';
    const STOCK = 'ROLE_STOCK';
    const STOCK_VIEW = 'ROLE_STOCK_VIEW';

    /**
     * Users with the STOCK_CREATE role can insert or adjust stock into the
     * system from 'nothing' i.e. without the use of a purchase order or a stock
     * transfer.
     */
    const STOCK_CREATE = 'ROLE_STOCK_CREATE';

    const STOREFRONT = 'ROLE_STOREFRONT';
    const SUPPLIER_SIMPLE = 'ROLE_SUPPLIER_SIMPLE';
    const SUPPLIER_ADVANCED = 'ROLE_SUPPLIER_ADVANCED';

    /**
     * Users with the SUPPLIER_INTERNAL role can access internal build files that
     * we do not share with other suppliers like eagle designs.
     */
    const SUPPLIER_INTERNAL = 'ROLE_SUPPLIER_INTERNAL';
    const WAREHOUSE = 'ROLE_WAREHOUSE';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=50)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    private $label;

    /**
     * @var string
     * @Assert\Length(max=50)
     */
    private $group = '';

    public function __construct($name)
    {
        $this->name = strtoupper(trim($name));
        $this->label = trim($name);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRole()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->getRole();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = trim($label);
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = trim($group);
    }
}
