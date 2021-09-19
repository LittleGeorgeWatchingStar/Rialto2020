<?php

namespace Rialto\Stock\Bin\Web;


use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Shelf\Rack;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base form type for filtering stock bins.
 */
class BinFilterBaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facility', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('rack', EntityType::class, [
                'class' => Rack::class,
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('isShelved', YesNoAnyType::class, [
                'label' => 'Shelved?',
            ])
            ->add('empty', CheckboxType::class, [
                'value' => 'yes',
                'label' => 'Show empty?',
                'required' => false,
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
