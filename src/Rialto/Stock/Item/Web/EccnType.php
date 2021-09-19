<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Stock\Item\Eccn;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EccnType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $codes = Eccn::getList();
        $resolver->setDefault('choices', array_combine($codes, $codes));
        $resolver->setDefault('invalid_message', 'Invalid ECCN code.');
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
