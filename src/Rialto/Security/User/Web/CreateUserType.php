<?php

namespace Rialto\Security\User\Web;

use Rialto\Security\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating a new user.
 */
class CreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, [
            'mapped' => false, // constructor argument
        ]);
    }

    public function getParent()
    {
        return UserType::class;
    }

    public function getBlockPrefix()
    {
        return 'NewUser';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $username = $form->get('username')->getData();
                return new User($username);
            },
        ]);
    }
}
