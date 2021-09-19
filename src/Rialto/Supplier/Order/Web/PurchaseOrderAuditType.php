<?php

namespace Rialto\Supplier\Order\Web;


use Rialto\Manufacturing\Audit\PurchaseOrderAudit;
use Rialto\Manufacturing\Audit\Web\AuditItemType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseOrderAuditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => AuditItemType::class,
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('sendEmail', CheckboxType::class, [
                'required' => false,
                'label' => 'Send email if there are shortages?',
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PurchaseOrderAudit::class,
        ]);
    }

}
