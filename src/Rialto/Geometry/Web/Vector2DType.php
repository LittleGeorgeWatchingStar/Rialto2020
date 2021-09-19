<?php

namespace Rialto\Geometry\Web;


use Gumstix\Geometry\Vector2D;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Vector2DType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('x', NumberType::class, [
                'scale' => $options['scale'],
                'attr' => $options['attr'],
            ])
            ->add('y', NumberType::class, [
                'scale' => $options['scale'],
                'attr' => $options['attr'],
            ]);
        $builder->addModelTransformer(new CallbackTransformer(
                function ($vector) {
                    if (!$vector) {
                        return [];
                    }
                    if ($vector instanceof Vector2D) {
                        return $vector->toArray();
                    }
                    throw new UnexpectedTypeException($vector, Vector2D::class);
                },
                function ($formData) {
                    if (count($formData) === 0) {
                        return null;
                    }
                    if (is_array($formData)) {
                        return new Vector2D($formData['x'], $formData['y']);
                    }
                    throw new UnexpectedTypeException($formData, 'array');
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', null); // the data transformer does this for us
        $resolver->setDefault('scale', null);
    }

}
