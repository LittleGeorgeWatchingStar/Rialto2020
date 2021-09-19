<?php

namespace Rialto\Purchasing\Invoice\Web;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class SupplierInvoiceListFilter extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
                'required' => false,
            ])
            ->add('purchaseOrder', SearchType::class, [
                'required' => false,
            ])
            ->add('reference', SearchType::class, [
                'required' => false,
            ])
            ->add('since', DateType::class, [
                'required' => false,
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
