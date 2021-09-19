<?php

namespace Rialto\Shipping\Method\Web;

use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Shipper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding a new shipping method to a shipper.
 *
 * @see Shipper
 * @see ShippingMethod
 */
class CreateShippingMethodType extends AbstractType
{
    public function getParent()
    {
        return EditShippingMethodType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class)
            ->add('name', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('shipper');
        $resolver->setAllowedTypes('shipper', Shipper::class);

        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $shipper = $form->getConfig()->getOption('shipper');
                $code = $form->get('code')->getData();
                $name = $form->get('name')->getData();
                return $shipper->addShippingMethod($code, $name);
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CreateShippingMethod';
    }

}
