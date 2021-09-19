<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Measurement\Web\DimensionsType;
use Rialto\Stock\Item\Version\ItemVersionTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form creating the initial version of a new stock item.
 *
 * @see StockItemTemplateType
 */
class InitialVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('versionCode', TextType::class, [
                'label' => 'Version code',
        ])
            ->add('weight', NumberType::class, [
                'label' => 'Weight (kgs)',
            ])
            ->add('dimensions', DimensionsType::class, [
                'label' => 'Dimensions (cm)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemVersionTemplate::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ItemVersion';
    }

}
