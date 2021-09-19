<?php

namespace Rialto\Sales\Salesman\Web;

use Rialto\Sales\Salesman\Salesman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating a new Salesman.
 */
class SalesmanCreateType extends AbstractType
{
     public function getParent()
    {
        return SalesmanType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'mapped' => false,
            'required' => false,
            'attr' => [
                'placeholder' => 'add a new salesperson'
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $id = $form->get('id')->getData();
                return $id ? new Salesman($id) : null;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesmanCreate';
    }

}
