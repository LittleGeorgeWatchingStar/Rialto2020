<?php

namespace Rialto\Shipping\Export\Web;

use Gumstix\GeographyBundle\Form\CountryType;
use Rialto\Shipping\Export\ShipmentProhibition;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Web\EccnType;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating/editing shipment prohibitions.
 *
 * @see ShipmentProhibition
 */
class ShipmentProhibitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prohibitedCountry', CountryType::class, [
                'label' => 'Prohibited country',
            ])
            ->add('stockItem', JsEntityType::class, [
                'class' => StockItem::class,
                'label' => 'Stock item',
                'required' => false,
            ])
            ->add('stockCategory', EntityType::class, [
                'class' => StockCategory::class,
                'label' => 'Stock category',
                'required' => false,
            ])
            ->add('eccnCode', EccnType::class, [
                'label' => 'ECCN code',
                'required' => false,
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShipmentProhibition::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ShipmentProhibition';
    }

}
