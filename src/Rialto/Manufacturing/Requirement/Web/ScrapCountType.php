<?php

namespace Rialto\Manufacturing\Requirement\Web;


use Rialto\Manufacturing\Requirement\ScrapCount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScrapCountType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'scrap_count';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('package', TextType::class)
            ->add('scrapCount', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ScrapCount::class);
    }
}
