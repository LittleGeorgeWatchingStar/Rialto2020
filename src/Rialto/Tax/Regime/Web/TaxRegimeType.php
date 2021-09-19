<?php

namespace Rialto\Tax\Regime\Web;

use Rialto\Tax\Regime\TaxRegime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing tax regimes.
 */
class TaxRegimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('county', TextType::class, [
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'required' => false,
            ])
            ->add('description', TextType::class, [
            ])
            ->add('acronym', TextType::class, [
                'required' => false,
            ])
            ->add('regimeCode', TextType::class, [
                'required' => false,
                'label' => 'Regime code',
            ])
            ->add('taxRate', PercentType::class, [
                'scale' => 4,
                'label' => 'Tax rate',
            ]);

        $thisYear = (int) date('Y');
        $yearRange = range(2000, $thisYear + 5);
        $builder
            ->add('startDate', DateType::class, [
                'label' => 'Start date',
                'years' => $yearRange,
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'label' => 'End date',
                'years' => $yearRange,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaxRegime::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'TaxRegime';
    }

}
