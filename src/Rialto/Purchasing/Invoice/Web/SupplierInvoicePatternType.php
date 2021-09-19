<?php

namespace Rialto\Purchasing\Invoice\Web;

use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for editing supplier invoice patterns.
 */
class SupplierInvoicePatternType extends AbstractType
{
    const PARSE_DEFINITION_HINT = 'XML document that describes where
        in the invoice the values for various header and line item fields
        are found';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('keyword', TextType::class)
            ->add('sender', TextType::class)
            ->add('location', ChoiceType::class, [
                'choices' => SupplierInvoicePattern::getLocationChoices(),
                'label' => 'Invoice location',
            ])
            ->add('format', ChoiceType::class, [
                'choices' => SupplierInvoicePattern::getFormatChoices(),
            ])
            ->add('splitPattern', TextType::class, [
                'label' => 'Split pattern',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierInvoicePattern::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'SupplierInvoicePattern';
    }

}
