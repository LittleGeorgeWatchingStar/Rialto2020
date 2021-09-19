<?php

namespace Rialto\Geppetto\Design\Web;

use Rialto\Geppetto\Design\Design;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 * use @see DesignRevision2Type
 *
 * Form type for creating Geppetto designs.
 */
class DesignType extends AbstractType
{
    public function getParent()
    {
        return DesignRevisionType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('permalink', UrlType::class, [
                'required' => false,
            ])
            ->add('pcbWeight', NumberType::class, [ // deprecated
                'required' => false,
                'scale' => 4,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Design::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Design';
    }

}
