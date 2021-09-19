<?php

namespace Rialto\Manufacturing\WorkOrder\Web;


use Rialto\Allocation\Allocation\StockAllocation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockAllocationSelectAndDeleteType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'choose_stock_allocations';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('stockAllocations', EntityType::class, [
        'class' => StockAllocation::class,
        'choices' => $options["allocations"],
        'choice_label' => 'sourceDescription',
        'expanded' => true,
        'multiple' => true,
        'attr' => ['class' => 'checkbox_group'],
        'label' => false,
    ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['allocations']);
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => ['requirementQuantity', 'quantity']
        ]);
    }
}
