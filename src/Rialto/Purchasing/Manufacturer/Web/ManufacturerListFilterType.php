<?php

namespace Rialto\Purchasing\Manufacturer\Web;

use Rialto\Web\Form\FilterForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For filtering the list of part manufacturers.
 */
class ManufacturerListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matching', TextType::class, [
                'required' => false,
            ])
            ->add('_limit', IntegerType::class, [
            'required' => false,
            'label' => 'Max records to show',
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
