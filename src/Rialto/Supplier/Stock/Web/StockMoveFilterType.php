<?php

namespace Rialto\Supplier\Stock\Web;


use Gumstix\Time\DateRangeType;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class StockMoveFilterType extends AbstractType
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
            ->add('date', DateRangeType::class, [
                'required' => false,
            ])
            ->add('reference', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'substring match'],
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
