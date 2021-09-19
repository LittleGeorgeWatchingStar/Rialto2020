<?php

namespace Rialto\Payment\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Payment\CardAuth;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering credit card authorization info.
 */
class CardAuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Amount to authorize',
            ])
            ->add('type', EntityType::class, [
                'class' => PaymentMethod::class,
                'label' => 'Card type',
            ])
            ->add('number', TextType::class, [
                'label' => 'Card number',
            ])
            ->add('expiry', CreditCardExpiryType::class, [
                'label' => 'Expiration',
            ])
            ->add('code', PasswordType::class, [
                'label' => 'CVV code',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CardAuth::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CardAuth';
    }

}
