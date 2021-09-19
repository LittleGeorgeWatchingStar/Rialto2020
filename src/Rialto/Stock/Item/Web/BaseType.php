<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Measurement\Web\UnitsType;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Core form type for editing/creating stock items.
 */
class BaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'required' => true,
        ]);
        $builder->add('longDescription', TextareaType::class, [
            'label' => 'Long description',
            'required' => true,
        ]);
        $builder->add('package', TextType::class, [
            'label' => 'Package',
            'required' => false,
        ]);
        $builder->add('partValue', TextType::class, [
            'label' => 'Part value',
            'required' => false,
        ]);

        $builder->add('closeCount', ChoiceType::class, [
            'label' => 'Close-count',
            'required' => true,
            'choices' => [
                'no' => false,
                'yes' => true,
            ],
        ]);

        $builder->add('units', UnitsType::class);

        $builder->add('rohs', RohsType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', StockItem::class);
    }
}
