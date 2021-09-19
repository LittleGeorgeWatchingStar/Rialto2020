<?php

namespace Rialto\Manufacturing\WorkOrder\Web;


use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ListFilterType
extends AbstractType
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
            ->add('workOrder', SearchType::class, [
                'required' => false,
                'label' => 'WO #',
            ])
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function (FacilityRepository $repo) {
                    return $repo->queryValidDestinations();
                },
                'required' => false,
                'placeholder' => '-- all --',
                'label' => 'at location',
            ])
            ->add('stockItem', SearchType::class, [
                'required' => false,
                'label' => 'for item',
            ])
            ->add('createdStart', DateType::class, [
                'required' => false,
                'label' => 'Created between',
            ])
            ->add('createdEnd', DateType::class, [
                'required' => false,
                'label' => 'and',
            ])
            ->add('sellable', YesNoAnyType::class)
            ->add('closed', YesNoAnyType::class)
            ->add('rework', YesNoAnyType::class)
            ->add('parents', YesNoAnyType::class)
            ->add('overdue', YesNoAnyType::class)
            ->add('filter', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FilterForm::class;
    }
}
