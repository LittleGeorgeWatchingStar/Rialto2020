<?php

namespace Rialto\Stock\Publication\Web;

use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Publication\UploadPublication;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stockItem', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
            ])
            ->add('purpose', ChoiceType::class, [
                'choices' => UploadPublication::getPurposeOptions(),
                'required' => false,
            ])
            ->add('filter', SubmitType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return FilterForm::class;
    }

}
