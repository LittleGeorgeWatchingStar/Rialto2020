<?php

namespace Rialto\Sales\Type\Web;

use Rialto\Sales\Type\SalesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing a SalesType
 */
class SalesTypeEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesType::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesType';
    }

}
