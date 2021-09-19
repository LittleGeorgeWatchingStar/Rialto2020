<?php

namespace Rialto\Stock\Item\Version\Web;


use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * For filtering the list view of ItemVersion records.
 *
 * @see ItemVersion
 */
class VersionListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matching', SearchType::class, [
                'required' => false,
            ])
            ->add('component', JsEntityType::class, [
                'class' => StockItem::class,
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'class' => StockCategory::class,
                'required' => false,
            ])
            ->add('discontinued', YesNoAnyType::class)
            ->add('hasBeenProduced', YesNoAnyType::class)
            ->add('hasBeenSold', YesNoAnyType::class)
            ->add('_limit', IntegerType::class)
            ->add('filter', SubmitType::class);
    }

    public function getParent()
    {
        return FilterForm::class;
    }
}
