<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for selecting other stock allocations.
 */
class ChooseOtherAllocationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ChooseFrom', CollectionType::class, [
            'by_reference' => false,
            'entry_type' => ChooseAllocationType::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ChooseOtherAllocations';
    }

}
