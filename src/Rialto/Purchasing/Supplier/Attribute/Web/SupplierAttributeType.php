<?php

namespace Rialto\Purchasing\Supplier\Attribute\Web;

use Rialto\Entity\Web\EntityAttributeType;
use Rialto\Purchasing\Supplier\Attribute\SupplierAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing SupplierAttributes.
 */
class SupplierAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attribute', ChoiceType::class, [
            'choices' => SupplierAttribute::getChoices(),
        ]);
    }

    public function getParent()
    {
        return EntityAttributeType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierAttribute::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierAttribute';
    }

}
