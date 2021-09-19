<?php

namespace Rialto\Purchasing\Invoice\Parser;

use DateTime;
use Doctrine\ORM\NoResultException;
use Gumstix\GeographyBundle\Model\Country;
use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use Rialto\Database\Orm\DbKeyException;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item\StockItem;

/**
 * Parses invoice data from a supplier.
 */
class SupplierInvoiceParser
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var DbManager */
    private $dbm;

    /** @var Supplier */
    private $supplier;

    /** @var \Exception[] */
    private $errors;

    public function __construct(SerializerInterface $serializer, DbManager $dbm)
    {
        $this->serializer = $serializer;
        $this->dbm = $dbm;
    }

    /**
     * Parses the invoice data and returns a list of SupplierInvoice objects.
     *
     * @param string[] $data The invoice data
     * @return SupplierInvoice[]
     */
    public function parse(SupplierInvoicePattern $pattern, array $data)
    {
        $this->supplier = $pattern->getSupplier();
        $this->errors = [];
        $rules = $pattern->getParseRules($this->serializer);
        $invoices = [];
        $invoiceData = $this->splitInvoices($rules, $data);
        foreach ($invoiceData as $invoiceGrid) {
            if (!is_array($invoiceGrid)) {
                continue;
            }
            try {
                $invoice = $this->parseHeaderValues($rules->headers, $invoiceGrid);
            } catch (InvalidArgumentException $ex) {
                $this->errors[] = SupplierInvoiceParserException::fromPrevious($ex);
                continue;
            }

            $lineItems = [];
            if ($rules->hasLines()) {
                $lineItems = $this->parseLines($rules, $invoiceGrid);
            }
            foreach ($lineItems as $lineItem) {
                $invoice->addItem($lineItem);
            }

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    /**
     * Splits the grid into sub-grids, one for each invoice in the PDF.
     *
     * @return string[] A list of sub-grids.
     */
    private function splitInvoices(RuleSet $rules, array $grid)
    {
        /* If there's only one invoice per grid, return it. */
        if (!$rules->start) {
            return [$grid];
        }
        if (!$rules->end) {
            throw new SupplierInvoiceParserException(sprintf(
                'Invalid end word for vendor %s', $this->supplier
            ));
        }
        $subgrids = [];
        $currentSubgrid = null;
        foreach ($grid as $line) {
            foreach ($line as $cell) {
                if ($rules->isEnd($cell) && (null !== $currentSubgrid)) {
                    /* Store the previous subgrid. */
                    $subgrids[] = $currentSubgrid;
                    /* Wait until we find the next startWord. */
                    $currentSubgrid = null;
                }
                if ($rules->isStart($cell) && (null === $currentSubgrid)) {
                    /* Start a new subgrid */
                    $currentSubgrid = [];
                    break;
                }
            }
            if (null !== $currentSubgrid) {
                $currentSubgrid[] = $line;
            }
        }
        $subgrids[] = $currentSubgrid;
        return $subgrids;
    }

    /** @return SupplierInvoice */
    private function parseHeaderValues(array $headers, array $grid)
    {
        $invoice = new SupplierInvoice($this->supplier);
        foreach ($headers as $header) {
            /* @var $header Header */
            $name = $header->name;
            assertion(!empty($name));
            $value = $this->findHeaderValue($header, $grid);
            $this->validateValue($header, $value);
            $this->setField($invoice, $name, $value);
        }
        return $invoice;
    }

    private function setField($object, $fieldname, $value)
    {
        $method = 'set' . ucfirst($fieldname);
        if (method_exists($object, $method)) {
            $object->$method($value);
        }
    }

    private function findHeaderValue(Header $header, array $grid)
    {
        if ($header->isStandingOrder()) {
            return $this->findMostRecentOpenOrder();
        }
        foreach ($grid as $lineNum => $line) {
            foreach ($line as $colNum => $cell) {
                if (!$header->matches($cell)) {
                    continue;
                }

                /* If this cell matches the header text, then we look
                 * for a matching value. */
                $matcher = new Matcher($header, $this->dbm);
                foreach ($header->positions as $pos) {
                    $x = $colNum + (int) $pos->x;
                    $y = $lineNum + (int) $pos->y;
                    if (!isset($grid[$y][$x])) {
                        continue;
                    }
                    $potential = $grid[$y][$x];
                    $match = $matcher->getMatch($potential);
                    if (null !== $match) {
                        return $this->prepHeaderValue($header->name, $match);
                    }
                }
            }
        }
        return null;
    }

    /** @return PurchaseOrder */
    private function findMostRecentOpenOrder()
    {
        try {
            /** @var $repo PurchaseOrderRepository */
            $repo = $this->dbm->getRepository(PurchaseOrder::class);
            return $repo->findMostRecentOpenOrder($this->supplier);
        } catch (NoResultException $ex) {
            throw new SupplierInvoiceParserException("No open PO exists for {$this->supplier}", $ex->getCode(), $ex);
        }
    }

    private function validateValue(Field $field, $value)
    {
        if (!$field->isValid($value)) {
            throw new SupplierInvoiceParserException("Unable to locate required field '{$field->name}'");
        }
    }

    private function convertException(\Exception $ex)
    {
        throw new SupplierInvoiceParserException(
            $ex->getMessage(),
            $ex->getCode(),
            $ex
        );
    }

    private function prepHeaderValue($fieldname, $value)
    {
        switch ($fieldname) {
            case 'purchaseOrder':
                return $this->findPurchaseOrder($value);
            case 'date':
            case 'invoiceDate':
                return $value ? new DateTime($value) : null;
            default:
                return $value;
        }
    }

    private function findPurchaseOrder($poNumber)
    {
        if (!$poNumber) {
            return null;
        }
        $po = $this->dbm->find(PurchaseOrder::class, $poNumber);
        if ($po) {
            return $po;
        }
        throw new SupplierInvoiceParserException("No such PO '$poNumber'");
    }

    private function parseLines(RuleSet $rules, array $grid)
    {
        $lines = $this->findEligibleLines($rules, $grid);
        $results = [];
        /* If the invoice does not supply line item numbers, we'll generate them. */
        $lineItemNumber = 1;
        foreach ($lines as $lineNum => $line) {
            /* How many columns do we expect the line to have? */
            if (!$rules->hasEnoughColumns($line)) {
                continue;
            }

            $lineItem = new SupplierInvoiceItem();
            $failed = false;
            foreach ($rules->lines as $field) {
                /* @var $field Field */
                if ($field->isConstant()) {
                    $this->setField($lineItem, $field->name, $field->text);
                    continue;
                }
                $matcher = new Matcher($field, $this->dbm);
                $match = null;

                /* See if any of the possible positions for this field match. */
                foreach ($field->positions as $pos) {
                    $x = (int) $pos->x;
                    if ($x < 0) {
                        $x = count($line) + $x;
                    }
                    $y = $lineNum;
                    if (isset($pos->y)) {
                        $y += (int) $pos->y;
                    }
                    if (!isset($lines[$y][$x])) {
                        continue;
                    }
                    $potential = $lines[$y][$x];
                    for ($i = 1; $i <= $pos->deltaX; ++$i) {
                        if (!isset($lines[$y][$x + $i])) {
                            continue;
                        }
                        $potential .= $lines[$y][$x + $i];
                    }
                    if ($field->matchesPrefix($potential)) {
                        $potential = $field->stripPrefix($potential);
                    } else {
                        continue;
                    }
                    $match = $matcher->getMatch($potential);
                    if (null !== $match) {
                        try {
                            $match = $this->prepDetailValue($field->name, $match);
                        } catch (DbKeyException $ex) {
                            $this->convertException($ex);
                        }
                        $this->setField($lineItem, $field->name, $match);
                        break;
                    }
                }

                /* If no positions match, and this field is required,
                 * then this line is a failure. */
                if (!$field->isValid($match)) {
                    $failed = true;
                    break;
                }
            }
            if ($failed) {
                continue;
            }
            /* If the invoice does not supply line item numbers, we'll generate them. */
            if (null == $lineItem->getLineNumber()) {
                $lineItem->setLineNumber($lineItemNumber);
                $lineItemNumber++;
            }
            $results[] = $lineItem;
        }
        return $results;
    }

    private function findEligibleLines(RuleSet $rules, array $grid)
    {
        $started = false;
        $results = [];
        foreach ($grid as $line) {
            if (count($line) <= 0) {
                continue;
            }
            foreach ($line as $cell) {
                if ($started) {
                    if ($rules->isLineEnd($cell)) {
                        return $results;
                    }
                } elseif ($rules->isLineStart($cell)) {
                    $started = true;
                    break; /* next line */
                }
            }
            if ($started) {
                $results[] = $line;
            }
        }
        return [];
    }

    private function prepDetailValue($fieldname, $value)
    {
        switch ($fieldname) {
            case 'stockItem':
                return $this->dbm->need(StockItem::class, $value);
            case 'countryOfOrigin':
                return Country::fromString($value);
            case 'reachDate':
                return $value ? new DateTime($value) : null;
            default:
                return $value;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
