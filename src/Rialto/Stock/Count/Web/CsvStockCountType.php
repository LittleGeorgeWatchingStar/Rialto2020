<?php

namespace Rialto\Stock\Count\Web;

use Rialto\Stock\Count\CsvStockCount;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CsvStockCountType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'csv_stock_count';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function (FacilityRepository $repo) {
                    return $repo->queryValidDestinations();
                },
                'placeholder' => '-- choose --',
                'label' => 'csv_stock_count.location',
            ])
            ->add('asOf', DateType::class, [
                'label' => 'csv_stock_count.asOf',
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'csv_stock_count.uploadedFile',
            ])
            ->add('columnHeadings', CollectionType::class, [
                'entry_type' => TextType::class,
                'label' => 'csv_stock_count.columnHeadings',
            ])
            ->add('viewDetail', ChoiceType::class, [
                'choices' => [
                    'Summary' => CsvStockCount::VIEW_SUMMARY,
                    'All modified bins' => CsvStockCount::VIEW_MODIFIED,
                    'All bins' => CsvStockCount::VIEW_ALL,
                ],
                'label' => 'csv_stock_count.viewDetail',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CsvStockCount::class);
        $resolver->setDefault('translation_domain', 'form');
    }

}
