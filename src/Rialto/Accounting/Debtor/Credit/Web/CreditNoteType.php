<?php

namespace Rialto\Accounting\Debtor\Credit\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\Credit\CreditNote;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering a customer credit note.
 */
class CreditNoteType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'CreditNote';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $customer = $options['customer'];
        $builder
            ->add('salesOrder', EntityType::class, [
                'class' => SalesOrder::class,
                'query_builder' => function (SalesOrderRepository $repo) use ($customer) {
                    return $repo->queryByCustomer($customer);
                },
                'choice_label' => 'id',
                'label' => 'Sales order',
                'placeholder' => '-- none --',
                'required' => false,
            ])
            ->add('date', DateType::class)
            ->add('toAccount', EntityType::class, [
                'class' => GLAccount::class,
                'placeholder' => '-- select an account --',
                'label' => 'To account',
            ])
            ->add('memo', TextType::class)
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('customer');
        $resolver->setAllowedTypes('customer', Customer::class);
        $resolver->setDefaults([
            'data_class' => CreditNote::class,
        ]);
    }
}
