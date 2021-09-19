<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Stock\Facility\Facility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for adding a new stock location.
 */
class CreateLocationType extends AbstractType
{
    public function getParent()
    {
        return FacilityType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', TextType::class, [
            'label' => 'Location code',
            'mapped' => false, // passed to constructor
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function(FormInterface $form) {
                $id = $form->get('id')->getData();
                return new Facility($id);
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'NewLocation';
    }

}
