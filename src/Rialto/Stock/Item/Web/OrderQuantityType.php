<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Stock\Item\StockItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing a stock item's economic order quantity (EOQ).
 */
class OrderQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('orderQuantity', NumberType::class, [
            'label' => 'EOQ',
            'label_attr' => [
                'title' => 'Economic order quantity',
                'class' => 'tooltip',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItem::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'OrderQuantity';
    }

}
