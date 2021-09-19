<?php

namespace Rialto\Sales\Returns\Receipt\Web;

use Rialto\Sales\Returns\Receipt\SalesReturnReceipt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form used for receiving returned merchandise.
 */
class SalesReturnReceiptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lineItems', CollectionType::class, [
            'entry_type' => SalesReturnItemReceiptType::class,
            'by_reference' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesReturnReceipt';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesReturnReceipt::class
        ]);
    }
}
