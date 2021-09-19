<?php

namespace Rialto\Purchasing\Order;

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducerVoter;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Stock\Category\StockCategory;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Determines privileges for purchase orders.
 */
class PurchaseOrderVoter extends RoleBasedVoter
{
    const SEND = 'send';
    const RECEIVE = 'receive';

    /** @var ObjectManager */
    private $om;

    public function __construct(RoleHierarchyInterface $hierarchy, ObjectManager $om)
    {
        parent::__construct($hierarchy);
        $this->om = $om;
    }

    protected function getSupportedAttributes()
    {
        return [
            Privilege::EDIT,
            Privilege::DELETE,
            self::SEND,
            self::RECEIVE,
        ];
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [PurchaseOrder::class];
    }

    /**
     * @param string $attribute
     * @param PurchaseOrder $po
     * @param UserInterface $user
     * @return bool
     */
    protected function isGranted($attribute, $po, $user = null)
    {
        switch ($attribute) {
            case Privilege::EDIT:
                return $this->canEdit($po, $user);
            case Privilege::DELETE:
                return $this->canDelete($po, $user);
            case self::SEND:
                return $this->canSend($po, $user);
            case self::RECEIVE:
                return $this->canReceive($po, $user);
            default:
                throw new InvalidArgumentException("Unsupported attribute $attribute");
        }
    }

    private function canEdit(PurchaseOrder $po, $user = null)
    {
        if ($po->isCompleted()) {
            return $this->hasRole(Role::ADMIN, $user);
        }
        if ($this->hasRole(Role::PURCHASING, $user)) {
            return true;
        }
        return $po->hasSupplier()
        && $this->hasRole(Role::WAREHOUSE, $user)
        && $this->sellsPackaging($po->getSupplier());
    }

    private function sellsPackaging(Supplier $supplier)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $this->om->getRepository(PurchasingData::class);
        return $repo->sellsCategories($supplier, [
            StockCategory::SHIPPING,
            StockCategory::ENCLOSURE,
        ]);
    }

    private function canDelete(PurchaseOrder $po, $user = null)
    {
        if ($po->isSent()) {
            return false;
        }
        if (! $this->hasRole(Role::PURCHASING, $user)) {
            return false;
        }
        $subVoter = new StockProducerVoter($this->roleHierarchy, $this->om);

        foreach ($po->getItems() as $poItem) {
            if (! $subVoter->isGranted(Privilege::DELETE, $poItem, $user)) {
                return false;
            }
        }
        return true;
    }

    private function canSend(PurchaseOrder $po, $user = null)
    {
        if (! $po->canBeSent()) {
            return false;
        }
        return $this->hasRole(Role::PURCHASING, $user);
    }

    private function canReceive(PurchaseOrder $po, $user = null)
    {
        if ($po->isCompleted()) {
            return false;
        }
        if (! $po->isSent()) {
            return false;
        }
        return $this->hasRole(Role::RECEIVING, $user);
    }
}
