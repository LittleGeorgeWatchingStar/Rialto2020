<?php

namespace Rialto\Shipping\Method\Web;

use Rialto\Shipping\Shipper\Shipper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Allows APIs to choose shipping methods by providing the shipper ID and
 * method code.
 */
class ShippingMethodApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipper', EntityType::class, [
                'class' => Shipper::class
            ])
            ->add('code', TextType::class);

        $transformer = new ShippingMethodToArrayTransformer();
        $builder->addViewTransformer($transformer);
    }

    public function getBlockPrefix()
    {
        return 'ShippingMethod';
    }

}
