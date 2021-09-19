<?php

namespace Rialto\Web\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A single text input that takes a comma-delimited list of strings
 * and converts in into an array.
 */
class CommaDelimitedArrayType extends AbstractType
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
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'comma_delimited_array';
    }
}
