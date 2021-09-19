<?php

namespace Rialto\Stock\Transfer\Web;

use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\TextEntityType;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TransferListFiltersType extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', SearchType::class, [
                'required' => false,
                'label' => 'ID',
            ])
            ->add('destination', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function(FacilityRepository $repo) {
                    return $repo->queryValidDestinations();
                },
                'required' => false,
                'placeholder' => '-- any --',
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Sent since',
                'required' => false,
            ])
            ->add('received', YesNoAnyType::class, [
                'label' => 'Received?',
                'required' => false,
            ])
            ->add('missingItems', CheckboxType::class, [
                'label' => 'Missing items?',
                'required' => false,
            ])
            ->add('bin', TextEntityType::class, [
                'class' => StockBin::class,
                'label' => 'Containing bin',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filter',
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

}
