<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Geography\Address\Web\AddressEntityType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing Facility records.
 */
class FacilityType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'Location';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
            ->add('contactName', TextType::class, [
                'label' => 'Contact',
                'required' => false,
            ])
            ->add('address', AddressEntityType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('phone', TextType::class, [
                'required' => false,
            ])
            ->add('fax', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('parentLocation', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
                'placeholder' => '-- none --',
            ])
            ->add('supplier', EntityType::class, [
                'class' => Supplier::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '-- none --',
            ])
            ->add('taxAuthority', EntityType::class, [
                'class' => TaxAuthority::class,
                'choice_label' => 'description',
                'label' => 'Tax authority',
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
            ->add('allocateFromCM', CheckboxType::class, [
                'required' => false,
                'label' => 'Allocate stock from CM?'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Facility::class,
        ]);
    }

}
