<?php

namespace Rialto\Purchasing\Recurring\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Purchasing\Recurring\RecurringInvoiceDetail;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing recurring invoice details.
 */
class RecurringInvoiceDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', EntityType::class, [
                'class' => GLAccount::class,
            ])
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => RecurringInvoiceDetail::MONEY_PRECISION,
            ])
            ->add('reference', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecurringInvoiceDetail::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'RecurringInvoiceDetail';
    }
}
