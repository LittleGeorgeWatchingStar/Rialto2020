<?php

namespace Rialto\Accounting\Supplier\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Supplier\SupplierCreditItem;
use Rialto\Accounting\Supplier\SupplierCreditNote;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for entering a credit note from a supplier.
 */
class SupplierCreditNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => SupplierCreditItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->add('date', DateType::class, [
            ])
            ->add('toAccount', EntityType::class, [
                'class' => GLAccount::class,
            ])
            ->add('reference', TextType::class)
            ->add('comments', TextareaType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierCreditNote::class,
            'attr' => ['class' => 'standard'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierCreditNote';
    }

}


class SupplierCreditItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('account', EntityType::class, [
            'class' => GLAccount::class,
            'placeholder' => '-- choose --',
        ])
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->add('memo', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierCreditItem::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierCreditItem';
    }

}
