<?php

namespace Rialto\Printing;

/**
 * Allows you to generate printer labels using Zebra's EPL 2
 * print language.
 *
 * @see https://www.zebra.com/content/dam/zebra/manuals/en-us/printer/epl2-pm-en.pdf
 *
 * To get a Zebra printer to render EPL correctly, make sure you are sending
 * the EPL code in "raw" mode; eg:
 *
 *    lp -d your-printer -o raw your-label.epl
 */
class EplDocument
{
    const ENCODING_LATIN1 = 'A';
    const COUNTRY_USA = '001';
    const DEFAULT_LABEL_WIDTH = 795;

    const BARCODE_CODE128 = '1';

    /** @var string[] */
    private $lines = [];

    /** @var EplFont */
    private $font;

    /** @var BarcodeParams */
    private $bp;

    public function __construct()
    {
        $this->font = new EplFont();
        $this->bp = new BarcodeParams();
    }

    /**
     * For fluent interface.
     */
    public static function create(): self
    {
        return new static();
    }

    public function withDefaultSettings(): self
    {
        return $this->setCharacterSet()
            ->enableDirectThermalMode()
            ->setLabelWidth()
            ->enableTopOfFormBackup()
            ->clearImageBuffer();
    }

    public function setCharacterSet(int $bits = 8,
                                    string $encoding = self::ENCODING_LATIN1,
                                    string $country = self::COUNTRY_USA): self
    {
        return $this->add('I', $bits, $encoding, $country);
    }

    private function add(string $code, ...$args): self
    {
        $this->lines[] = $code . join(',', $args);
        return $this;
    }

    public function enableDirectThermalMode(): self
    {
        return $this->add('O', 'D');
    }

    public function setLabelWidth(int $dots = self::DEFAULT_LABEL_WIDTH): self
    {
        return $this->add('q', $dots);
    }

    /**
     * This command enables the Top Of Form Backup feature and presents the
     * last label of a batch print operation. Upon request initiating the
     * printing of the next form (or batch), the last label backs up the Top
     * Of Form before printing the next label.
     */
    public function enableTopOfFormBackup(): self
    {
        return $this->add('JF');
    }

    public function setPrintSpeed(int $speed = 2): self
    {
        return $this->add('S', $speed);
    }

    /**
     * Tells the printer to start printing from the bottom of the image buffer.
     */
    public function printFromBottom(): self
    {
        return $this->setPrintDirection('B');
    }

    private function setPrintDirection(string $code): self
    {
        return $this->add('Z', $code);
    }

    /**
     * All printer configuration commands should be issued prior calling this.
     */
    public function clearImageBuffer(): self
    {
        return $this->add('N');
    }

    public function setFontSize(int $size): self
    {
        $this->font->size = $size;
        return $this;
    }

    public function addText(string $text,
                            int $xPos,
                            int $yPos,
                            int $rotation = 0): self
    {
        $text = $this->prepareText($text);
        return $this->add('A', $xPos, $yPos, $rotation,
            $this->font->size,
            $this->font->xMult,
            $this->font->yMult,
            $this->font->getReverseCode(),
            $text);
    }

    private function prepareText(string $text): string
    {
        $text = str_replace('"', '\\"', $text);
        $text = "\"$text\"";
        return $text;
    }

    /**
     * Configure the size and parameters of 1D barcodes.
     */
    public function configureBarcode(int $narrowBarWidth,
                                     int $wideBarWidth,
                                     int $barHeight,
                                     bool $humanReadable): self
    {
        $this->bp->narrowBarWidth = $narrowBarWidth;
        $this->bp->wideBarWidth = $wideBarWidth;
        $this->bp->barHeight = $barHeight;
        $this->bp->humanReadable = $humanReadable;
        return $this;
    }

    /**
     * Add a 1D barcode in Code 128 format.
     */
    public function addCode128(string $data,
                               int $xPos,
                               int $yPos,
                               int $rotation = 0): self
    {
        return $this->addBarCode($data, $xPos, $yPos, $rotation, self::BARCODE_CODE128);
    }

    private function addBarCode(string $data,
                                int $xPos,
                                int $yPos,
                                int $rotation,
                                int $codeSelection): self
    {
        $data = $this->prepareText($data);
        return $this->add('B', $xPos, $yPos, $rotation, $codeSelection,
            $this->bp->narrowBarWidth,
            $this->bp->wideBarWidth,
            $this->bp->barHeight,
            $this->bp->getHumanReadableCode(),
            $data);
    }

    /**
     * Add a 2D barcode in Data Matrix format.
     */
    public function addDataMatrix(string $data,
                                  int $xPos,
                                  int $yPos): self
    {
        $data = $this->prepareText($data);
        return $this->add('b', $xPos, $yPos, 'D', $data);
    }

    public function addPrintCommand(int $numSets = 1): self
    {
        return $this->add('P', $numSets);
    }

    public function toString(): string
    {
        // Trailing newline is very important!
        return join("\n", $this->lines) . "\n";
    }

    public function __toString()
    {
        return $this->toString();
    }
}


class EplFont
{
    public $size = 1;  // 8 x 12 dots
    public $xMult = 1;
    public $yMult = 1;
    public $reverse = false;

    public function getReverseCode(): string
    {
        return $this->reverse ? 'R' : 'N';
    }
}

class BarcodeParams
{
    public $narrowBarWidth = 3;
    public $wideBarWidth = 6;
    public $barHeight = 12; // dots
    public $humanReadable = true;

    public function getHumanReadableCode(): string
    {
        return $this->humanReadable ? 'B' : 'N';
    }
}
