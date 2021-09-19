<?php

namespace Rialto\Stock\Shelf\Rack\Web;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RackBaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'max 10 chars...',
                    'pattern' => '[a-zA-Z]{1,10}',
                    'title' => 'Letters only, max 10',
                ],
            ])
            ->add('esdProtection', CheckboxType::class, [
                'required' => false,
                'label' => 'ESD protection?',
            ]);
    }
}
