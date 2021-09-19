<?php

namespace Rialto\Shopify\Storefront\Web;

use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Shopify\Storefront\Storefront;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating storefronts.
 */
class StorefrontType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'Storefront';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function(UserRepository $repo) {
                    return $repo->queryByRole(Role::STOREFRONT);
                },
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Users must have STOREFRONT role to appear in this list',
                ],
        ])
            ->add('paymentMethod', EntityType::class, [
                'class' => PaymentMethod::class,
            ])
            ->add('salesType', EntityType::class, [
                'class' => SalesType::class,
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Orders from this storefront will be created with this sales type',
                ],
            ])
            ->add('salesman', EntityType::class, [
                'class' => Salesman::class,
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'New customer branches from this storefront will be associated with this salesperson',
                ],
            ])
            ->add('domain', TextType::class)
            ->add('apiKey', TextType::class)
            ->add('apiPassword', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Change...',
                ],
            ])
            ->add('sharedSecret', PasswordType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Change...',
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storefront::class,
        ]);
    }

}
