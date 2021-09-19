<?php

namespace Rialto\Purchasing\Quotation\Web;


use Gumstix\Time\DateRangeType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class QuotationRequestListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => 'Stock item',
            ])
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('dateSent', DateRangeType::class, [
                'required' => false,
                'start_label' => 'Date sent'
            ])
            ->add('sent', YesNoAnyType::class)
            ->add('received', YesNoAnyType::class)
            ->add('_limit', NumberType::class, [
                'required' => false,
                'label' => 'Show # of records',
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
