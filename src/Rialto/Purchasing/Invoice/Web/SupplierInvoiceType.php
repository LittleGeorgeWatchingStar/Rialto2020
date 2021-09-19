<?php

namespace Rialto\Purchasing\Invoice\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for editing a supplier invoice and its line items.
 */
class SupplierInvoiceType extends DynamicFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('items', CollectionType::class, [
            'entry_type' => SupplierInvoiceItemType::class,
            'by_reference' => true,
            'allow_add' => true,
            'allow_delete' => false,
            'prototype' => true,
            'label' => false,
        ]);
        parent::buildForm($builder, $options);
    }

    /**
     * @param SupplierInvoice $invoice
     */
    protected function updateForm(FormInterface $form, $invoice)
    {
        assert($invoice instanceof SupplierInvoice);

        $supplier = $invoice->getSupplier();
        $form->add('purchaseOrder', EntityType::class, [
            'class' => PurchaseOrder::class,
            'query_builder' => function(PurchaseOrderRepository $repo) use ($supplier) {
                return $repo->queryUninvoicedOrdersBySupplier($supplier);
            },
            'label' => 'PO #',
            'required' => $form->getConfig()->getOption('order_required'),
            'placeholder' => '-- none --',
        ]);
        $form->add('supplierReference', TextType::class, [
            'label' => 'Supplier Ref',
        ]);
        $form->add('supplierOrderReference', TextType::class, [
            'label' => 'Supplier Order No',
            'required' => false,
        ]);
        $form->add('date', DateType::class, [
            'attr' => ['class' => 'dateInput'],
            'placeholder' => '',
        ]);
        $form->add('totalCost', NumberType::class, [
            'label' => 'Total cost',
            'scale' => SupplierInvoice::MONEY_PRECISION,
        ]);
        $form->add('trackingNumber', TextType::class, [
            'label' => 'Tracking Number',
            'empty_data' => '',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierInvoice::class,
            'order_required' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoice';
    }

}
