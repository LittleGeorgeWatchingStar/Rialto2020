<?php

namespace Rialto\Sales\Customer\Web;


use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matching', SearchType::class, [
                'required' => false,
            ])
            ->add('name', SearchType::class, [
                'required' => false,
            ])
            ->add('email', SearchType::class, [
                'required' => false,
            ])
            ->add('sourceID', SearchType::class, [
                'required' => false,
                'label' => 'Source ID',
            ])
            ->add('internal', YesNoAnyType::class, [
                'label' => 'Internal customer?',
            ])
            ->add('filter', SubmitType::class);
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
