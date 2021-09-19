<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Rialto\Manufacturing\WorkOrder\WorkOrderEmail;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for sending an email requesting the build of an in-house
 * work order.
 */
class WorkOrderEmailType
extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('to', EntityType::class, [
            'class' => User::class,
            'query_builder' => function(UserRepository $repo) {
                return $repo->queryByRole(Role::WAREHOUSE);
            },
            'choice_label' => 'emailLabel',
            'multiple' => true,
            'expanded' => true,
            'attr' => ['class' => 'checkbox_group'],
        ]);
        $builder->add('body', TextareaType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkOrderEmail::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'WorkOrderEmail';
    }

}
