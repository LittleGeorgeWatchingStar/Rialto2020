<?php

namespace Rialto\Shipping\Shipment\Web;

use Rialto\Shipping\Shipment\ShipmentPackage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form input for entering/editing ShipmentPackages.
 */
class ShipmentPackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $weightOpts = array_merge([
            'label' => 'Weight (kg)'
        ], $options['weight']);
        $builder->add('weight', NumberType::class, $weightOpts);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShipmentPackage::class,
            'weight' => [],
        ]);
    }
}
