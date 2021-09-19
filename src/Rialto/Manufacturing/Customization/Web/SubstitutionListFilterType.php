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

class SubstitutionListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'empty_data' => '100',
                'attr' => ['max' => 1000],
            ])
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
