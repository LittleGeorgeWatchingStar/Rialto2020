<?php


namespace Rialto\Purchasing\Invoice\Web;


use Rialto\Email\Mailable\Web\MailableType;
use Rialto\Purchasing\Invoice\Email\SupplierInvoiceEmail;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for sending a supplier invoice email.
 *
 * @see SupplierInvoiceEmail
 */
final class SupplierInvoiceEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $invoice SupplierInvoice */
        $invoice = $options['invoice'];
        $builder->add('to', MailableType::class, [
            'multiple' => true,
            'expanded' => true,
            'by_reference' => false,
            'choices' => $this->getRecipientChoices($invoice),
            'attr' => ['class' => 'checkbox_group'],
        ])
        ->add('cc', MailableType::class, [
            'multiple' => true,
            'expanded' => true,
            'by_reference' => false,
            'choices' => $this->getRecipientChoices($invoice),
            'attr' => ['class' => 'checkbox_group'],
        ])
        ->add('subject', TextType::class)
        ->add('body', TextareaType::class, [
            'required' => false,
        ]);
    }

    private function getRecipientChoices(SupplierInvoice $invoice)
    {
        $toList = [];
        if ($supplier = $invoice->getDeliveryLocation()->getSupplier()) {
            $contacts = $supplier->getActiveContacts();
            foreach ($contacts as $to) {
                $toList[$to->getEmail()] = $to;
            }
        } else {
            $contact = $invoice->getDeliveryLocation()->getContact();
            $toList[$contact->getEmail()] = $contact;
        }
        return $toList;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('invoice');
        $resolver->setAllowedTypes('invoice', SupplierInvoice::class);
        $resolver->setDefaults([
            'data_class' => SupplierInvoiceEmail::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoiceEmail';
    }
}
