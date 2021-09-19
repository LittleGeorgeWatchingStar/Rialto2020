<?php

namespace Rialto\Purchasing\Receiving\Web;


use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoodsReceivedItemsSelectAndDeleteType extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'reverse_items_qty';
    }

    protected function updateForm(FormInterface $form, $purchData)
    {
        $form->add('items', CollectionType::class, [
            'entry_type' => GoodsReceiveItemType::class,
            'entry_options' => ['label' => false],
            'by_reference' => false,
            'prototype' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class', GoodsReceivedNotice::class
        ]);
    }
}
