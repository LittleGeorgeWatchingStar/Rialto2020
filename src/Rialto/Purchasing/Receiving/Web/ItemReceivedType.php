<?php

namespace Rialto\Purchasing\Receiving\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for receiving a purchase order item.
 */
class ItemReceivedType extends DynamicFormType
{
    /**
     * @param ItemReceived $itemRec
     */
    protected function updateForm(FormInterface $form, $itemRec)
    {
        if ($itemRec instanceof AutoReceived) {
            $form->add('received', CheckboxType::class, [
                'required' => false,
                'label' => 'Received?',
            ]);
        } elseif ($itemRec instanceof StockReceived) {
            $form->add('bins', CollectionType::class, [
                'entry_type' => BinReceivedType::class,
                'entry_options' => [
                    'item_received' => $itemRec,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => 'Bins received:',
            ]);
        } else {
            $form->add('qtyReceived', IntegerType::class, [
                'label' => 'Qty received',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemReceived::class,
        ]);
    }
}

