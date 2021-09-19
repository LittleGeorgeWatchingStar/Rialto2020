<?php

namespace Rialto\Magento2\Storefront\Web;

use Rialto\Magento2\Storefront\Storefront;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StorefrontType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->queryByRole(Role::STOREFRONT);
                },
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Users must have STOREFRONT role to appear in this list',
                ],
            ])
            ->add('salesType', EntityType::class, [
                'class' => SalesType::class,
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Orders from this storefront will be created with this sales type',
                ],
            ])
            ->add('quoteType', EntityType::class, [
                'class' => SalesType::class,
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'Quotations from this storefront will be created with this sales type',
                ],
            ])
            ->add('salesman', EntityType::class, [
                'class' => Salesman::class,
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'New customer branches from this storefront will be associated with this salesperson',
                ],
            ])
            ->add('shipFromFacility', EntityType::class, [
                'class' => Facility::class,
                'query_builder' => function (FacilityRepository $repo) {
                    return $repo->queryValidDestinations();
                },
            ])
            ->add('storeUrl', UrlType::class)
            ->add('apiKey', TextType::class, [
                'required' => false,
            ])
            ->add('generateAPI', SubmitType::class, [
                'label' => 'Generate API Key and Submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storefront::class,
        ]);
    }
}
