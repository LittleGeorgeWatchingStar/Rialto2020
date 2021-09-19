<?php

namespace Rialto\Manufacturing\Bom\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BomCsvFileType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('attachment', FileType::class);
    }

    public function getBlockPrefix()
    {
        return 'BomCsvFile';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BomCsvFileUpload::class,
        ]);
    }
}
