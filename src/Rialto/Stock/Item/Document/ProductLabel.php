<?php

namespace Rialto\Stock\Item\Document;

use Rialto\Filetype\Postscript\PostscriptFont;
use Rialto\Filetype\Postscript\PostscriptImage;
use Rialto\Filetype\Postscript\PostscriptLabel;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A product label that goes on the product's box.
 */
class ProductLabel extends PostscriptLabel
{
    /** @var VersionedItem */
    private $product;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1, max=150)
     */
    private $numCopies;

    /** @var PostscriptImage|null */
    private $logo;

    public function __construct(VersionedItem $product, $numCopies)
    {
        $this->product = $product;
        $this->numCopies = $numCopies;
    }

    public function includeLogo(PostscriptImage $logo): void
    {
        $this->logo = $logo;
    }

    public function __toString()
    {
        return "{$this->numCopies} x Product Label {$this->product->getFullSku()}";
    }

    /**
     * Labels are oriented vertically, which is why width is less than height.
     */
    protected function getPageHeight()
    {
        return $this->inchToPoint(3.5);
    }

    /**
     * Labels are oriented vertically, which is why width is less than height.
     */
    protected function getPageWidth()
    {
        return $this->inchToPoint(1.125);
    }

    /**
     * @return int
     */
    public function getNumCopies()
    {
        return $this->numCopies;
    }

    /**
     * @param int $numCopies
     */
    public function setNumCopies($numCopies)
    {
        $this->numCopies = $numCopies;
    }

    protected function renderDocument()
    {
        $item = $this->product->getStockItem();
        $name = utf8ToAscii(strip_tags($item->getName()));
        $stockCode = $this->product->getFullSku();
        $barcode = $this->createBarcode($stockCode);
        $logoId = $this->getLogoId();

        $this->printPage($name, $stockCode, $barcode, $logoId);

        if ($barcode) {
            $this->closeImage($barcode);
        }
        if ($logoId) {
            $this->closeImage($logoId);
        }
    }

    private function getLogoId(): ?int
    {
        return $this->logo
            ? $this->loadImage($this->logo->getPath(), $this->logo->getType())
            : null;
    }

    private function printPage($name, $stockCode, $barcodeId = null, $logoId = null)
    {
        $this->beginPage();

        $x = 70; // make room for gumstix logo
        $y = 28;
        $this->setTextPosition($x, $y);

        $this->setPsFont(PostscriptFont::getArial(9));
        $this->writeText($name);
        $this->setPsFont(PostscriptFont::getArial(8));
        $this->continueText($stockCode);
        if ($barcodeId) {
            $this->addBarcodeImage($barcodeId);
        }
        if ($logoId) {
            $this->addLogoImage($logoId);
        }
        $this->endPage();
    }

    protected function addBarcodeImage($imageId)
    {
        $this->placeImage($imageId, 180, 8, 0.7);
    }

    protected function addLogoImage($imageId)
    {
        $this->placeImage($imageId, 5, 55, 0.1);
    }
}
