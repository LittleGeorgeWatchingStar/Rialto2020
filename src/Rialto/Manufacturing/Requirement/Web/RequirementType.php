<?php

namespace Rialto\Manufacturing\Requirement\Web;

use Rialto\Manufacturing\Component\Web\ReferenceDesignatorType;
use Rialto\Manufacturing\WorkType\WorkType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Base form type for creating or editing a requirement.
 */
class RequirementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('unitQty', IntegerType::class, [
            'label' => 'Unit qty',
        ]);
        $builder->add('scrapCount', IntegerType::class, [
            'label' => 'Scrap count',
            'required' => false,
        ]);
        $builder->add('designators', ReferenceDesignatorType::class, [
            'attr' => ['rows' => 5, 'cols' => 50],
            'required' => false,
        ]);
        $builder->add('workType', EntityType::class, [
            'class' => WorkType::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'Requirement';
    }
}
