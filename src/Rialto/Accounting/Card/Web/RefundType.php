<?php

namespace Rialto\Accounting\Card\Web;


use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Currency\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * For refunding a captured card transaction.
 */
class RefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CardTransaction $payment */
        $payment = $options['payment'];
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Last four digits of card to refund',
                'constraints' => new Assert\Regex([
                    'pattern' => '/^\d{4}$/',
                    'message' => 'Please enter the last four digits of the original credit card number',
                ]),
            ])
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Amount to refund',
                'constraints' => new Assert\Range([
                    'min' => 0.01,
                    'max' => $payment->getAmountCaptured(),
                ]),
                'data' => $payment->getAmountCaptured(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('payment');
        $resolver->setAllowedTypes('payment', CardTransaction::class);
    }
}
