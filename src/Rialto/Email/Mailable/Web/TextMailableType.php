<?php

namespace Rialto\Email\Mailable\Web;

use Rialto\Email\Mailable\GenericMailable;
use Rialto\Email\Mailable\Mailable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextMailableType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'text_mailable';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = empty($options['multiple'])
            ? new TextMailableTransformer()
            : new MultipleTextMailableTransformer();
        $builder->addModelTransformer($transformer);
    }

    public function getParent()
    {
        return TextType::class;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined("multiple");
        $resolver->setDefault('attr', function (Options $options) {
            $placeholder = 'email@example.com';
            if (!empty($options['multiple'])) {
                $placeholder = "$placeholder,$placeholder,...";
            }
            return ['placeholder' => $placeholder];
        });
    }
}


class TextMailableTransformer implements DataTransformerInterface
{
    /**
     * @param Mailable $mailable The value in the original representation
     *
     * @return string The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($mailable)
    {
        return $mailable ? $mailable->getEmail() : '';
    }

    /**
     * @param string $emailAddress The value in the transformed representation
     *
     * @return Mailable The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($emailAddress)
    {
        return $emailAddress ? new GenericMailable($emailAddress, $emailAddress) : null;
    }
}


class MultipleTextMailableTransformer implements DataTransformerInterface
{
    /**
     * @param Mailable[] $mailables The value in the original representation
     *
     * @return string The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($mailables)
    {
        if ($mailables instanceof \Iterator) {
            $mailables = iterator_to_array($mailables);
        }
        return join(',', array_map(function(Mailable $m) {
            return $m->getEmail();
        }, $mailables));
    }

    /**
     * @param string $emailAddresses Comma-delimited email addresses
     *
     * @return Mailable The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($emailAddresses)
    {
        $list = explode(',', $emailAddresses);
        return array_filter(array_map(function($e) {
            $e = trim($e);
            return $e ? new GenericMailable($e, $e) : null;
        }, $list));
    }
}
