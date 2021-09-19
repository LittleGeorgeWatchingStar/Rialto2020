<?php

namespace Rialto\Manufacturing\Component\Web;

use Rialto\Web\Form\ArrayToCommaDelimitedStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * Form type for entering a list of reference designators into textarea input.
 */
class ReferenceDesignatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new ArrayToCommaDelimitedStringTransformer(),
            true
        );
    }

    public function getParent()
    {
        return TextareaType::class;
    }

    public function getBlockPrefix()
    {
        return 'designators';
    }
}
