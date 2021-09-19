<?php

namespace Rialto\Purchasing\Order\Web;

use Gumstix\FormBundle\Form\Type\DynamicFormType;
use Rialto\Email\Attachment\Web\AttachmentSelectorType;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Security\Role\Role;
use Rialto\Security\User\Orm\UserRepository;
use Rialto\Security\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for sending a PO email to the supplier.
 */
class PurchaseOrderEmailType extends DynamicFormType
{
    /**
     * @param FormInterface $form
     * @param PurchaseOrderEmail $email
     */
    protected function updateForm(FormInterface $form, $email)
    {
        assertion($email instanceof PurchaseOrderEmail);

        $contacts = $email->getSupplierContacts();
        $form
            ->add('to', EntityType::class, [
                'class' => SupplierContact::class,
                'choices' => $contacts,
                'choice_label' => 'emailLabel',
                'expanded' => true,
                'multiple' => true,
                'error_bubbling' => true,
                'attr' => ['class' => 'checkbox_group'],
            ])
            ->add('cc', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $repo) {
                    return $repo->queryMailableByRole(Role::PURCHASING);
                },
                'choice_label' => 'emailLabel',
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'error_bubbling' => true,
                'attr' => ['class' => 'checkbox_group'],
            ])
            ->add('body', TextareaType::class)
            ->add('additionalAttachments', CollectionType::class, [
                'entry_type' => AttachmentSelectorType::class,
                'by_reference' => true,
                'label' => 'Additional attachments',
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
