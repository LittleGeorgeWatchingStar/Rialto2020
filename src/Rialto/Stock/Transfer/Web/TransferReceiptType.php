<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Time\Web\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for receiving a Transfer.
 */
class TransferReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => TransferItemReceiptType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => true,
            ])
            ->add('extraItems', CollectionType::class, [
                'entry_type' => TransferExtraItemType::class,
                'label' => false,
                'allow_add' => true,
                'prototype' => true,
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date received',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'Transfer';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransferReceipt::class,
            'validation_groups' => ['Default', 'receipt'],
        ]);
    }
}
