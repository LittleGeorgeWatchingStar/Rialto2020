<?php

namespace Rialto\Sales\Stats\Web;

use Rialto\Sales\Stats\SalesStatOptions;
use Rialto\Sales\Type\SalesType;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesStatOptionsType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('targetDays', IntegerType::class, [
                'label' => 'Target days of stock'
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Starting date of review',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('salesType', EntityType::class, [
                'class' => SalesType::class,
                'choice_label' => 'name',
                'label' => 'Order type',
                'required' => false,
                'placeholder' => '-- all --',
            ])
            ->add('filters', ChoiceType::class, [
                'choices' => SalesStatOptions::getFilterChoices(),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'placeholder' => 'all',
                'label' => 'Limit results to',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesStatOptions::class,
            'csrf_protection' => false,
            'method' => 'get',
        ]);
    }
}
