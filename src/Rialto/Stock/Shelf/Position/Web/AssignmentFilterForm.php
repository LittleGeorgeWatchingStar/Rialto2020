<?php

namespace Rialto\Stock\Shelf\Position\Web;


use Rialto\Stock\Bin\Web\BinFilterBaseType;
use Rialto\Stock\Bin\Web\BinStyleType;
use Rialto\Stock\Shelf\Velocity\Web\VelocityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class AssignmentFilterForm extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', SearchType::class, [
                'required' => false,
                'label' => 'SKU',
            ])
            ->add('velocity', VelocityType::class, [
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('styles', BinStyleType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->remove('_limit')
            ->add('_limit', NumberType::class, [
                'required' => false,
                'empty_data' => '100',
            ])
            ->add('_start', NumberType::class, [
                'required' => false,
            ]);
    }

    public function getParent()
    {
        return BinFilterBaseType::class;
    }

}
