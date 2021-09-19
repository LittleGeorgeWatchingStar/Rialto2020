<?php

namespace Rialto\Filetype\Postscript;

use Rialto\Filesystem\FilesystemException;
use Rialto\Filetype\Image\QrCodeGenerator;

/**
 * A postscript document for printing a label.
 */
abstract class PostscriptLabel extends PostscriptDocument
{
    const PRINT_BARCODES = true;

    protected function setCoordinateOrientation()
    {
        /* Orient the labels vertically */
        ps_rotate($this->psLabel, -90.0);
        ps_translate($this->psLabel, -$this->getPageHeight(), 0);
    }

    /**
     * @param string $string
     *  The contents of the barcode.
     * @return int|null
     *  The image resource ID; null if barcodes are disabled.
     */
    protected function createBarcode($string)
    {
        if (!self::PRINT_BARCODES) {
            return null;
        }
        $imagePath = $this->createBarcodeFile();
        $margin = 1;
        $generator = new QrCodeGenerator($margin);
        $generator->writeToFile($imagePath, $string);
        $imageId = $this->loadImage($imagePath, QrCodeGenerator::IMAGE_FORMAT);
        unlink($imagePath);
        return $imageId;
    }

    private function createBarcodeFile()
    {
        $tempDir = '/tmp';
        $imagePath = tempnam($tempDir, 'LBL_QRCODE_');
        if (!$imagePath) {
            throw new FilesystemException($tempDir,
                'unable to create temporary file'
            );
        }
        return $imagePath;
    }

}
