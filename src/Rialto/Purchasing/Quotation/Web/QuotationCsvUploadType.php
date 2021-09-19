<?php

namespace Rialto\Purchasing\Quotation\Web;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Builds the form that prompts the user to upload a supplier quotation
 * .csv file.
 */
class QuotationCsvUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quotationNumber', TextType::class, [
                'label' => 'Quotation No.',
                'constraints' => new NotBlank([
                    'message' => "Quotation number cannot be blank.",
                ]),
            ])
            ->add('file', FileType::class, [
                'constraints' => new File([
                    'maxSize' => "100k",
                    'mimeTypes' => [
                        'text/csv',
                        'text/tab-separated-values',
                        'text/plain',
                    ],
                ]),
            ])
            ->add('delimiter', ChoiceType::class, [
                'choices' => [
                    "Tab" => "tab",
                    "Comma" => ",",
                ],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'csv';
    }
}
