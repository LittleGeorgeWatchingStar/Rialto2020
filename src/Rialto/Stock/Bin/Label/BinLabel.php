<?php

namespace Rialto\Stock\Bin\Label;

use Rialto\Filetype\Postscript\PostscriptFont;
use Rialto\Filetype\Postscript\PostscriptLabel;
use Rialto\Stock\Bin\StockBin;

/**
 * An ID label for a StockBin.
 */
class BinLabel extends PostscriptLabel
{
    private $bin;
    private $numCopies;

    public function __construct(StockBin $bin)
    {
        $error = $this->validateBin($bin);
        if ($error) {
            throw new \InvalidArgumentException("$bin has $error");
        }
        $this->bin = $bin;
        $this->numCopies = $bin->getBinStyle()->getNumLabels();
    }

    /**
     * Labels are oriented vertically, which is why width is less than height.
     */
    protected function getPageHeight()
    {
        return $this->inchToPoint(2);
    }

    /**
     * Labels are oriented vertically, which is why width is less than height.
     */
    protected function getPageWidth()
    {
        return $this->inchToPoint(1);
    }

    private function validateBin(StockBin $bin)
    {
        if (!$bin->getId()) {
            return 'no ID';
        }
        if ($bin->getQuantity() < 0) {
            return sprintf('an invalid quantity (%s)', $bin->getQuantity());
        }
        return null;
    }

    protected function renderDocument()
    {
        assertion($this->numCopies > 0);
        $barcode = $this->createBarcode($this->bin->getId());

        $this->printPage($barcode);

        if ($barcode) {
            $this->closeImage($barcode);
        }
    }

    private function printPage($imageId = null)
    {
        $this->beginPage();

        $x = 1;
        $y = $this->getPageWidth() - 15;
        $this->setTextPosition($x, $y);

        $this->setPsFont(PostscriptFont::getArial(8));
        $this->writeText(sprintf('Gumstix %s', $this->bin));
        $this->continueText($this->bin->getFullSku());
        $this->continueText($this->bin->getShelfPosition());

        $this->setPsFont(PostscriptFont::getArial(6));
        $this->continueText($this->bin->getItemName());

        $this->setPsFont(PostscriptFont::getArial(7));
        $text = sprintf('%s   %s pcs',
            date('Y-m-d'),
            number_format($this->bin->getQuantity()));
        $this->continueText($text);
        if ($imageId) {
            $this->addImage($imageId);
        }
        $this->endPage();
    }

    protected function addImage($imageId)
    {
        $xpos = $this->getPageHeight() - 53;
        $ypos = 6;
        $scale = 0.7;
        $this->placeImage($imageId, $xpos, $ypos, $scale);
    }

    public function getNumCopies(): int
    {
        return $this->numCopies;
    }
}
