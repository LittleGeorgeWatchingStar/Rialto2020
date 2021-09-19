<?php

namespace Rialto\Manufacturing\Customization\Web;

use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Substitution;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for editing Customization records.
 */
class CustomizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stockCodePattern', TextType::class, [
            'label' => 'Stock code pattern',
            'label_attr' => [
                'class' => 'tooltip',
                'title' => 'Use "%" as a wildcard',
            ],
        ]);
        $builder->add('name', TextType::class, [
            'label' => "Name",
            'required' => true,
        ]);
        $builder->add('strategies', CustomizationStrategyType::class, [
            'required' => false,
            'multiple' => true,
            'expanded' => true,
        ]);
        $builder->add('substitutions', CollectionType::class, [
            'entry_type' => EntityType::class,
            'allow_add'    => true,
            'allow_delete' => true,
            'prototype'    => true,
            'entry_options' => [
                'class' => Substitution::class,
                'choice_label' => 'longDescription',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customization::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Customization';
    }

}
