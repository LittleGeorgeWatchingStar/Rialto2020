<?php

namespace Rialto\Stock\Shelf\Shelf\Web;


use Rialto\Geometry\Web\Vector3DType;
use Rialto\Stock\Bin\Web\BinStyleType;
use Rialto\Stock\Shelf\Velocity\Web\VelocityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShelfEditForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('velocity', VelocityType::class)
            ->add('binStyles', BinStyleType::class, [
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('dimensions', Vector3DType::class, [
                'attr' => ['class' => 'dimensions numeric'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ShelfEdit::class);
    }

}
