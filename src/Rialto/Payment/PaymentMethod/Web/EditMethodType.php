<?php

namespace Rialto\Payment\PaymentMethod\Web;

use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For editing payment methods.
 */
class EditMethodType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'PaymentMethod';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
            ->add('group', EntityType::class, [
                'class' => PaymentMethodGroup::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentMethod::class,
        ]);
    }


}
