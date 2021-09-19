<?php

namespace Rialto\Accounting\Transaction\Web;


use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class TransactionListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sysType', EntityType::class, [
                'class' => SystemType::class,
                'required' => false,
                'label' => 'Type',
            ])
            ->add('groupNo', SearchType::class, [
                'required' => false,
                'label' => 'Trans No',
            ])
            ->add('account', JsEntityType::class, [
                'class' => GLAccount::class,
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
            ])
            ->add('memo', SearchType::class, [
                'required' => false,
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'attr' => ['placeholder' => '0 for no limit'],
            ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
