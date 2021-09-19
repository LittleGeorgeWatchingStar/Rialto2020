<?php

namespace Rialto\Measurement\Temperature\Web;

use Rialto\Measurement\Temperature\TemperatureRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemperatureRangeType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('min', NumberType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'min'],
            ])
            ->add('max', NumberType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'max'],
            ])
            ->addModelTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', null);
        $resolver->setDefault('attr', ['class' => 'temp-range']);
    }

    /**
     * @param TemperatureRange $range
     */
    public function transform($range)
    {
        if (!$range) {
            return [];
        }
        return [
            'min' => $range->getMin(),
            'max' => $range->getMax(),
        ];
    }

    public function reverseTransform($array)
    {
        $min = isset($array['min']) ? $array['min'] : null;
        $max = isset($array['max']) ? $array['max'] : null;
        return new TemperatureRange($min, $max);
    }
}

