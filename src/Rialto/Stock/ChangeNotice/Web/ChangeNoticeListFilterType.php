<?php

namespace Rialto\Stock\ChangeNotice\Web;

use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangeNoticeListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stockItem', JsEntityType::class, [
               'class' => StockItem::class,
               'label' => 'Stock Item',
               'required' => false,
            ])
            ->add('description', SearchType::class, [
                'label' => 'Description',
                'required' => false,
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
