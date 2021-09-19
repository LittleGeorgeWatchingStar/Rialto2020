<?php

namespace Rialto\Security\User\Web;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Rialto\Web\Form\JsEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form type for editing a user.
 */
class UserType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    public function getBlockPrefix()
    {
        return 'User';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full name',
            ])
            ->add('email', EmailType::class, ['required' => false])
            ->add('xmpp', EmailType::class, [
                'required' => false,
                'label_attr' => [
                    'class' => 'tooltip',
                    'title' => 'eg, Google Talk address'
                ],
            ])
            ->add('phone', TextType::class, ['required' => false])
            ->add('theme', ChoiceType::class, [
                'choices' => [
                    'claro' => 'claro',
                    'tundra' => 'tundra'
                ],
                'required' => true,
            ])
            ->add('dateFormat', ChoiceType::class, [
                'choices' => User::getDateFormatOptions(),
            ]);

        if ($this->auth->isGranted(Role::ADMIN)) {
            $builder
                ->add('uuids', CollectionType::class, [
                    'entry_type' => TextType::class,
                    'label' => 'SSO UUIDs',
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                ])
                ->add('roles', EntityType::class, [
                    'class' => Role::class,
                    'multiple' => true,
                    'expanded' => true,
                    'group_by' => function (Role $role) {
                        return $role->getGroup();
                    },
                    'choice_label' => 'label',
                    'attr' => ['class' => 'checkbox_group'],
                ])
                ->add('supplier', JsEntityType::class, [
                    'class' => Supplier::class,
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => '-- no supplier --',
                ])
                ->add('defaultLocation', EntityType::class, [
                    'class' => Facility::class,
                    'required' => false,
                    'placeholder' => '-- no location --',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

