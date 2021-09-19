<?php

namespace Rialto\Shipping\Shipment\Web;

use Rialto\Shipping\Method\ShippingMethodInterface;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipment\ShipmentFactory;
use Rialto\Shipping\Shipment\ShipmentOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShipmentOptionsType extends AbstractType
{
    /** @var ShipmentFactory */
    private $shipmentFactory;

    public function __construct(ShipmentFactory $factory)
    {
        $this->shipmentFactory = $factory;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['salesOrder']);
        $resolver->setAllowedTypes('salesOrder', RatableOrder::class);
        $resolver->setDefault('choices', function (Options $options) {
            return $this->loadChoices($options['salesOrder']);
        });
        $resolver->setDefault('choice_label', function (ShipmentOption $option) {
            return sprintf('%s ($%s)',
                $option->getName(),
                number_format($option->getShippingCost(), 2));
        });
        $resolver->setDefault('choice_value', function (ShippingMethodInterface $method = null) {
            return $method ? $method->getCode() : '';
        });
    }

    private function loadChoices(RatableOrder $order): array
    {
        return $this->shipmentFactory->getShipmentOptions($order);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
