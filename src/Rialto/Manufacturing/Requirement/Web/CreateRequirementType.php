<?php

namespace Rialto\Manufacturing\Requirement\Web;

use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating a work order requirement.
 */
class CreateRequirementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('component', JsEntityType::class, [
            'class' => StockItem::class,
            'label' => 'Component',
            'required' => false,
        ]);
        $builder->add('submit', SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return 'CreateRequirement';
    }

    public function getParent()
    {
        return RequirementType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateRequirement::class,
        ]);
    }
}

