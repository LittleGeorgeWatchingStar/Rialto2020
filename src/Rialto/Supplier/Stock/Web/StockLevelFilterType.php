<?php

namespace Rialto\Supplier\Stock\Web;


use Rialto\Web\Form\FilterForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class StockLevelFilterType extends AbstractType
{
    public function getParent()
    {
        return FilterForm::class;
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', SearchType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'filter by SKU...',
                ],
            ]);
    }
}
