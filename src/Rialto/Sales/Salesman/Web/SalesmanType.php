<?php

namespace Rialto\Sales\Salesman\Web;

use Rialto\Sales\Salesman\Salesman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing a Salesman.
 */
class SalesmanType extends AbstractType
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
            'data_class' => Salesman::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Salesman';
    }

}
