<?php

namespace Rialto\Email\Mailable\Web;

use Rialto\Email\Mailable\Mailable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A choice form type for selecting email recipients.
 *
 * Expects 'choices' to be a list of Mailable objects.
 *
 * @see Mailable
 */
class MailableType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'mailable';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_value' => function(Mailable $c = null) {
                return $c
                    ? preg_replace("/\W/", '_', $c->getEmail())
                    : null;
            },
            'choice_label' => function(Mailable $c = null) {
                return $c
                    ? sprintf('%s <%s>', $c->getName(), $c->getEmail())
                    : '';
            },
        ]);

        /* Remove choices that don't have an email address. */
        $resolver->setNormalizer('choices', function (Options $option, $choices) {
            return array_filter($choices, function (Mailable $choice) {
                return !!$choice->getEmail();
            });
        });
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}
