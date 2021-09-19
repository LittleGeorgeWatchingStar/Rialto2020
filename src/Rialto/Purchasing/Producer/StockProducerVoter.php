<?php

namespace Rialto\Purchasing\Producer;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;
use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;


/**
 * Determines privileges for stock producers.
 *
 * @see StockProducer
 */
class StockProducerVoter extends RoleBasedVoter
{
    const COST = 'unitCost';
    const QTY_ORDERED = 'qtyOrdered';
    const FLAGS = 'flags';
    const ALLOCATE = 'openForAllocation';
    const INSTRUCTIONS = 'instructions';
    const VERSION = 'version';
    const PURCH_DATA = 'purchData';
    const CUSTOMIZATION = 'customization';
    const PARENT = 'parent';
    const REWORK = 'rework';

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
            Privilege::DELETE,
            self::REWORK,
            self::COST,
            self::QTY_ORDERED,
            self::ALLOCATE,
            self::INSTRUCTIONS,
            self::PURCH_DATA,
            self::VERSION,
            self::CUSTOMIZATION,
            self::PARENT,
            self::FLAGS,
        ];
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [StockProducer::class];
    }

    /**
     * @param StockProducer $producer
     * @return bool
     */
    public function isGranted($attribute, $producer, $user = null)
    {
        switch ($attribute) {
            case self::ALLOCATE:
                return !$producer->isClosed();
            case self::INSTRUCTIONS:
                return !$producer->isClosed();
            case self::FLAGS:
                return !$producer->isClosed();
            case self::PURCH_DATA:
                return !$producer->isInProcess();
            case self::VERSION:
                return !$producer->isInProcess();
            case self::CUSTOMIZATION:
                return !$producer->isInProcess();
            case self::PARENT:
                return !$producer->isInProcess();
            case self::COST:
                return $this->unitCost($producer, $user);
            case self::QTY_ORDERED:
                return $this->qtyOrdered($producer, $user);
            case self::REWORK:
                return $this->hasRole(Role::ADMIN, $user);
            case Privilege::DELETE:
                return $this->delete($producer, $user);
            default:
                throw new \UnexpectedValueException("Unsupported attribute $attribute");
        }
    }

    private function unitCost(StockProducer $producer, $user = null)
    {
        return ($producer->getQtyReceived() == 0)
            && ($producer->getQtyInvoiced() == 0);
    }

    private function qtyOrdered(StockProducer $producer, $user = null)
    {
        if ($producer->isInProcess()) {
            return $this->hasRole(Role::ADMIN, $user);
        }
        return true;
    }


    private function delete(StockProducer $producer, $user = null)
    {
        if (!$this->hasRole(Role::PURCHASING, $user)) {
            return false;
        }
        if ($producer->getQtyInvoiced() > 0) {
            return false;
        }
        if ($producer->getQtyReceived() > 0) {
            return false;
        }
        if ($this->hasDependentRecords($producer)) {
            return false;
        }

        return true;
    }

    private function hasDependentRecords(StockProducer $producer)
    {
        /** @var $repo StockProducerRepository */
        $repo = $this->om->getRepository(get_class($producer));
        return $repo->hasDependentRecords($producer);
    }

}
