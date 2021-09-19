<?php

namespace Rialto\Stock\Shelf\Velocity\Web;


use Rialto\Stock\Shelf\Velocity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VelocityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', Velocity::getValidValues());
        $resolver->setDefault('choice_value', function (Velocity $velocity = null) {
            return $velocity ? $velocity->getValue() : null;
        });
        $resolver->setDefault('invalid_message', 'Invalid velocity');
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}
