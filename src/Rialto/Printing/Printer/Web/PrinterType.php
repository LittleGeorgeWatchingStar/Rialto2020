<?php

namespace Rialto\Printing\Printer\Web;


use Gumstix\FormBundle\Form\DynamicFormType;
use Rialto\Printing\Printer\Printer;
use Rialto\Printing\Printer\ZebraPrinter;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrinterType extends DynamicFormType
{
    /**
     * @param Printer $printer
     */
    protected function updateForm(FormInterface $form, $printer)
    {
        $form
            ->add('description', TextType::class, [
                'required' => false,
            ])
            ->add('host', TextType::class)
            ->add('port', IntegerType::class);

        if ($printer instanceof ZebraPrinter) {
            $form->add('sleepTime', IntegerType::class);
        }
    }


    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Printer::class);
    }

}
