<?php

namespace Rialto\Sales\Returns\Web;

use Rialto\Sales\Customer\Customer;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
                'required' => false,
                'label' => 'RMA number',
        ])
            ->add('customer', JsEntityType::class, [
                'class' => Customer::class,
                'required' => false,
            ])
            ->add('reworkOrder', TextType::class, [
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'in receipt' => 'receive',
                    'in testing' => 'test',
                    'tested' => 'tested'
                ],
            ])
            ->add('stockItem', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
            ])
            ->add('filter', SubmitType::class);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

}
