<?php

namespace Rialto\Stock\ChangeNotice\Web;

use Rialto\Stock\ChangeNotice\ChangeNotice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for associating zero or more ChangeNotices with an
 * item or version.
 *
 * Allows the user to pick existing notices and/or add a new one.
 */
class ChangeNoticeListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('existingNotices', EntityType::class, [
                'class' => ChangeNotice::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Existing',
                'choice_label' => 'description',
            ])
            ->add('newNotice', ChangeNoticeType::class, [
                'required' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangeNoticeList::class,
            'attr' => ['class' => 'changeNotices'],
            'label_attr' => ['class' => 'changeNotices'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ChangeNotices';
    }

}
