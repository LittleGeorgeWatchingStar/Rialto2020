<?php

namespace Rialto\Purchasing\Catalog\Remote\Web;

use Rialto\Purchasing\Catalog\Remote\SupplierApi;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing SupplierApi records.
 * @see SupplierApi
 */
class SupplierApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('supplier', EntityType::class, [
            'class' => Supplier::class,
            'choice_label' => 'name',
        ]);
        $builder->add('website', UrlType::class);
        $builder->add('serviceName', ChoiceType::class, [
            'choices' => SupplierApi::getServiceOptions(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierApi::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierApi';
    }

}
