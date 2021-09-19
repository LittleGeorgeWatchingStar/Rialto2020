<?php

namespace Rialto\Purchasing\Supplier\Contact\Web;

use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing a supplier contact.
 */
class SupplierContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
            ->add('position', TextType::class, [
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'required' => false,
            ])
            ->add('fax', TextType::class, [
                'required' => false,
            ])
            ->add('mobilePhone', TextType::class, [
                'label' => 'Mobile',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('contactForOrders', CheckboxType::class, [
                'label' => 'Email purchase orders',
                'required' => false,
            ])
            ->add('contactForStats', CheckboxType::class, [
                'label' => 'Email sales stats',
                'required' => false,
            ])
            ->add('contactForKits', CheckboxType::class, [
                'label' => 'Email work order kits',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierContact::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierContact';
    }
}
