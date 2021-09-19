<?php

namespace Rialto\Sales\Customer\Web;


use Rialto\Sales\Salesman\Salesman;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\ArrayToCommaDelimitedStringTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class BatchUpdateFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matching', SearchType::class, [
                'required' => false,
            ])
            ->add('exclude', SearchType::class, [
                'required' => false,
            ])
            ->add('lastOrderSince', DateType::class, [
                'required' => false,
                'label' => 'Last order between',
            ])
            ->add('lastOrderUntil', DateType::class, [
                'required' => false,
                'label' => 'and',
            ])
            ->add('country', CountryType::class, [
                'required' => false,
            ])
            ->add(
                $builder->create('state', SearchType::class, [
                    'required' => false,
                    'label' => 'State code',
                ])->addModelTransformer(
                    new ArrayToCommaDelimitedStringTransformer()
                )
            )
            ->add('salesman', EntityType::class, [
                'class' => Salesman::class,
                'required' => false,
            ])
            ->add('_limit', NumberType::class, [
                'label' => 'Show # of records',
            ])
            ->add('_start', NumberType::class, [
                'label' => 'Start at record #',
            ])
            ->add('filter', SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return null;
    }

}
