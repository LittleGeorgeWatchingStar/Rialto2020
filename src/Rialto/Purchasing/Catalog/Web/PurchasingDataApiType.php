<?php

namespace Rialto\Purchasing\Catalog\Web;

use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Web\VersionTextType;
use Rialto\Stock\Item\Web\RohsType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating/editing purchasing data records via the API.
 */
class PurchasingDataApiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stockItem', TextEntityType::class, [
            'class' => StockItem::class,
            'mapped' => false,  // constructor parameter
        ]);
        $builder->add('supplier', TextEntityType::class, [
            'class' => Supplier::class,
        ]);
        $builder->add('catalogNumber', TextType::class, [
            'label' => 'Catalog no.',
        ]);
        $builder->add('quotationNumber', TextType::class, [
            'label' => 'Quotation no.',
            'required' => false,
        ]);
        $builder->add('manufacturerCode', TextType::class, [
            'label' => 'Manufacturer code',
            'required' => false,
        ]);
        $builder->add('supplierDescription', TextType::class, [
            'label' => 'Supplier description',
            'required' => false,
        ]);
        $builder->add('version', VersionTextType::class, [
            'required' => false,
        ]);
        $builder->add('RoHS', RohsType::class);
        $builder->add('incrementQty', IntegerType::class, [
            'label' => 'Minimum increment qty',
            'required' => false,
        ]);
        $builder->add('binSize', IntegerType::class, [
            'label' => 'Units per bin',
            'required' => false,
        ]);
        $builder->add('binStyle', EntityType::class, [
            'class' => BinStyle::class,
            'label' => 'Bin type',
        ]);
        $builder->add('turnkey', CheckboxType::class, [
            'label' => 'Is a turnkey build?',
            'required' => false,
        ]);

        $builder->add('costLevels', CollectionType::class, [
            'entry_type' => CostBreakType::class,
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'property_path' => 'costBreaks',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchasingData::class,
            'csrf_protection' => false,
            'empty_data' => function(FormInterface $form) {
                $item = $form->get('stockItem')->getData();
                if ( $item ) {
                    return new PurchasingData($item);
                } else {
                    throw new TransformationFailedException();
                }
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'PurchasingData';
    }

}
