<?php

namespace Rialto\Shipping\Export;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Shipping\Export\Orm\ShipmentProhibitionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @see AllowedCountry
 */
class AllowedCountryValidator extends ConstraintValidator
{
    /** @var ShipmentProhibitionRepository */
    private $repo;

    public function __construct(DbManager $dbm)
    {
        $this->repo = $dbm->getRepository(ShipmentProhibition::class);
    }

    public function validate($order, Constraint $constraint)
    {
        if (! $order instanceof SalesOrder ) {
            throw new UnexpectedTypeException($order, 'SalesOrder');
        }

        $destination = $order->getShippingAddress()->getCountry();

        foreach ( $order->getLineItems() as $item ) {
            if ( $this->repo->isProhibited($item->getStockItem(), $destination) ) {
                $this->context->addViolation($constraint->message, [
                    'item' => $item->getSku(),
                    'country' => $destination,
                ]);
            }
        }
    }
}
