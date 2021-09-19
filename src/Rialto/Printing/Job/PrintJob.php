<?php

namespace Rialto\Printing\Job;

use Rialto\Entity\RialtoEntity;
use Rialto\Printing\Printer\Printer;

/**
 * A print job that can be printed immediately or asynchronously.
 */
class PrintJob implements RialtoEntity
{
    const FORMAT_PDF = 'application/pdf';
    const FORMAT_PS = 'application/postscript';
    const FORMAT_RAW = 'application/octet-stream';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \DateTime
     */
    private $datePrinted = null;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string An optional description of the print job.
     */
    private $description = '';

    /**
     * @var string
     */
    private $data;

    /**
     * @var integer
     */
    private $numCopies;

    /**
     * The printer that should print this job.
     * @var Printer
     */
    private $printer;

    /**
     * This field is populated if an unexpected error occurs during printing.
     *
     * @var string
     */
    private $error = '';

    public static function raw($data, $numCopies = 1)
    {
        return new self($data, self::FORMAT_RAW, $numCopies);
    }

    public static function postscript($data, $numCopies = 1)
    {
        return new self($data, self::FORMAT_PS, $numCopies);
    }

    public static function pdf($data, $numCopies = 1)
    {
        return new self($data, self::FORMAT_PDF, $numCopies);
    }

    private function __construct($data, $format, $numCopies = 1)
    {
        $this->dateCreated = new \DateTime();
        $this->data = $data;
        $this->format = $format;
        $this->numCopies = $numCopies;
    }

    public function __toString()
    {
        return "job ". $this->id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return clone $this->dateCreated;
    }

    /**
     * Marks this job as printed
     */
    public function setPrinted()
    {
        $this->datePrinted = new \DateTime();
        $this->setError(''); // clear any previous error messages.
    }

    /**
     * @return \DateTime
     */
    public function getDatePrinted()
    {
        return $this->datePrinted ? clone $this->datePrinted : null;
    }

    public function getNumCopies()
    {
        return $this->numCopies;
    }

    public function setError($error)
    {
        $error = trim($error);
        /* We don't want the act of storing the error message to itself
         * create an error due to the data being wider than the column. */
        $this->error = mb_substr($error, 0, 255);
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = substr(trim($description), 0, 255);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getContentType()
    {
        return $this->format;
    }

    public function getPrinter()
    {
        return $this->printer;
    }

    public function setPrinter(Printer $printer)
    {
        $this->printer = $printer;
    }

}
