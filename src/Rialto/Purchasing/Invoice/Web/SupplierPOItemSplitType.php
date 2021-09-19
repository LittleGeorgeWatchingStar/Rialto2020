<?php

namespace Rialto\Purchasing\Invoice\Web;

use Rialto\Purchasing\Invoice\SupplierPOItemSplit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for editing an item in a supplier invoice.
 */
class SupplierPOItemSplitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('attachments', collectionType::class, [
            'entry_type' => SupplierPOItemSplitSoloType::class,
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierPOItemSplit::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoiceItemSplit';
    }

}
