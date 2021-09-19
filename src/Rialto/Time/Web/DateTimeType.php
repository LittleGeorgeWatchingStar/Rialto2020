<?php

namespace Rialto\Time\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extends Symfony's built-in DateTimeType form type with some Rialto-specific
 * defaults.
 */
class DateTimeType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'date'],
            'date_widget' => 'single_text',
        ]);
    }
}
