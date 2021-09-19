<?php


namespace Rialto\Purchasing\Invoice\Command;


use Rialto\Purchasing\Invoice\SupplierInvoice;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Command representing a request to upload a raw file and assign it to an
 * existing SupplierInvoice.
 */
final class UploadSupplierInvoiceFileCommand
{
    /**
     * @var SupplierInvoice
     * @Assert\NotNull(message="Supplier invoice is required")
     *
     */
    public $invoice;

    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotBlank("Filename must not be blank")
     */
    public $filename;

    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotBlank("File data must not be blank")
     */
    public $filedata;

    public static function forInvoice(SupplierInvoice $invoice)
    {
        $command = new self();
        $command->invoice = $invoice;

        return $command;
    }
}
