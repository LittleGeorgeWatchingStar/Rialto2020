<?php

namespace Rialto\Cms\Web;

use Rialto\Cms\CmsEntry;
use Rialto\Security\Role\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Form type for editing CmsEntry.
 *
 * @see CmsEntry
 */
class CmsEntryType extends AbstractType
{
    /** @var AuthorizationCheckerInterface */
    private $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ( $this->auth->isGranted(Role::ADMIN) ) {
            $builder->add('id', TextType::class, [
                'label' => 'ID',
            ]);
        }

        $builder->add('format', ChoiceType::class, [
            'choices' => CmsEntry::getFormatChoices(),
        ]);
        $builder->add('content', TextareaType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsEntry::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'CmsEntry';
    }

}
