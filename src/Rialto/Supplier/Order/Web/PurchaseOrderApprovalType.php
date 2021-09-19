<?php

namespace Rialto\Supplier\Order\Web;


use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for approving or rejecting the documentation that accompanies
 * a purchase order.
 */
class PurchaseOrderApprovalType extends DynamicFormType
{
    /**
     * @param PurchaseOrder $po
     */
    protected function updateForm(FormInterface $form, $po)
    {
        $supplier = $po->getSupplier();
        $form
            ->add('approvalStatus', ChoiceType::class, [
                'choices' => [
                    'yes' => PurchaseOrder::APPROVAL_APPROVED,
                    'no' => PurchaseOrder::APPROVAL_REJECTED,
                ],
                'expanded' => true,
                'label' => 'Approve this PO?'
            ])
            ->add('approvalReason', TextareaType::class, [
                'label' => 'Reason (if rejected)',
                'required' => false,
            ])
            ->add('supplierReference', TextType::class, [
                'label' => sprintf('%s reference, if applicable (eg RMA #)', $supplier->getName()),
                'required' => false,
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrder::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'PurchaseOrderApproval';
    }
}
