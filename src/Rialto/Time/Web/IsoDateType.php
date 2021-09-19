<?php

namespace Rialto\Time\Web;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType as SymfonyDateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IsoDateType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'iso_date';
    }

    public function getParent()
    {
        return SymfonyDateType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'date'],
            'widget' => 'single_text',
//            'date_format' => "yyyy-MM-dd'T'HH:mm:ss.SSSXX",
        ]);
    }
}
