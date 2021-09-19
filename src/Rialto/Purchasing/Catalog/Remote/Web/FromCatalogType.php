<?php

namespace Rialto\Purchasing\Catalog\Remote\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\Web\BaseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating PurchasingData records from a supplier's online catalog.
 */
class FromCatalogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* The catalog automatically sets the manufacturer. */
        $builder->remove('manufacturer');

        $builder->add('manufacturerLeadTime', IntegerType::class);

        $builder->add('costBreaks', CollectionType::class, [
            'entry_type' => SimpleBreakType::class,
            'entry_options' => ['label' => false],
            'by_reference' => false,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    public function getParent()
    {
        return BaseType::class;
    }
}

class SimpleBreakType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minimumOrderQty', IntegerType::class)
            ->add('unitCost', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => PurchasingData::UNIT_COST_SCALE,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CostBreak::class);
    }

}
