<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Measurement\Temperature\Web\TemperatureRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For creating a new part.
 *
 * "Part" means not a board, product, etc.
 */
class CreatePartType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sku', TextType::class, [
            'label' => 'SKU',
        ]);
        $builder->add('economicOrderQty', IntegerType::class, [
            'label' => 'EOQ',
            'required' => false,
            'label_attr' => ['title' => 'Economic order qty'],
        ]);
        $builder->add('temperatureRange', TemperatureRangeType::class, [
            'required' => false,
        ]);
    }
}
