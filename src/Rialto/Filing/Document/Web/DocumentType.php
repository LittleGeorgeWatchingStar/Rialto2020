<?php

namespace Rialto\Filing\Document\Web;

use Rialto\Filing\Document\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating/editing documents.
 *
 * @see Document
 */
class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
            ->add('templateFile', FileType::class, [
                'required' => false,
            ])
            ->add('scheduleDay', IntegerType::class, [
                'required' => false,
                'label' => 'Schedule: day of month',
            ])
            ->add('scheduleMonths', ChoiceType::class, [
                'required' => false,
                'label' => 'Schedule: months',
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->getMonthChoices(),
                'attr' => ['class' => 'checkbox_group'],
            ])
            ->add('fields', CollectionType::class, [
                'entry_type' => DocumentFieldType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
            ]);
    }

    private function getMonthChoices()
    {
        $choices = [];
        foreach (range(1, 12) as $number) {
            $label = date('F', strtotime("2012-$number-01"));
            $choices[$label] = $number;
        }
        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Document';
    }

}
