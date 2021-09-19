<?php

namespace Rialto\Panelization\Web;

use Rialto\Panelization\Panelizer;
use Rialto\Purchasing\Supplier\Orm\SupplierRepository;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Web\Form\CommaDelimitedArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanelizerType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'Panelizer';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function(FacilityRepository $repo) {
                    return $repo->queryActiveManufacturers();
                },
                'label' => 'Manufacturer',
            ])
            ->add('pcbSuppliers', EntityType::class, [
                'class' => Supplier::class,
                'query_builder' => function (SupplierRepository $repo) {
                    return $repo->queryByStockCategory(StockCategory::PCB);
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Request PCB quotes from',
            ])
            ->add('panelsToOrder', IntegerType::class)
            ->add('leadTimes', CommaDelimitedArrayType::class, [
                'required' => false,
            ])
            ->add('versions', CollectionType::class, [
                'entry_type' => BoardOfPanelType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => 'Item versions and boards per panel',
                'error_bubbling' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Panelizer::class,
            'validation_groups' => ['Default', 'dimensions'],
        ]);
    }
}
