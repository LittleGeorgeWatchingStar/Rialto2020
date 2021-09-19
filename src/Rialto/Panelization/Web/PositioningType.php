<?php

namespace Rialto\Panelization\Web;


use Rialto\Geometry\Web\Vector2DType;
use Rialto\Panelization\Panel;
use Rialto\Panelization\Pose;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * For manually positioning boards on a panel.
 */
class PositioningType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'positioning';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scale = Pose::NUM_PLACES;
        $numberAttr = ['step' => 10 ** -$scale];

        $builder
            ->add('boards', CollectionType::class, [
                'entry_type' => BoardType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => true,
            ])
            ->add('margin', NumberType::class, [
                'scale' => $scale,
                'attr' => $numberAttr,
                'label_attr' => [
                    'title' => "Min distance between boards",
                ],
            ])
            ->add('width', NumberType::class, [
                'scale' => $scale,
                'attr' => $numberAttr,
            ])
            ->add('height', NumberType::class, [
                'scale' => $scale,
                'attr' => $numberAttr,
            ])
            ->add('bottomLeft', Vector2DType::class, [
                'label' => 'Bottom-left',
                'scale' => $scale,
                'attr' => $numberAttr,
            ])
            ->add('outputOffset', Vector2DType::class, [
                'label' => 'Machine origin',
                'scale' => $scale,
                'attr' => $numberAttr,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Panel::class,
            'validation_groups' => ['Default', 'dimensions'],
        ]);
    }
}
