<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MissingItemsType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('since', DateType::class, [
            'input' => 'string',
            'required' => false,
        ]);
        $builder->add('transfer', TextType::class, [
            'required' => false,
            'label' => 'Transfer ID',
        ]);
        $builder->add('purchaseOrder', TextType::class, [
            'required' => false,
            'label' => 'PO number',
        ]);
        $builder->add('stockBin', TextType::class, [
            'required' => false,
            'label' => 'Stock bin',
        ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
