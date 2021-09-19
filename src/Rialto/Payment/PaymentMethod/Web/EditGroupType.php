<?php

namespace Rialto\Payment\PaymentMethod\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Ledger\Account\AccountGroup;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Account\Orm\GLAccountRepository;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing payment method groups.
 */
class EditGroupType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'PaymentMethodGroup';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => PaymentMethodGroup::getValidTypes(),
            ])
            ->add('baseFee', MoneyType::class, [
                'currency' => Currency::USD,
            ])
            ->add('feeRate', PercentType::class, [
                'scale' => 2, // two decimal places
            ])
            ->add('depositAccount', EntityType::class, [
                'class' => GLAccount::class,
                'query_builder' => function (GLAccountRepository $repo) {
                    return $repo->queryByGroup(AccountGroup::CURRENT_ASSETS);
                },
            ])
            ->add('feeAccount', EntityType::class, [
                'class' => GLAccount::class,
                'query_builder' => function (GLAccountRepository $repo) {
                    return $repo->queryByGroup(AccountGroup::CURRENT_LIABILITIES);
                },
            ])
            ->add('sweepFeesDaily', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentMethodGroup::class,
        ]);
    }
}
