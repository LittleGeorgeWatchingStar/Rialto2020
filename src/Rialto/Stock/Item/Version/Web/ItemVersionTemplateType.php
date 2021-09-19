<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Measurement\Web\DimensionsType;
use Rialto\Stock\ChangeNotice\Web\ChangeNoticeListType;
use Rialto\Stock\Item\Version\ItemVersionTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for creating new ItemVersion instances via ItemVersionTemplate
 */
class ItemVersionTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('versionCode', TextType::class, [
                'label' => 'Version',
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Weight (kgs)',
                'required' => false,
            ])
            ->add('dimensions', DimensionsType::class, [
                'label' => 'Dimensions (cm)',
                'required' => false,
            ])
            ->add('autoBuildVersion', CheckboxType::class, [
                'required' => false,
                'label' => 'Use this as the new auto-build version?',
                'label_attr' => ['class' => 'checkbox'],
            ])
            ->add('shippingVersion', CheckboxType::class, [
                'required' => false,
                'label' => 'Use this as the new shipping version?',
                'label_attr' => ['class' => 'checkbox'],
            ])
            ->add('changeList', ChangeNoticeListType::class, [
                'required' => false,
                'label' => 'You can optionally add a change notice to this new version:',
                'label_attr' => ['class' => 'section'],
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
