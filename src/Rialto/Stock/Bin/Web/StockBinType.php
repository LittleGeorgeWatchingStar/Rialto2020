<?php

namespace Rialto\Stock\Bin\Web;

use Rialto\Stock\Bin\BinStyle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for creating or editing stock bins.
 */
class StockBinType extends AbstractType
{
    public function getParent()
    {
        return BinQtyType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('binStyle', EntityType::class, [
            'class' => BinStyle::class,
            'label' => 'Bin style',
        ]);
    }
}
