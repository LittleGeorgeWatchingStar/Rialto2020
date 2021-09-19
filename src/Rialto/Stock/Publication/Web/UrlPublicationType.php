<?php

namespace Rialto\Stock\Publication\Web;

use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\UrlPublication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding publications to stock items.
 *
 * @see Publication
 */
class UrlPublicationType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'UrlPublication';
    }

    public function getParent()
    {
        return PublicationType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, [
                'label' => 'URL',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UrlPublication::class,
        ]);
    }
}
