<?php

namespace Rialto\Sales\Order\Web;

use Rialto\Email\Mailable\Web\MailableType;
use Rialto\Sales\Order\Email\SalesOrderEmail;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for sending a sales order email.
 *
 * @see SalesOrderEmail
 */
class SalesOrderEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $order SalesOrder */
        $order = $options['order'];
        $builder->add('to', MailableType::class, [
            'multiple' => true,
            'expanded' => true,
            'by_reference' => false,
            'choices' => $this->getRecipientChoices($order),
            'attr' => ['class' => 'checkbox_group'],
        ])
        ->add('cc', MailableType::class, [
            'multiple' => true,
            'expanded' => true,
            'by_reference' => false,
            'choices' => $this->getRecipientChoices($order),
            'attr' => ['class' => 'checkbox_group'],
        ])
        ->add('template', ChoiceType::class, [
            'choices' => SalesOrderEmail::getTemplates(),
            'placeholder' => '-- choose --',
            'attr' => [
                'order-id' => $order->getId(),
            ],
        ])
        ->add('attachmentType', ChoiceType::class, [
            'choices' => SalesOrderEmail::getAttachmentTypes(),
            'required' => false,
            'placeholder' => '-- none --',
            'label' => 'Attachment',
            'attr' => [
                'order-id' => $order->getId(),
            ],
        ])
        ->add('subject', TextType::class)
        ->add('body', TextareaType::class, [
            'required' => false,
        ]);
    }

    private function getRecipientChoices(SalesOrder $order)
    {
        $customer = $order->getCustomer();
        $branch = $order->getCustomerBranch();
        $allBranches = $customer->getBranches();

        $choices = [];
        foreach ($allBranches as $b) {
            $choices[$b->getEmail()] = $b;
        }
        $choices[$customer->getEmail()] = $customer;
        $choices[$branch->getEmail()] = $branch;
        $choices[$order->getEmail()] = $order;
        return array_values($choices);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('order');
        $resolver->setAllowedTypes('order', SalesOrder::class);
        $resolver->setDefaults([
            'data_class' => SalesOrderEmail::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SalesOrderEmail';
    }

}
