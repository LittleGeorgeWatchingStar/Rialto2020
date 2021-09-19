<?php

namespace Rialto\Time\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extends Symfony's built-in DateType form type with some Rialto-specific
 * defaults.
 */
class DateType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'rialto_date';
    }

    public function getParent()
    {
        return BaseType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'date'],
            'widget' => 'single_text',
        ]);
    }
}
