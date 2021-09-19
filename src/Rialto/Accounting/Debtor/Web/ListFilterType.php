<?php

namespace Rialto\Accounting\Debtor\Web;


use Rialto\Accounting\Transaction\SystemType;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', JsEntityType::class, [
                'class' => Customer::class,
                'required' => false,
            ])
            ->add('salesOrder', TextEntityType::class, [
                'class' => SalesOrder::class,
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
                'label' => 'Since',
            ])
            ->add('systemType', EntityType::class, [
                'class' => SystemType::class,
                'required' => false,
                'placeholder' => '-- any --',
                'label' => 'Type',
                // todo: filter debtor trans sys types
            ])
            ->add('groupNo', SearchType::class, [
                'required' => false,
                'label' => 'Trans No(s)',
            ])
            ->remove('_limit')
            ->add('_limit', NumberType::class, [
                'required' => false,
                'empty_data' => '100',
                'attr' => ['title' => 'Enter zero for no limit'],
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
