<?php

namespace Rialto\Shipping\Method\Web;

use Rialto\Shipping\Method\ShippingMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing ShippingMethods.
 *
 * @see ShippingMethod
 */
class EditShippingMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('showByDefault', CheckboxType::class, [
                'required' => false,
            ])
            ->add('trackingNumberRequired', CheckboxType::class, [
                'required' => false,
                'label' => 'Tracking number must be manually entered?',
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Check this box if the tracking number must be ' .
                        'manually entered when the order is shipped.',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShippingMethod::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'EditShippingMethod';
    }

}
