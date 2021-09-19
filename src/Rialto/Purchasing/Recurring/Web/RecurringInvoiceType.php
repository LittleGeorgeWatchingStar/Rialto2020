<?php

namespace Rialto\Purchasing\Recurring\Web;

use Rialto\Purchasing\Recurring\RecurringInvoice;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing recurring invoices.
 */
class RecurringInvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
            ])
            ->add('reference', TextType::class)
            ->add('dates', CollectionType::class, [
                'entry_type' => IntegerType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('details', CollectionType::class, [
                'entry_type' => RecurringInvoiceDetailType::class,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecurringInvoice::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'RecurringInvoice';
    }
}
