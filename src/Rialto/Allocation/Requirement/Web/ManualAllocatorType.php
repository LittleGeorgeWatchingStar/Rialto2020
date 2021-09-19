<?php

namespace Rialto\Allocation\Requirement\Web;


use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Allocation\Requirement\ManualAllocator;
use Rialto\Purchasing\Producer\Orm\StockProducerRepository;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;

class ManualAllocatorType extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'requirement_allocator';
    }

    /**
     * @param ManualAllocator $allocator
     */
    protected function updateForm(FormInterface $form, $allocator)
    {
        $requirement = $allocator->getRequirement();
        $form->add('bins', EntityType::class, [
            'class' => StockBin::class,
            'query_builder' => function (StockBinRepository $repo) use ($requirement) {
                return $repo->createBuilder()
                    ->available()
                    ->allocatable()
                    ->byRequirement($requirement)
                    ->getQueryBuilder();
            },
            'expanded' => true,
            'multiple' => true,
            'attr' => ['class' => 'checkbox_group'],
            'label' => false,
        ]);

        $form->add('producers', EntityType::class, [
            'class' => $allocator->getProducerClass(),
            'query_builder' => function (StockProducerRepository $repo) use ($requirement) {
                return $repo->createBuilder()
                    ->openForAllocation()
                    ->forVersionedItem($requirement)
                    ->getQueryBuilder();
            },
            'choice_label' => 'sourceDescription',
            'expanded' => true,
            'multiple' => true,
            'attr' => ['class' => 'checkbox_group'],
            'label' => false,
        ]);

        $form->add('stealAllocations', CheckboxType::class, [
            'required' => false,
            'label' => 'Steal allocations from other orders?',
        ]);
    }

}
