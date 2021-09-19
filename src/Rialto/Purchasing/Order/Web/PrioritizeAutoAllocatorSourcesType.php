<?php

namespace Rialto\Purchasing\Order\Web;

use Rialto\Manufacturing\Allocation\AllocationConfigurationArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrioritizeAutoAllocatorSourcesType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'allocConfigArray';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('warehousePriority', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                ]
            ])
            ->add('warehouseDisabled', CheckboxType::class, [
                'label' => "disable this source?",
                'required' => false,
            ])
            ->add('purchaseOrderPriority', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                ]
            ])
            ->add('purchaseOrderDisabled', CheckboxType::class, [
                'label' => "disable this source?",
                'required' => false,
            ])
            ->add('cmPriority', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                ]
            ])
            ->add('cmDisabled', CheckboxType::class, [
                'label' => "disable this source?",
                'required' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AllocationConfigurationArray::class,
            'validation_groups' => false,
        ]);
    }
}
