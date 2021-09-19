<?php

namespace Rialto\Purchasing\Invoice\Web;

use Gumstix\GeographyBundle\Form\CountryType;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\TextEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for editing an item in a supplier invoice.
 */
class SupplierInvoiceItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stockItem', TextEntityType::class, [
            'class' => StockItem::class,
            'required' => false,
            'invalid_message' => 'Invalid stock item.',
        ]);
        $builder->add('description', TextType::class);
        $builder->add('qtyOrdered', IntegerType::class, [
            'required' => false,
            'attr' => ['class' => 'autoPop'],
        ]);
        $builder->add('qtyInvoiced', IntegerType::class,[
            'attr' => ['class' => 'autoPop'],
        ]);
        $builder->add('lineNumber', IntegerType::class);
        $builder->add('unitCost', NumberType::class, [
            'scale' => SupplierInvoiceItem::MONEY_PRECISION
        ]);
        $builder->add('extendedCost', NumberType::class, [
            'scale' => SupplierInvoice::MONEY_PRECISION
        ]);
        $builder->add('tariff', NumberType::class, [
            'scale' => SupplierInvoice::MONEY_PRECISION,
            'required' => false,
            'label' => 'Tariff',
        ]);
        $builder->add('harmonizationCode', TextType::class, [
            'required' => false,
            'label' => 'Harm.'
        ]);
        $builder->add('eccnCode', TextType::class, [
            'required' => false,
            'label' => 'ECCN',
        ]);
        $builder->add('countryOfOrigin', CountryType::class, [
            'required' => false,
            'label' => 'Origin',
        ]);
        $builder->add('leadStatus', TextType::class, [
            'required' => false,
            'label' => 'Lead',
        ]);
        $builder->add('rohsStatus', TextType::class, [
            'required' => false,
            'label' => 'RoHS',
        ]);
        $builder->add('reachStatus', TextType::class, [
            'required' => false,
            'label' => 'REACH',
        ]);
        $builder->add('reachDate', DateType::class, [
            'required' => false,
            'label' => 'REACH',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierInvoiceItem::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoiceItem';
    }

}
