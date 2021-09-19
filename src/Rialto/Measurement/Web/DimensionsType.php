<?php

namespace Rialto\Measurement\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for entering the dimensions
 */
class DimensionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('x', NumberType::class, [
            'scale' => 4
        ])
        ->add('y', NumberType::class, [
            'scale' => 4
        ])
        ->add('z', NumberType::class, [
            'scale' => 4
        ]);
        $builder->addModelTransformer(new DimensionsToArrayTransformer());
    }

    public function getBlockPrefix()
    {
        return 'Dimensions';
    }

}
