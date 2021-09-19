<?php

namespace Rialto\Purchasing\Catalog\Web;

use Rialto\Accounting\Currency\Currency;
use Rialto\Purchasing\Catalog\PurchasingData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for editing CostBreakAbstract records.
 */
class CostBreakAbstractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minimumOrderQty', IntegerType::class)
            ->add('manufacturerLeadTime', IntegerType::class)
            ->add('supplierLeadTime', IntegerType::class, [
                'required' => false,
            ])
            ->add('unitCost', MoneyType::class, [
                'currency' => Currency::USD,
                'scale' => PurchasingData::UNIT_COST_SCALE,
            ]);
    }
}
