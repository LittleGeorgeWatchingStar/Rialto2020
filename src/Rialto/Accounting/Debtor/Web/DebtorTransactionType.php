<?php

namespace Rialto\Accounting\Debtor\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing debtor transactions.
 */
class DebtorTransactionType extends DynamicFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DebtorTransaction::class,
            'validation_groups' => ['Default', 'orderAllocation'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DebtorTransaction';
    }

    /** @param $debtorTrans DebtorTransaction */
    protected function updateForm(FormInterface $form, $debtorTrans)
    {
        $form->add('memo', TextType::class, [
            'required' => false,
            'attr' => ['class' => 'memo'],
        ]);
        if ($debtorTrans->isInvoice()) {
            $form->add('consignment', TextType::class, [
                'label' => 'Consignment/tracking no.',
                'required' => false,
            ]);
        } else {
            $form->add('orderAllocations', CollectionType::class, [
                'entry_type' => OrderAllocationType::class,
                'entry_options' => ['credit' => $debtorTrans],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]);
        }
    }
}
