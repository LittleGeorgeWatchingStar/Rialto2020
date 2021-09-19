<?php

namespace Rialto\Stock\Move\Web;

use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class StockMoveListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', JsEntityType::class, [
                'class' => StockItem::class,
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => 'SKU',
                'attr' => ['placeholder' => 'substring match'],
            ])
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'required' => false,
                'placeholder' => '-- all --',
            ])
            ->add('bin', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'exact ID or "none"'],
            ])
            ->add('startDate', DateType::class, [
                'required' => false,
                'label' => 'Between',
                'attr' => ['placeholder' => 'start date'],
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'label' => 'and',
                'attr' => ['placeholder' => 'end date'],
            ])
            ->add('reference', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'substring match'],
            ])
            ->add('showTransit', CheckboxType::class, [
                'label' => 'Show "In transit"?',
                'required' => false,
                'value' => 'yes',
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'label' => 'Max records',
                'attr' => ['placeholder' => '0 for no limit'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FilterForm::class;
    }
}
