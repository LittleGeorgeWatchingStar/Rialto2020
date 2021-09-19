<?php

namespace Rialto\Stock\Web;

use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Cost\InventoryValuation;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Time\Web\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 */
class InventoryValuationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('get')
            ->add('categories', EntityType::class, [
                'class' => StockCategory::class,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('location', EntityType::class, [
                'class' => Facility::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => '-- all --',
            ]);

        $builder->add('date', DateType::class);
        $builder->add('showZeroes', CheckboxType::class, [
            'required' => false,
            'label' => 'Show zeroes?'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InventoryValuation::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }

}
