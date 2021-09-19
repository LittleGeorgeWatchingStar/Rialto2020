<?php

namespace Rialto\Accounting\Period\Web;


use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Period\PeriodRange;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodRangeType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'PeriodRange';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lastPeriod', EntityType::class, [
                'class' => Period::class,
                'query_builder' => function (PeriodRepository $repo) {
                    return $repo->queryRecent();
                },
                'label' => 'Period',
        ])
            ->add('numPeriods', IntegerType::class, [
                'label' => 'Number of periods',
            ])
            ->add('interval', IntegerType::class, [
                'label' => 'Interval (months)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PeriodRange::class,
            'csrf_protection' => false,
        ]);
    }


}
