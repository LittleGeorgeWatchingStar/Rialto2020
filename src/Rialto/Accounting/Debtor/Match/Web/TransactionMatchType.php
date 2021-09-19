<?php

namespace Rialto\Accounting\Debtor\Match\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\Match\TransactionMatch;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionMatchType extends DynamicFormType
{
    protected function updateForm(FormInterface $form, $match)
    {
        /* @var $match TransactionMatch */

        if ( $match->isBalanced() ) {
            $form->add('selected', CheckboxType::class, [
                'required' => false,
                'attr' => ['class' => 'allocateCheckbox'],
                'label' => 'Batch allocate',
                'error_bubbling' => true,
            ]);
        }
        elseif ( $match->isSubjectToTransferFee() ) {
            $form->add('feeAmount', MoneyType::class, [
                'required' => false,
                'currency' => Currency::USD,
                'label' => 'Fee amount',
                'attr' => ['class' => 'feeAmount'],
                'error_bubbling' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransactionMatch::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'match';
    }
}
