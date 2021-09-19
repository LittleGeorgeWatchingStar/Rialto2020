<?php

namespace Rialto\Geometry\Web;


use Gumstix\Geometry\Vector3D;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Vector3DType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('x', NumberType::class)
            ->add('y', NumberType::class)
            ->add('z', NumberType::class);
        $builder->addModelTransformer(new CallbackTransformer(
            function ($vector) {
                if (!$vector) {
                    return [];
                }
                if ($vector instanceof Vector3D) {
                    return $vector->toArray();
                }
                throw new UnexpectedTypeException($vector, Vector3D::class);
            },
            function ($formData) {
                if (count($formData) === 0) {
                    return null;
                }
                if (is_array($formData)) {
                    return new Vector3D($formData['x'], $formData['y'], $formData['z']);
                }
                throw new UnexpectedTypeException($formData, 'array');
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', null); // the data transformer does this for us
    }
}
