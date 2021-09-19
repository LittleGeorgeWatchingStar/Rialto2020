<?php

namespace Rialto\Shipping\Label\Ecia;

use InvalidArgumentException;
use Rialto\Allocation\AllocationInterface;
use Rialto\Printing\EplDocument;
use Rialto\Sales\Shipping\ShippableOrderItem;
use Rialto\Stock\Bin\StockBin;


/**
 * The ECIA definition of a "unit pack" basically corresponds to Rialto's
 * concept of a stock bin.
 */
class UnitPackLabel
{
    /**
     * Fields with standardized Data Identifier codes.
     */
    private static $standardFields = [
        'P' => 'Customer Part',
        '1P' => 'Manufacturer Part',
        'Q' => 'Quantity',
        '4L' => 'COO',  // Country of Origin
        '9D' => 'Date Code',
        '1T' => 'Lot Code',
    ];

    /**
     * The field values, indexed by code, for this label.
     *
     * It's okay to add ad-hoc fields, too (eg, RoHS).
     */
    private $values = [];

    public static function fromBin(StockBin $bin): self
    {
        $label = new self();
        $label->setManufacturerPart($bin->getSku());
        $label->setQuantity($bin->getQtyRemaining());
        $label->setCountryOfOrigin($bin->getCountryOfOrigin());
        $label->setDateToNow();
        $label->setLotCode($bin->getId());
        $label->setRohsStatus($bin->getRohsStatus());
        return $label;
    }

    public static function fromAllocation(AllocationInterface $alloc): self
    {
        assert($alloc->isWhereNeeded());
        /** @var StockBin $bin */
        $bin = $alloc->getSource();
        $label = self::fromBin($bin);
        $label->setQuantity($alloc->getQtyAllocated());
        return $label;
    }

    public static function fromOrderItem(ShippableOrderItem $item): self
    {
        $label = new self();
        $label->setManufacturerPart($item->getSku());
        $label->setQuantity($item->getQtyToShip());
        $label->setCountryOfOrigin($item->getCountryOfOrigin());
        $label->setDateToNow();
        return $label;
    }

    public function setCustomerPart(string $partNo)
    {
        $this->setValue('P', $partNo);
    }

    private function setManufacturerPart(string $mpn)
    {
        $this->setValue('1P', $mpn);
    }

    private function setQuantity($quantity)
    {
        assert($quantity > 0, new InvalidArgumentException("Label qty must be positive"));
        $this->setValue('Q', $quantity);
    }

    private function setCountryOfOrigin(string $countryCode)
    {
        $this->setValue('4L', $countryCode);
    }

    private function setDateToNow()
    {
        $this->setValue('9D', date('YW'));
    }

    private function setLotCode($lotCode)
    {
        $this->setValue('1T', $lotCode);
    }

    private function setRohsStatus(string $rohs)
    {
        $this->setValue('RoHS', $rohs);
    }

    private function setValue(string $field, $value)
    {
        $this->values[$field] = $value;
    }

    /**
     * Generate the contents of a 2D barcode according to ECIA specification
     * EIGP 114.00.
     */
    public function get2dBarcodeContents(): string
    {
        $toInclude = $this->getFieldsFor2dBarcode();

        $rs = "\x1e"; // ASCII record separator
        $gs = "\x1d"; // ASCII group separator
        $eot = "\x04"; // ASCII end of transmission
        $prefix = "[)>"; // ECIA standard prefix
        $format = "06"; // ISO/IEC 15434 Format 06

        $result = $prefix . $rs . $format;
        foreach ($toInclude as $field => $value) {
            $result .= $gs . $field . $value;
        }
        $result .= $rs . $eot;
        return $result;
    }

    private function getFieldsFor2dBarcode(): array
    {
        $toInclude = [];
        foreach (self::$standardFields as $code => $label) {
            if ($this->hasValue($code)) {
                $toInclude[$code] = $this->getValue($code);
            }
        }
        // ensure fields are always included in the same order
        ksort($toInclude);
        return $toInclude;
    }

    private function hasValue(string $field): bool
    {
        return isset($this->values[$field]);
    }

    private function getValue(string $field)
    {
        return $this->values[$field];
    }

    /**
     * Generate the EPL printer commands needed to print this label on
     * a Zebra printer.
     */
    public function generateEpl(): string
    {
        $doc = EplDocument::create()
            ->withDefaultSettings();

        $doc->setFontSize(3);
        $doc->configureBarcode(2, 4, 28, false);
        $lineHeight = 32;
        $fieldPadding = 16;
        $leftCol = 40;
        $rightCol = $leftCol + 480;
        $yPos = 24;

        $doc->addText($this->makeLabelLine('P'), $leftCol, $yPos);
        $yPos += $lineHeight;
        $doc->addCode128($this->getValue('P'), $leftCol, $yPos);
        $yPos += $lineHeight + $fieldPadding;

        $doc->addText($this->makeLabelLine('1P'), $leftCol, $yPos);
        $yPos += $lineHeight;
        $doc->addCode128($this->getValue('1P'), $leftCol, $yPos);
        $yPos += $lineHeight + $fieldPadding;

        $doc->addText($this->makeLabelLine('Q'), $leftCol, $yPos);
        $doc->addText($this->makeLabelLine('4L'), $rightCol, $yPos);
        $yPos += $lineHeight;
        $doc->addCode128($this->getValue('Q'), $leftCol, $yPos);
        $doc->addCode128($this->getValue('4L'), $rightCol, $yPos);
        $yPos += $lineHeight + $fieldPadding;

        $doc->addDataMatrix($this->get2dBarcodeContents(), $rightCol, $yPos);

        $doc->addText($this->makeLabelLine('9D'), $leftCol, $yPos);
        $yPos += $lineHeight;
        $doc->addCode128($this->getValue('9D'), $leftCol, $yPos);
        $yPos += $lineHeight + $fieldPadding;

        if ($this->hasValue('1T')) {
            $doc->addText($this->makeLabelLine('1T'), $leftCol, $yPos);
            $yPos += $lineHeight;
            $doc->addCode128($this->getValue('1T'), $leftCol, $yPos);
            $yPos += $lineHeight + $fieldPadding;
        }
        if ($this->hasValue('RoHS')) {
            $doc->addText('ROHS ' . $this->getValue('RoHS'), $leftCol, $yPos);
        }

        $doc->addPrintCommand();
        return $doc->toString();
    }

    /**
     * A line of human readable text for the label; eg:
     *   (P) Customer Part: THE-PART-NO
     */
    private function makeLabelLine(string $field): string
    {
        $name = self::$standardFields[$field];
        $value = $this->getValue($field);
        return "($field) $name: $value";
    }
}
