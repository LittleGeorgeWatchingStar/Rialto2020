<?php

namespace Rialto\Stock\Publication\Web;

use Rialto\Stock\Publication\UploadPublication;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding publications to stock items.
 *
 * @see UploadPublication
 */
class UploadPublicationType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'UploadPublication';
    }

    public function getParent()
    {
        return PublicationType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
            ])
            ->add('purpose', ChoiceType::class, [
                'choices' => UploadPublication::getPurposeOptions(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UploadPublication::class,
        ]);
    }
}
