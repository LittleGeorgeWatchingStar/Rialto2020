<?php

namespace Rialto\Accounting\Debtor\Receipt\Web;

use Rialto\Accounting\Currency\Currency;
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
 * Base form type for customer receipts. Specific customer receipt types
 * (eg, credit card receipt, wire receipt) should use this as their parent
 * form type.
 */
class CustomerReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $customer Customer */
        $customer = $options['customer'];

        $builder
            ->add('salesOrder', EntityType::class, [
                'class' => SalesOrder::class,
                'query_builder' => function (SalesOrderRepository $repo) use ($customer) {
                    return $repo->queryUnpaidOrdersByCustomer($customer);
                },
                'choice_label' => 'id',
                'required' => false,
                'placeholder' => '-- none --',
                'label' => 'Sales order'
            ])
            ->add('date', DateType::class, [
                'format' => 'yyyy-MM-dd',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->add('memo', TextType::class, [
                'attr' => ['class' => 'memo']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['customer']);
        $resolver->setAllowedTypes('customer', Customer::class);
    }

    public function getBlockPrefix()
    {
        return 'rialto_customer_receipt';
    }
}
