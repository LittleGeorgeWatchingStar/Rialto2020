<?php

namespace Rialto\Accounting\Supplier\Web;


use Gumstix\Time\DateRangeType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function getParent()
    {
        return FilterForm::class;
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('dates', DateRangeType::class, [
                'required' => false,
            ])
            ->add('reference', SearchType::class, [
                'required' => false,
            ])
            ->add('credit', ChoiceType::class, [
                'choices' => [
                    'invoice' => 'no',
                    'payment' => 'yes',
                ],
                'required' => false,
                'placeholder' => '-- all --',
                'label' => 'Type',
            ])
            ->add('groupNos', SearchType::class, [
                'required' => false,
                'label' => 'Type No(s)',
            ])
            ->add('settled', YesNoAnyType::class)
            ->add('minAmount', SearchType::class, [
                'required' => false,
                'label' => 'Amount between',
            ])
            ->add('maxAmount', SearchType::class, [
                'required' => false,
                'label' => 'and',
            ])
            ->add('_limit', IntegerType::class, [
                'label' => 'Limit',
                'empty_data' => '100',
            ]);
    }


}
