<?php

namespace Rialto\Purchasing\Order\Web;

use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Purchasing\Order\PurchasingOrderBuildFiles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for uploading build/engineering data files for a PCB.
 */
class PurchasingOrderBuildFilesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('panelizedGerbers', FileType::class, [
            'required' => false,
            'label' => 'Panelized Gerbers'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchasingOrderBuildFiles::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'BuildFiles';
    }

}
