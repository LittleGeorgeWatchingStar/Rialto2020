<?php

namespace Rialto\Panelization\Web;

use Rialto\Panelization\Pose;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PoseType extends AbstractType implements DataMapperInterface
{
    public function getBlockPrefix()
    {
        return 'pose';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scale = Pose::NUM_PLACES;
        $attr = ['step' => 10 ** -$scale];
        $builder
            ->add('x', NumberType::class, [
                'scale' => $scale,
                'attr' => $attr,
            ])
            ->add('y', NumberType::class, [
                'scale' => $scale,
                'attr' => $attr,
            ])
            ->add('rotation', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 270,
                    'step' => 90,
                ],
            ])
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pose::class,
            'validation_groups' => ['Default', 'dimensions'],
            'empty_data' => null,
        ]);
    }

    /**
     * Maps properties of some data to a list of forms.
     *
     * @param Pose $data Structured data.
     * @param FormInterface[] $forms A list of {@link FormInterface} instances.
     */
    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);
        $forms['x']->setData($data->getX());
        $forms['y']->setData($data->getY());
        $forms['rotation']->setData($data->getRotation());
    }

    /**
     * Maps the data of a list of forms into the properties of some data.
     *
     * @param FormInterface[] $forms A list of {@link FormInterface} instances.
     * @param mixed $data Structured data.
     */
    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);
        $data = new Pose(
            $forms['x']->getData(),
            $forms['y']->getData(),
            $forms['rotation']->getData());
    }
}
