<?php

namespace Rialto\Stock\Count\Web;

use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Count\StockCount;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Web\Form\TextEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for creating a new StockCount.
 */
class StockCountRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('location', EntityType::class, [
            'class' => Facility::class,
            'query_builder' => function(FacilityRepository $repo) {
                return $repo->queryValidDestinations();
            },
            'placeholder' => '-- choose --',
        ])
        ->add('bins', CollectionType::class, [
            'entry_type' => TextEntityType::class,
            'entry_options' => [
                'class' => StockBin::class,
                'required' => false,
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'constraints' => new Assert\Count([
                'min' => 1,
                'minMessage' => 'At least one bin is required.',
            ]),
        ])
        ->add('sendEmail', CheckboxType::class, [
            'required' => false,
            'data' => false,
            'mapped' => false,
        ])
        ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockCount::class,
            'attr' => ['class' => 'standard'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockCount';
    }

}
