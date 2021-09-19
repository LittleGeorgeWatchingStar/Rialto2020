<?php

namespace Rialto\Manufacturing\Customization\Web;


use Rialto\Manufacturing\Customization\Substitution;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomizationListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => 'SKU',
            ])
            ->add('substitution', EntityType::class, [
                'class' => Substitution::class,
                'choice_label' => 'instructions',
                'required' => false,
            ])
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'empty_data' => '100',
                'attr' => ['max' => 1000],
            ])
            ->add('active', YesNoAnyType::class)
            ->add('filter', SubmitType::class);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefixes default to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix()
    {
        return null;
    }

    public function getParent()
    {
        return FilterForm::class;
    }

}
