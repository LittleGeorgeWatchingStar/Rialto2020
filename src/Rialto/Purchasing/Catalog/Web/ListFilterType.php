<?php

namespace Rialto\Purchasing\Catalog\Web;


use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListFilterType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('_limit');
        $builder
            ->add('stockItem', JsEntityType::class, [
                'class' => StockItem::class,
            ])
            ->add('supplier', JsEntityType::class, [
                'class' => Supplier::class,
            ])
            ->add('manufacturer', JsEntityType::class, [
                'class' => Manufacturer::class,
            ])
            ->add('hasManufacturer', YesNoAnyType::class)
            ->add('matching', TextType::class, [
                'attr' => ['placeholder' => 'Search...'],
                'required' => false,
            ])
            ->add('preferred', YesNoAnyType::class, [
                'label' => 'Preferred?',
            ])
            ->add('active', YesNoAnyType::class, [
                'label' => 'Active?',
            ])
            ->add('canSync', YesNoAnyType::class, [
                'label' => 'Can Sync?',
            ])
            ->add('inGeppettoBom', YesNoAnyType::class, [
                'label' => 'In Geppetto Module BOM?',
            ])
            ->add('_order', ChoiceType::class, [
                'choices' => [
                    'SKU' => 'sku',
                    'Supplier' => 'supplier',
                    'Catalog No.' => 'catalogNo',
                ],
                'required' => false,
                'label' => 'Sort by',
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'attr' => ['title' => 'Enter 0 for no limit']
            ])
            ->add('filter', SubmitType::class)
            ->add('csv', SubmitType::class, [
                'label' => 'Download CSV',
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => false,
        ]);
    }
}
