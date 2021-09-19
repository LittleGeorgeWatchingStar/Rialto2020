<?php

namespace Rialto\Stock\Count\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Stock\Count\BinCount;
use Rialto\Stock\Count\StockCount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The location manager uses this form to enter stock counts.
 */
class StockCountEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('binCounts', CollectionType::class, [
            'entry_type' => BinCountEntryType::class,
            'label' => false,
        ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockCount::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'StockCount';
    }

}

class BinCountEntryType extends DynamicFormType
{
    /**
     * @param BinCount $bc
     */
    public function updateForm(FormInterface $form, $bc)
    {
        assertion($bc instanceof BinCount);
        if ( $bc->isApproved() ) {
            return;
        }
        $form->add('reportedQty', NumberType::class, [
            'required' => false,
        ])
            ->add('selectedAllocations', EntityType::class, [
                'class' => StockAllocation::class,
                'choices' => $bc->getAllocations(),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'choice_label' => 'orderDescriptionWithQuantity',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BinCount::class,
        ]);
    }
}