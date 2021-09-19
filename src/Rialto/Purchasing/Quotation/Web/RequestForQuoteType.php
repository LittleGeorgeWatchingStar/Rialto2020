<?php

namespace Rialto\Purchasing\Quotation\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Email\Attachment\Web\AttachmentSelectorType;
use Rialto\Email\Mailable\Web\TextMailableType;
use Rialto\Purchasing\Quotation\Email\RequestForQuote;
use Rialto\Purchasing\Supplier\Contact\Orm\SupplierContactRepository;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestForQuoteType extends DynamicFormType
{
    /**
     * @param RequestForQuote $rfq
     */
    public function updateForm(FormInterface $form, $rfq)
    {
        $supplier = $rfq->getSupplier();
        $form->add('to', EntityType::class, [
                'class' => SupplierContact::class,
                'query_builder' => function(SupplierContactRepository $repo) use ($supplier) {
                    return $repo->queryOrderContactsForSupplier($supplier);
                },
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'quoteLabel',
            ])
            ->add('cc', TextMailableType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('subject', TextType::class)
            ->add('body', TextareaType::class)
            ->add('attachments', CollectionType::class, [
                'entry_type' => AttachmentSelectorType::class,
                'by_reference' => true,
                'label' => 'Attachments',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RequestForQuote::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'RequestForQuote';
    }

}
