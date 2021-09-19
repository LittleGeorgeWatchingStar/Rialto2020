<?php

namespace Rialto\Purchasing\Invoice\Web;

use Rialto\Purchasing\Invoice\SupplierInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form used to approve a supplier invoice and thereby create a
 * supplier transaction for the invoice.
 */
class SupplierInvoiceApprovalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', CollectionType::class, [
            'entry_type' => SupplierInvoiceItemApprovalType::class,
            'allow_add' => false,
            'allow_delete' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierInvoice::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoice';
    }

}
