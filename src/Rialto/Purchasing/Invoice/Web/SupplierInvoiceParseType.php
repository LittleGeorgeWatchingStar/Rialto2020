<?php

namespace Rialto\Purchasing\Invoice\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for confirming supplier invoices that have been parsed by the
 * email reader.
 */
class SupplierInvoiceParseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* Replace the 'items' collection of the parent type with one
         * that has no prototype. */
        $builder->add('items', CollectionType::class, [
            'entry_type' => SupplierInvoiceItemType::class,
            'by_reference' => true,
            'allow_add' => false,
            'allow_delete' => false,
            'prototype' => false,
            'label' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoiceParse';
    }

    public function getParent()
    {
        return SupplierInvoiceType::class;
    }

}
