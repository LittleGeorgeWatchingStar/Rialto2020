<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting a Version.
 *
 * @see Version
 */
class VersionChoiceType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'version_choice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_any' => false,
            'choice_value' => function ($version) {
                return (string) $version;
            },
            'choice_label' => function ($version) {
                return (string) $version;
            }
        ]);

        $resolver->setNormalizer('choices', function (Options $options, $versions) {
            if ($options['allow_any']) {
                array_unshift($versions, Version::any());
            }
            return $versions;
        });
    }
}
