<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Measurement\Web\DimensionsType;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing ItemVersions.
 */
class ItemVersionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('weight', NumberType::class, [
                'label' => 'Weight (kgs)',
        ])
            ->add('dimensions', DimensionsType::class, [
                'label' => 'Dimensions (cm)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemVersion::class,
            'validation_groups' => function(FormInterface $form) {
                $groups = ['Default'];
                $version = $form->getData();
                if ( $version && $version->isSellable() ) {
                    $groups[] = 'sellable';
                }
                return $groups;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ItemVersion';
    }

}
