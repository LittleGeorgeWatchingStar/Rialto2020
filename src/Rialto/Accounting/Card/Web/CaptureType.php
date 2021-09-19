<?php

namespace Rialto\Accounting\Card\Web;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Currency\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * For capturing authorized card transactions.
 */
class CaptureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CardTransaction $cardTrans */
        $cardTrans = $options['trans'];
        $builder
            ->add('amount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Amount to capture',
                'data' => $cardTrans->getAmountAuthorized(),
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range([
                        'min' => 0.01,
                        'max' => $cardTrans->getAmountAuthorized(),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        /* The authorized card transaction */
        $resolver->setRequired('trans');
        $resolver->setAllowedTypes('trans', CardTransaction::class);
    }

}
