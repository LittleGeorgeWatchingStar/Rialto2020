<?php

namespace Rialto\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YesNoAnyType
extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'yes_no_any';
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'any' => 'any',
                'yes' => 'yes',
                'no' => 'no',
            ],
        ]);
    }
}
