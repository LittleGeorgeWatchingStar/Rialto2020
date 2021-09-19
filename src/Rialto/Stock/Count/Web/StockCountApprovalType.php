<?php

namespace Rialto\Stock\Count\Web;

use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Stock\Count\BinCount;
use Rialto\Stock\Count\StockCount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type that the admin uses to approve a stock count.
 */
class StockCountApprovalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('binCounts', CollectionType::class, [
                'entry_type' => BinCountApprovalType::class,
                'label' => false,
            ])
            ->add('adjustmentDate', DateTimeType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'date'],
            ]);
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

class BinCountApprovalType extends DynamicFormType
{
    /**
     * @param BinCount $binCount
     */
    public function updateForm(FormInterface $form, $binCount)
    {
        assertion($binCount instanceof BinCount);
        if ($binCount->isApproved()) {
            return;
        }

        $isCounted = $binCount->isCounted();
        $form->add('acceptedQty', ChoiceType::class, [
            'choices' => $binCount->getPossibleQuantities(),
            'required' => $isCounted,
            'placeholder' => $isCounted ? null : 'not counted yet',
            'disabled' => ! $isCounted,
            'expanded' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BinCount::class,
        ]);
    }
}