<?php

namespace Rialto\Purchasing\Order;

use Rialto\Security\Role\Role;
use Rialto\Security\Role\RoleBasedVoter;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Determines whether the current user can purchase a stock item.
 */
class StockItemVoter extends RoleBasedVoter
{
    const PURCHASE = 'purchase';

    protected function getSupportedAttributes()
    {
        return [self::PURCHASE];
    }

    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return [StockItem::class];
    }

    /**
     * @param TokenInterface $token
     * @param StockItem $item
     * @param string $attribute
     * @return bool
     */
    protected function isGranted($attribute, $item, $user = null)
    {
        assertion($item instanceof StockItem);
        if ( $item->isDiscontinued() ) {
            return false;
        }
        if ( $this->hasRole(Role::PURCHASING, $user) ) {
            return true;
        }
        return $this->hasRole(Role::WAREHOUSE, $user) && (
            $item->isCategory(StockCategory::SHIPPING) ||
            $item->isCategory(StockCategory::ENCLOSURE) );
    }
}
