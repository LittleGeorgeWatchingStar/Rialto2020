<?php

namespace Rialto\Stock\ChangeNotice\Web;

use DateTime;
use Rialto\Stock\ChangeNotice\ChangeNotice;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating ChangeNotices.
 */
class ChangeNoticeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'New',
            ])
            ->add('effectiveDate', DateType::class, [
                'data' => new DateTime(),
            ])
            ->add('publish', CheckboxType::class, [
                'required' => false,
                'label' => 'Publish?'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangeNotice::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ChangeNotice';
    }

}
