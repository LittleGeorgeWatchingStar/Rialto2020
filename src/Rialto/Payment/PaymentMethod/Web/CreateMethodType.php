<?php

namespace Rialto\Payment\PaymentMethod\Web;

use Rialto\Payment\PaymentMethod\PaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating new payment methods.
 */
class CreateMethodType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'PaymentMethod';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'attr' => [
                'placeholder' => 'New method...',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $id = $form->get('id')->getData();
                return $id ? new PaymentMethod($id) : null;
            },
            'required' => false,
        ]);
    }

    public function getParent()
    {
        return EditMethodType::class;
    }
}
