<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Stock\Item\StockItemAttribute;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For filtering the list of stock items.
 */
class ListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matching', TextType::class, [
                'required' => false,
            ])
            ->add('manufacturerCode', TextType::class, [
                'required' => false,
            ])
            ->add('attribute', ChoiceType::class, [
                'required' => false,
                'choices' => StockItemAttribute::getChoices(),
            ])
            ->add('discontinued', YesNoAnyType::class)
            ->add('filter', SubmitType::class);
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
