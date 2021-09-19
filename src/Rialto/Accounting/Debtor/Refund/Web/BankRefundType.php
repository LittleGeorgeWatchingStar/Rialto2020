<?php

namespace Rialto\Accounting\Debtor\Refund\Web;


use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\Refund\BankRefund;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Time\Web\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankRefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Customer $customer */
        $customer = $options['customer'];

        $builder
            ->add('date', DateTimeType::class)
            ->add('salesOrder', EntityType::class, [
                'class' => SalesOrder::class,
                'query_builder' => function(SalesOrderRepository $repo) use ($customer) {
                    return $repo->queryRefundableOrders($customer);
                },
            ])
            ->add('bankAccount', EntityType::class, [
                'class' => BankAccount::class,
                'choice_label' => 'name',
                'label' => 'Bank account',
            ])
            ->add('paymentType', ChoiceType::class, [
                'choices' => BankTransaction::getValidPaymentTypes(),
                'label' => 'Payment method'
            ])
            ->add('chequeNumber', IntegerType::class, [
                'label' => 'Cheque No',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', BankRefund::class);
        $resolver->setRequired('customer');
        $resolver->setAllowedTypes('customer', Customer::class);
    }

}
