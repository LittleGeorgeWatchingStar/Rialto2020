<?php

namespace Rialto\Sales\Invoice\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Shipping\Shipment\Web\ShipmentOptionsType;
use Rialto\Shipping\Shipment\Web\ShipmentPackageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesInvoiceType extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'SalesInvoice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesInvoice::class
        ]);
    }

    /**
     * @param FormInterface $form
     * @param SalesInvoice $invoice
     */
    public function updateForm(FormInterface $form, $invoice)
    {
        $form->add('lineItems', CollectionType::class, [
            'entry_type' => SalesInvoiceItemType::class,
            'allow_add' => false,
            'prototype' => true,
            'by_reference' => false,
            'error_bubbling' => true,
        ]);
        $form->add('closeOrder', ChoiceType::class, [
            'choices' => [
                'Automatically put balance on back order' => 0,
                'Cancel any quantities not delivered' => 1,
            ],
            'label' => 'Action for balance'
        ]);
        $form->add('comments', TextareaType::class, ['required' => false]);
        if (!$invoice->containsShippableItems()) {
            return;
        }
        $form->add('shippingMethod', ShipmentOptionsType::class, [
            'salesOrder' => $invoice,
            'required' => false,
        ]);
        $form->add('packages', CollectionType::class, [
            'entry_type' => ShipmentPackageType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'error_bubbling' => true,
            'label' => 'Packages (kg)',
            'entry_options' => [
                'attr' => ['class' => 'package'],
                'weight' => [
                    'attr' => ['class' => 'recalculate'],
                    'label' => false,
                ]
            ],
        ]);
        $form->add('shippingPrice', MoneyType::class, [
            'currency' => Currency::USD,
            'error_bubbling' => true,
            'attr' => ['class' => 'recalculate'],
        ]);

        if ($invoice->isTrackingNumberRequired()) {
            $form->add('trackingNumber', TextType::class);
        }
    }

}

class SalesInvoiceItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('qtyToShip', IntegerType::class, [
            'error_bubbling' => true,
            'attr' => ['class' => 'recalculate'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesInvoiceItem::class
        ]);
    }
}
