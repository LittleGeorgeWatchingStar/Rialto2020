<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use Rialto\Manufacturing\Allocation\RequirementAllocator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class RequirementAllocatorType
extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('qtyToOrder', IntegerType::class, []);
    }

    public function getBlockPrefix()
    {
        return 'RequirementAllocator';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RequirementAllocator::class,
        ]);
    }

}
