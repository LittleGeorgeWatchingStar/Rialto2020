<?php

namespace Rialto\Stock\Bin;

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Security\User\User;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Determines privileges for purchase orders.
 */
class StockBinVoter extends RoleBasedVoter
{
    const VIEW = 'view';

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
            self::VIEW,
        ];
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [StockBin::class];
    }

    /**
     * @param string $attribute
     * @param StockBin $bin
     * @param UserInterface $user
     * @return bool
     */
    protected function isGranted($attribute, $bin, $user = null)
    {
        if ($attribute === self::VIEW){
            return $this->canView($bin, $user);
        } else {
            throw new InvalidArgumentException("Unsupported attribute $attribute");
        }
    }

    private function canView(StockBin $bin, $user = null)
    {
        if ($user instanceof User) {
            if ($this->hasRole(Role::EMPLOYEE, $user)) {
                return true;
            }
            if ($user->getSupplier() != null) {
                if ($bin->getLocation() === $user->getSupplier()->getLocation()) {
                    return true;
                }
            }
        }
        return false;
    }
}
