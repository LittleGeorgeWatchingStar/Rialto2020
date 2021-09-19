<?php

namespace Rialto\Measurement\Web;

use Rialto\Measurement\Units;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting the unit of measure.
 *
 * @see Units
 */
class UnitsType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this, true);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => Units::getChoices(),
            'label' => 'Units',
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'rialto_units';
    }

    public function transform($units)
    {
        if (!$units) return null;
        if (!$units instanceof Units) {
            throw new UnexpectedTypeException($units, 'Units');
        }
        return $units->getName();
    }

    public function reverseTransform($name)
    {
        if (!$name) return null;
        return new Units($name);
    }
}

