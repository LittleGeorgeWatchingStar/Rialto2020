<?php

namespace Rialto\Security\User\Web;


use Rialto\Stock\Facility\Facility;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return FilterForm::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', YesNoAnyType::class)
            ->add('matching', TextType::class, [
                'required' => false,
            ])
            ->add('role', TextType::class, [
                'required' => false,
            ])
            ->add('facility', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
            ])
            ->add('filter', SubmitType::class)
        ;
    }
}
