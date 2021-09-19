<?php

namespace Rialto\Stock\Bin\Web;


use Rialto\Stock\Bin\BinStyle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type that allows the user to choose a bin style.
 *
 * Set the "multiple" option to true to select multiple bin styles.
 */
class BinStyleType extends AbstractType
{
    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => BinStyle::class,
            'invalid_message' => 'Invalid bin style',
        ]);
    }

}
