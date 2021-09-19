<?php

namespace Rialto\Panelization\Web;

use Rialto\Panelization\PlacedBoard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'board';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('pose', PoseType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlacedBoard::class,
            'validation_groups' => ['Default', 'dimensions'],
        ]);
    }
}
