<?php

namespace Rialto\Accounting\Supplier\Email;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Email\Mailable\Web\MailableType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for sending an email to a supplier regarding a supplier
 * transaction.
 *
 * @see SupplierTransactionEmail
 */
class SupplierTransactionEmailType extends DynamicFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierTransactionEmail::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierTransactionEmail';
    }

    protected function updateForm(FormInterface $form, $email)
    {
        /* @var $email SupplierTransactionEmail */
        $form->add('to', MailableType::class, [
            'multiple' => true,
            'expanded' => true,
            'choices' => $email->getContacts(),
        ])
        ->add('subject', TextType::class)
        ->add('body', TextareaType::class, [
            'required' => false,
        ]);
    }


}
