<?php

namespace Rialto\Purchasing\Quotation\Web;

use Rialto\Purchasing\Quotation\QuotationCsvMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form that maps a supplier quotation csv file
 * to the fields of PurchasingData and CostBreak records.
 */
class QuotationCsvMappingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quotationNumber', HiddenType::class)
            ->add('mapping', CollectionType::class, [
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'choices' => $this->getMappingChoices(),
                    'placeholder' => '-- assign --',
                    'required' => false,
                ],
                'allow_add' => true,
            ])
            ->add('csvData', CollectionType::class, [
                'entry_type' => CollectionType::class,
                'entry_options' => [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'required' => false,
                ],
                'allow_add' => true,
            ]);
    }

    private function getMappingChoices()
    {
        $choices = [
            'catalogNumber',
            'manufacturerCode',
            'stockCode',
            'supplierDescription',
            'RoHS',
            'minimumOrderQty',
            'supplierLeadTime',
            'manufacturerLeadTime',
            'incrementQty',
            'binSize',
            'cost',
        ];
        return array_combine($choices, $choices);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QuotationCsvMapping::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'csv';
    }

}
