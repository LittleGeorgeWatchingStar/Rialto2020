<?php

namespace Rialto\Accounting\Debtor\Receipt\Web;

use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\Credit\WireReceipt;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering customer receipts for wire transfers or cheque deposits.
 */
class BankReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'label' => 'Account',
            ])
            ->add('feeAmount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Fee',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    BankTransaction::TYPE_DIRECT => BankTransaction::TYPE_DIRECT,
                    BankTransaction::TYPE_CHEQUE => BankTransaction::TYPE_CHEQUE,
                ],
            ])
            ->add('transactionId', TextType::class, [
                'label' => 'Transaction ID',
                'required' => false,
            ])
            ->add('chequeNo', IntegerType::class, [
                'label' => "Cheque No",
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WireReceipt::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'BankReceipt';
    }

    public function getParent()
    {
        return CustomerReceiptType::class;
    }


}
