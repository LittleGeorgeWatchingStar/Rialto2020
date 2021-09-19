<?php

namespace Rialto\Stock\Shelf\Rack\Web;

use Rialto\Stock\Bin\Web\BinStyleType;
use Rialto\Stock\Facility\Web\ActiveFacilityType;
use Rialto\Stock\Shelf\Velocity\Web\VelocityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RackMakerForm extends AbstractType
{
    public function getParent()
    {
        return RackBaseType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facility', ActiveFacilityType::class)
            ->add('numShelves', IntegerType::class)
            ->add('positionsPerShelf', IntegerType::class, [
                'label' => 'Bins per shelf',
            ])
            ->add('defaultVelocity', VelocityType::class)
            ->add('binStyles', BinStyleType::class, [
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', RackMaker::class);
    }

}
