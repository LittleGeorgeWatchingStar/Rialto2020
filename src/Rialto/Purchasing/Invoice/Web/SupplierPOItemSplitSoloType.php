<?php

namespace Rialto\Purchasing\Invoice\Web;

use Rialto\Purchasing\Invoice\SupplierPOItemsSplitSolo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Form for editing an item in a supplier invoice.
 */
class SupplierPOItemSplitSoloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('splitToThis', CheckboxType::class, ['mapped' => false, 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierPOItemsSplitSolo::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoiceItem';
    }

}
