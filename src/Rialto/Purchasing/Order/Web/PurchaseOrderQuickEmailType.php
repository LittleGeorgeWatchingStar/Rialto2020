<?php

namespace Rialto\Purchasing\Order\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A small form for quickly sending a PO to the supplier.
 */
class PurchaseOrderQuickEmailType extends DynamicFormType
{
    /**
     * @param PurchaseOrderEmail $email
     */
    protected function updateForm(FormInterface $form, $email)
    {
        $contacts = $email->getOrderContacts();
        $form->add('to', EntityType::class, [
            'class' => SupplierContact::class,
            'choices' => $contacts,
            'choice_label' => 'emailLabel',
            'expanded' => true,
            'multiple' => true,
            'error_bubbling' => true,
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrderEmail::class,
            'error_bubbling' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'PurchaseOrderEmail';
    }
}
