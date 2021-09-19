<?php

namespace Rialto\Madison\Feature\Web;

use Rialto\Madison\Feature\StockItemFeature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for adding features to a stock item.
 */
class EditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', TextType::class, [
            'label' => 'Value',
            'required' => false
        ]);
        $builder->add('details', TextareaType::class, [
            'label' => 'Detail',
            'required' => false
        ]);
        $builder->add('delete', SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return 'StockItemFeature';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItemFeature::class,
        ]);
    }
}

