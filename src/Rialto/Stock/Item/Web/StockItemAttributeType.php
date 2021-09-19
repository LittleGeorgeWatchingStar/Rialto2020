<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Entity\Web\EntityAttributeType;
use Rialto\Stock\Item\StockItemAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing StockItemAttributes.
 */
class StockItemAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attribute', ChoiceType::class, [
            'choices' => StockItemAttribute::getChoices(),
        ]);
    }

    public function getParent()
    {
        return EntityAttributeType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockItemAttribute::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockItemAttribute';
    }

}
