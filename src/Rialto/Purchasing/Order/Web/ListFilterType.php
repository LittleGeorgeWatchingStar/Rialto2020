<?php

namespace Rialto\Purchasing\Order\Web;

use Gumstix\Time\DateRangeType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For filtering the list of purchase orders.
 */
class ListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderNo', SearchType::class, [
                'required' => false,
            ])
            ->add('supplierRef', SearchType::class, [
                'required' => false,
            ])
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('stockItem', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
            ])
            ->add('exclude', SearchType::class, [
                'required' => false,
                'label' => 'Exclude initiator(s)',
            ])
            ->add('deliveryLocation', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
                'label' => 'Ship to',
            ])
            ->add('orderDate', DateRangeType::class, [
                'required' => false,
                'start_label' => 'Ordered between'
            ])
            ->add('printed', YesNoAnyType::class)
            ->add('completed', YesNoAnyType::class)
        ;
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
