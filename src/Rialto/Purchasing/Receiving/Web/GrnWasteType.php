<?php

namespace Rialto\Purchasing\Receiving\Web;


use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Time\Web\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GrnWasteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'attr' => ['class' => 'waste date'],
                'label' => 'Transaction date',
                'constraints' => new Assert\NotBlank(),
            ])
            ->add('items', CollectionType::class, [
                'entry_type' => WorkOrderWasteType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => false,
                'allow_delete' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', GoodsReceivedNotice::class);
    }
}


class WorkOrderWasteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('chargeForDiscard', CheckboxType::class, [
                'required' => false,
                'label' => 'grn_waste.charge_for_labour',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', GoodsReceivedItem::class);
        $resolver->setDefault('translation_domain', 'form');
    }
}
