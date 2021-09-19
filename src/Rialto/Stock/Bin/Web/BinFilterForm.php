<?php

namespace Rialto\Stock\Bin\Web;

use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BinFilterForm extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return BinFilterBaseType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stockItem', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
            ]);
    }
}
