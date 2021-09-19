<?php

namespace Rialto\Manufacturing\WorkType\Web;


use Rialto\Stock\Item\Document\ProductLabel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for printing product labels.
 */
class PrintProductLabelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('numCopies', IntegerType::class, [
                'label' => 'How many labels do you need?',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ProductLabel::class);
    }
}
