<?php

namespace Rialto\Supplier\Order\Web;


use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;
use Rialto\Supplier\Order\AdditionalPart;
use Rialto\Web\Form\TextEntityType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionalPartType extends DynamicFormType
{
    public function updateForm(FormInterface $form, $addPart)
    {
        /* @var $addPart AdditionalPart */
        $form
            ->add('component', TextEntityType::class, [
                'class' => StockItem::class,
                'attr' => ['placeholder' => 'SKU'],
                'label' => 'Part code you want to add',
            ])
            ->add('scrapCount', NumberType::class, [
                'attr' => ['placeholder' => 'total qty needed'],
                'label' => 'Total quantity needed',
                'required' => false,
            ])
            ->add('workType', EntityType::class, [
                'class' => WorkType::class,
                'label' => 'Manufacturing process needed',
            ])
            ->add('reason', TextareaType::class, [
                'attr' => ['placeholder' => 'reason'],
                'label' => 'Why do you need this part?',
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdditionalPart::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'AddPart';
    }

}
