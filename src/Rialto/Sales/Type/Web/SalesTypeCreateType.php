<?php

namespace Rialto\Sales\Type\Web;

use Rialto\Sales\Type\SalesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * For creating a new SalesType.
 */
class SalesTypeCreateType extends AbstractType
{
    public function getParent()
    {
        return SalesTypeEditType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'mapped' => false,
            'required' => false,
            'attr' => [
                'placeholder' => 'create a new sales type'
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $id = $form->get('id')->getData();
                return $id ? new SalesType($id) : null;
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesTypeCreate';
    }

}
