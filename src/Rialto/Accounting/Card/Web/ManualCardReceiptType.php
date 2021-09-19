<?php

namespace Rialto\Accounting\Card\Web;


use Rialto\Accounting\Currency\Currency;
use Rialto\Payment\AuthorizeNet;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Time\Web\DateTimeType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For manually entering a receipt CardTransaction.
 *
 * Normally, CardTransactions are created automatically by payment gateways
 * such as Authorize.net.
 *
 * @see AuthorizeNet
 */
class ManualCardReceiptType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'CardTransaction';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('card', EntityType::class, [
                'class' => PaymentMethod::class,
                'placeholder' => '-- choose --',
        ])
            ->add('transactionId', TextType::class, [
                'label' => 'Transaction ID',
            ])
            ->add('authCode', TextType::class, [
            ])
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->add('created', DateTimeType::class, [
                'data' => new \DateTime(),
            ])
            ->add('salesOrder', TextEntityType::class, [
                'class' => SalesOrder::class,
            ])
            ->add('capture', CheckboxType::class, [
                'required' => false,
                'label' => 'Is captured?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ManualCardReceipt::class,
        ]);
    }
}
