<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Stock\Item\RoHS;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RohsType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => RoHS::getValid(),
            'label' => 'RoHS status',
        ]);
    }
}
