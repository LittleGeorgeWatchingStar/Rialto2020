<?php

namespace Rialto\Sales\Returns\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Web\Form\JsEntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesReturnType extends DynamicFormType
{
    public function getBlockPrefix()
    {
        return 'SalesReturn';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SalesReturn::class
        ]);
    }

    /**
     * @param SalesReturn $rma
     */
    protected function updateForm(FormInterface $form, $rma)
    {
        $form->add('lineItems', CollectionType::class, [
            'entry_type' => SalesReturnItemType::class,
            'allow_add' => false,
        ]);
        $form->add('caseNumber', IntegerType::class, [
            'label' => 'CRM case number',
            'required' => false,
        ]);
        $form->add('trackingNumber', TextType::class, [
            'label' => 'Tracking number(s)',
            'required' => false,
        ]);

        $form->add('engineerBranch', JsEntityType::class, [
            'class' => CustomerBranch::class,
            'label' => 'Assigned to engineer',
            'label_attr' => [
                'title' => 'Ship parts to this engineer, if required.',
            ],
            'required' => false,
        ]);

        if ($rma->isNew()) {
            $form->add('createReplacementOrder', CheckboxType::class, [
                'label' => 'Create replacement order?',
                'required' => false,
            ]);

            $form->add('shipImmediately', CheckboxType::class, [
                'label' => 'Ship replacement order immediately?',
                'required' => false,
            ]);
        }
    }
}

