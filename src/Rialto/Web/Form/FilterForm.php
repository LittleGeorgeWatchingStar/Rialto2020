<?php

namespace Rialto\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Base form type for forms that submit filters to the query string.
 */
class FilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('_limit', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'get',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'attr' => [
                'class' => 'standard filter inline',
            ]
        ]);
    }
}
