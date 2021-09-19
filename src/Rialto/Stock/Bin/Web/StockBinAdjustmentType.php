<?php

namespace Rialto\Stock\Bin\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\Version\Web\VersionChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormInterface;

/**
 * Form type for creating or editing stock bins.
 */
class StockBinAdjustmentType extends DynamicFormType
{
    public function getParent()
    {
        return StockBinType::class;
    }

    /**
     * @param StockBin $stockBin
     */
    protected function updateForm(FormInterface $form, $stockBin)
    {
        $item = $stockBin->getStockItem();
        if ($item->isVersioned() && (!$item->isPrintedLabel())) {
            $form->add('version', VersionChoiceType::class, [
                'choices' => $item->getValidVersions(),
                'placeholder' => '-- choose --',
            ]);
        }
        if ($item->isCustomizable()) {
            $form->add('customization', EntityType::class, [
                'class' => Customization::class,
                'query_builder' => function (CustomizationRepository $repo) use ($item) {
                    return $repo->createBuilder()
                        ->bySku($item)
                        ->getQueryBuilder();
                },
                'required' => false,
                'placeholder' => '-- none --',
            ]);
        }
        if ($stockBin->isNew()) {
            $form->add('purchaseCost', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Purchase cost per unit',
                'scale' => 8,
            ]);
            $form->add('materialCost', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Cost of components per unit',
                'scale' => 8,
            ]);
        }
    }
}
