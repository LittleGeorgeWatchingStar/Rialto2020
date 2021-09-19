<?php

namespace Rialto\Accounting\Balance\Web;


use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', JsEntityType::class, [
                'class' => GLAccount::class,
                'required' => false,
            ])
            ->add('fromPeriod', TextEntityType::class, [
                'class' => Period::class,
                'required' => false,
                'label' => 'Between period',
            ])
            ->add('toPeriod', TextEntityType::class, [
                'class' => Period::class,
                'required' => false,
                'label' => 'and',
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
