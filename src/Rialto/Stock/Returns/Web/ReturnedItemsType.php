<?php

namespace Rialto\Stock\Returns\Web;

use Rialto\Stock\Returns\ReturnedItems;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For entering parts that have been returned from a manufacturer which
 * cannot be clearly identified.
 */
class ReturnedItemsType
extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', CollectionType::class, [
            'entry_type' => ReturnedItemType::class,
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
