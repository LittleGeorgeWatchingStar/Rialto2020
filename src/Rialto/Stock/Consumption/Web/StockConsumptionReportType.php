<?php

namespace Rialto\Stock\Consumption\Web;

use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Consumption\StockConsumptionReport;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockConsumptionReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('startDate', DateType::class, [
            'label' => 'Start date',
            'format' => 'yyyy-MM-dd',
            'required' => true,
            'error_bubbling' => true,
        ])
        ->add('salesType', EntityType::class, [
            'label' => 'Sales type',
            'class' => SalesType::class,
            'required' => false,
            'placeholder' => '-- all --',
        ])
        ->add('showTurnkey', ChoiceType::class, [
            'choices' => [
                'consignment only' => false,
                'turnkey only' => true,
            ],
            'label' => 'Show turnkey?',
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockConsumptionReport::class,
            'csrf_protection' => false,
            'method' => 'get',
            'allow_extra_fields' => true,
        ]);
    }
}
