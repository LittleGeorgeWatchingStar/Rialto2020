<?php

namespace Rialto\Shipping\Shipper\Web;

use Rialto\Shipping\Shipper\Shipper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating/editing Shippers.
 *
 * @see Shipper
 */
class ShipperType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
            ->add('accountNumber', TextType::class, [
                'label' => 'Account number',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Active?',
                'required' => false,
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shipper::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Shipper';
    }

}
