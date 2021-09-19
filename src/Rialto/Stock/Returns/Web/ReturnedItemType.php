<?php

namespace Rialto\Stock\Returns\Web;


use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\Web\BinStyleType;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Used by ReturnedItems type for items which cannot be clearly identified.
 */
class ReturnedItemType
extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'ReturnedItem';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bin', TextEntityType::class, [
                'class' => StockBin::class,
                'required' => false,
                'label' => 'Bin ID',
            ])
            ->add('item', TextEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
                'label' => 'SKU',
            ])
            ->add('binStyle', BinStyleType::class, [
                'placeholder' => '-- choose --',
            ])
            ->add('manufacturerCode', TextType::class, [
                'required' => false,
                'label' => 'MPN',
                'label_attr' => ['title' => 'Manufacturer part number'],
            ])
            ->add('catalogNumber', TextType::class, [
                'required' => false,
                'label' => 'Catalog no',
                'label_attr' => ['title' => 'Supplier catalog number']
            ])
            ->add('buildPO', TextEntityType::class, [
                'class' => PurchaseOrder::class,
                'required' => false,
                'label' => 'Build PO',
                'label_attr' => [
                    'title' => 'The work order PO to which this part was allocated',
                ]
            ])
            ->add('partsPO', TextEntityType::class, [
                'class' => PurchaseOrder::class,
                'required' => false,
                'label' => 'Parts PO',
                'label_attr' => [
                    'title' => 'The parts PO from which this part originated',
                ]
            ])
            ->add('supplierReference', TextType::class, [
                'required' => false,
                'label' => 'Supplier ref',
                'label_attr' => ['title' => 'Supplier order number']
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
                'label' => 'Quantity on bin',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReturnedItem::class,
        ]);
    }
}
