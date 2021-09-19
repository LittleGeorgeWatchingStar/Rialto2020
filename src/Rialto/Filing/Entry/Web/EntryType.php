<?php

namespace Rialto\Filing\Entry\Web;

use Rialto\Filing\Entry\Entry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for uploading document entries.
 */
class EntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Entry';
    }

}
