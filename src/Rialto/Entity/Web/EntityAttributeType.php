<?php

namespace Rialto\Entity\Web;

use Rialto\Entity\EntityAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base form type for editing entity attributes.
 *
 * @see EntityAttribute
 */
class EntityAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attribute', TextType::class)
            ->add('value', TextType::class, [
                'required' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'EntityAttribute';
    }

}
