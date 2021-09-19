<?php

namespace Rialto\Stock\Shelf\Position\Web;


use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\Web\BinStyleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchAssignerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selected', EntityType::class, [
                'class' => StockBin::class,
                'multiple' => true,
                'expanded' => true,
                'choices' => $options['bins'],
            ])
            ->add('binStyle', BinStyleType::class, [
                'required' => false,
                'placeholder' => '-- choose --',
            ])
            ->add('printLabels', CheckboxType::class, [
                'required' => false,
                'label' => 'Print new labels?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', BatchAssigner::class);
        $resolver->setRequired('bins');
    }

}
