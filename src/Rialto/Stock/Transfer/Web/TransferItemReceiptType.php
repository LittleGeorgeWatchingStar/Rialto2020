<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Stock\Transfer\TransferItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for receiving TransferItems.
 */
class TransferItemReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('qtyReceived', NumberType::class, [
            'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_DOWN,
            'scale' => 0,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'TransferItem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransferItem::class
        ]);
    }

}
