<?php

namespace Rialto\Accounting\Card\Web;


use Rialto\Accounting\Card\CardTransaction;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing card transactions.
 */
class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creditCard', EntityType::class, [
                'class' => PaymentMethod::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CardTransaction::class);
    }
}
