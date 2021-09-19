<?php

namespace Rialto\Accounting\Ledger\Entry\Web;


use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Period;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
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
            ->add('systemTypeNumber', SearchType::class, [
                'label' => 'Group no',
                'required' => false,
            ])
            ->add('narrative', SearchType::class, [
                'required' => false,
            ])
            ->add('minAmount', SearchType::class, [
                'required' => false,
            ])
            ->add('maxAmount', SearchType::class, [
                'required' => false,
            ])
            ->add('startPeriod', EntityType::class, [
                'class' => Period::class,
                'required' => false,
            ])
            ->add('endPeriod', EntityType::class, [
                'class' => Period::class,
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
            ])
            ->add('posted', YesNoAnyType::class, [
                'label' => 'Posted?',
            ])
            ->add('_limit', IntegerType::class, [
                'empty_data' => '100',
                'required' => false,
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
