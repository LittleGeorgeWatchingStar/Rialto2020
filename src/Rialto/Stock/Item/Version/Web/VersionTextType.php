<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for manually entering a Version.
 *
 * @see Version
 */
class VersionTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new VersionToStringTransformer(), true);
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'version_text';
    }
}
