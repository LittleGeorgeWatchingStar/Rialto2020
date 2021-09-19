<?php

namespace Rialto\Stock\Count\Web;

use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CountListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('counted', YesNoAnyType::class)
            ->add('approved', YesNoAnyType::class);
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
