<?php

namespace Rialto\Stock\Returns\Web;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Returns\ReturnedItems;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For entering the IDs of bins that have been returned from a manufacturer.
 */
class ReturnedBinsType
extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('source', EntityType::class, [
            'class' => Facility::class,
            'query_builder' => function(FacilityRepository $repo) {
                return $repo->queryValidDestinations();
            },
            'placeholder' => '-- choose --',
            'label' => 'From',
        ]);

        $builder->add('items', CollectionType::class, [
            'entry_type' => ReturnedBinType::class,
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'required' => false,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReturnedItems::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ReturnedItems';
    }
}
