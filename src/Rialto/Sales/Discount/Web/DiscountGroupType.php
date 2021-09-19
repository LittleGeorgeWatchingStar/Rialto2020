<?php

namespace Rialto\Sales\Discount\Web;

use Rialto\Sales\Discount\DiscountGroup;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscountGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => "Name"
        ]);

        $builder->add('items', CollectionType::class, [
            'entry_type' => JsEntityType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'entry_options' => [
                'class' => StockItem::class,
            ],
        ]);

        $builder->add('rates', CollectionType::class, [
            'entry_type' => DiscountRateType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'DiscountGroup';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DiscountGroup::class,
            'validation_groups' => ['discount']
        ]);
    }
}
