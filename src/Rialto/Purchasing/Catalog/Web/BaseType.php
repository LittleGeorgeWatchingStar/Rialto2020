<?php

namespace Rialto\Purchasing\Catalog\Web;

use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Item\Web\RohsType;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base fields for creating/editing a purchasing data record.
 */
class BaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('catalogNumber', TextType::class, [
            'label' => 'Catalog no.',
        ]);
        $builder->add('manufacturer', JsEntityType::class, [
            'class' => Manufacturer::class,
        ]);
        $builder->add('manufacturerCode', TextType::class, [
            'label' => 'Manufacturer code',
            'required' => false,
        ]);

        $builder->add('RoHS', RohsType::class, [
            'placeholder' => '-- choose --',
        ]);
        $builder->add('incrementQty', IntegerType::class, [
            'label' => 'Increment qty',
            'required' => true,
        ]);
        $builder->add('binSize', IntegerType::class, [
            'label' => 'Units per bin',
            'required' => false,
        ]);
        $builder->add('binStyle', EntityType::class, [
            'class' => BinStyle::class,
            'label' => 'Bin type',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', PurchasingData::class);
    }

}
