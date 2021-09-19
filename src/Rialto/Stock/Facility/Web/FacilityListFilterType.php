<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Stock\Facility\Facility;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FacilityListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facility', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
            ])
            ->add('active', YesNoAnyType::class);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
