<?php


namespace Rialto\Purchasing\Invoice\Web;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

final class UploadSupplierInvoiceFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uploadedFile', FileType::class, [
            'label' => 'Invoice File',
            ])
            ->add('submit', SubmitType::class);
    }
}