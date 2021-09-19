<?php

namespace Rialto\Manufacturing\BuildFiles\Web;

use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for uploading build/engineering data files for a PCB.
 */
class PcbBuildFilesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageTop', FileType::class, [
            'required' => false,
            'label' => 'Top image'
        ]);
        $builder->add('imageBottom', FileType::class, [
            'required' => false,
            'label' => 'Bottom image'
        ]);
        $builder->add('netlist', FileType::class, [
            'required' => false,
            'label' => 'Netlist'
        ]);
        $builder->add('gerbers', FileType::class, [
            'required' => false,
            'label' => 'Gerbers'
        ]);
        $builder->add('boardOutline', FileType::class, [
            'required' => false,
            'label' => 'Board Outline Gerber'
        ]);
        $builder->add('drillExcellon24', FileType::class, [
            'required' => false,
            'label' => 'Drill Excellon 24'
        ]);
        $builder->add('panelizedGerbers', FileType::class, [
            'required' => false,
            'label' => 'Panelized Gerbers'
        ]);
        $builder->add('xy', FileType::class, [
            'required' => false,
            'label' => 'XY'
        ]);
        $builder->add('schematic', FileType::class, [
            'required' => false,
            'label' => 'Schematic'
        ]);
        $builder->add('testInstructions', FileType::class, [
            'required' => false,
            'label' => 'Test Instructions'
        ]);
        $builder->add('eagleDesign', FileType::class, [
            'required' => false,
            'label' => 'Eagle Design (Internal CM Only)'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PcbBuildFiles::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'BuildFiles';
    }

}
