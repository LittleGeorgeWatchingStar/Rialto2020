<?php

namespace Rialto\Stock\Transfer\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Stock\Transfer\MissingTransferItem;
use Rialto\Time\Web\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissingTransferItemType extends DynamicFormType
{
    protected function updateForm(FormInterface $form, $item)
    {
        /** @var $item MissingTransferItem */
        $form
            ->add('location', ChoiceType::class, [
                'choices' => MissingTransferItem::getLocationChoices($item),
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('qtyFound', IntegerType::class, [
                'error_bubbling' => true,
            ])
            ->add('dateFound', DateTimeType::class);
    }


    public function getBlockPrefix()
    {
        return 'MissingTransferItem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MissingTransferItem::class
        ]);
    }
}
