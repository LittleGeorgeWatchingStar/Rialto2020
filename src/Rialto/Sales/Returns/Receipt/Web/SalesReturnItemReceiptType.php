<?php

namespace Rialto\Sales\Returns\Receipt\Web;

use Rialto\Sales\Returns\Receipt\SalesReturnItemReceipt;
use Rialto\Stock\Bin\BinStyle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for entering how much of a returned item has been received.
 */
class SalesReturnItemReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', IntegerType::class);
        $builder->add('binStyle', EntityType::class, [
            'class' => BinStyle::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesReturnItemReceipt';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesReturnItemReceipt::class
        ]);
    }
}
