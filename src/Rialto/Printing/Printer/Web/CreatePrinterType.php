<?php

namespace Rialto\Printing\Printer\Web;

use Rialto\Printing\Printer\StandardPrinter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for creating a printer.
 */
class CreatePrinterType extends AbstractType
{
    /**
     * @param StandardPrinter $printer
     */
    public function buildForm(FormBuilderInterface $form, $printer)
    {
        $form
            ->add('id', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('host', TextType::class)
            ->add('port', IntegerType::class)
            ->add('submit', SubmitType::class);
    }


    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', StandardPrinter::class);
    }
}
