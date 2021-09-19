<?php

namespace Rialto\Sales\Order\Web;


use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Type\SalesType;
use Rialto\Tax\TaxExemption;
use Rialto\Time\Web\DateType;
use Rialto\Web\Form\FilterForm;
use Rialto\Web\Form\YesNoAnyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListFilterType
    extends AbstractType
{
    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* Filter exact */
        $builder
            ->add('id', TextType::class, [
                'required' => false,
                'label' => 'Order ID',
                'attr' => [
                    'title' => "The exact Rialto order ID; " .
                        "use &quot;Reference&quot; for a broader search"
                ]
            ])
            ->add('type', EntityType::class, [
                'class' => SalesType::class,
                'required' => false,
                'label' => 'Order type',
                'placeholder' => 'any',
            ])
            ->add('salesStage', ChoiceType::class, [
                'choices' => SalesOrder::getValidStages(),
                'required' => false,
                'placeholder' => 'any',
            ])
            ->add('taxExemption', ChoiceType::class, [
                'choices' => TaxExemption::getChoices(),
                'required' => false,
                'placeholder' => 'any',
            ])
            ->add('shipped', YesNoAnyType::class, [
                'label' => 'Shipped?',
            ])
            ->add('printed', YesNoAnyType::class, [
                'label' => 'Printed?',
            ])
            ->add('allocated', YesNoAnyType::class,[
                'label' => 'Allocated?',
            ])
            /* Filter matching */
            ->add('customer', TextType::class, [
                'required' => false,
            ])
            ->add('reference', TextType::class, [
                'required' => false,
            ])
            ->add('item', TextType::class, [
                'required' => false,
                'label' => 'Stock item',
            ])
            ->add('comments', TextType::class, [
                'required' => false,
            ])
            ->add('shippingAddress', TextType::class, [
                'required' => false,
            ])
            ->add('county', TextType::class, [
                'required' => false,
            ])
            /* Filter by date */
            ->add('startDate', DateType::class, [
                'required' => false,
                'label' => 'Created between',
                'input' => 'string',
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
                'label' => 'and',
                'input' => 'string',
            ])
            ->add('invoiceStartDate', DateType::class, [
                'required' => false,
                'label' => 'Shipped between',
                'input' => 'string',
            ])
            ->add('invoiceEndDate', DateType::class, [
                'required' => false,
                'label' => 'and',
                'input' => 'string',
            ])
            ->remove('_limit')
            ->add('_limit', IntegerType::class, [
                'required' => false,
                'empty_data' => '100',
                'attr' => ['max' => 1000],
            ])
            ->add('filter', SubmitType::class, [
                'label' => 'Apply filters',
            ]);
    }

    public function getParent()
    {
        return FilterForm::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['class' => 'standard filter'],
        ]);
    }


}
