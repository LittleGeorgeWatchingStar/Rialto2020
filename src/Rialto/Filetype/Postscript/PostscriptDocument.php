<?php

namespace Rialto\Filetype\Postscript;

use Rialto\Filesystem\FilesystemException;


/**
 * Base class for creating postscript documents.
 */
abstract class PostscriptDocument
{
    protected $psLabel = null;
    private $tempFilename = null;

    /**
     * @return string
     *  The postscript file data
     */
    public function render()
    {
        $this->createDocument();
        $this->renderDocument();
        $psdata = $this->closeDocument();
        return $psdata;
    }

    protected abstract function renderDocument();

    private function createDocument()
    {
        if (!extension_loaded("ps")) {
            throw new \RuntimeException('Extension "ps" is not installed or loaded');
        }

        $this->tempFilename = tempnam('/tmp', 'LBL_');
        $this->psLabel = ps_new();

        ps_set_info($this->psLabel,
            "BoundingBox",
            sprintf("0 0 %s %s", $this->getPageWidth(), $this->getPageHeight())
        );
        ps_set_parameter($this->psLabel, "warning", "true");

        ps_open_file($this->psLabel, $this->tempFilename);
    }

    private function closeDocument()
    {
        ps_close($this->psLabel);
        $psdata = file_get_contents($this->tempFilename);
        if (PHP_MAJOR_VERSION < 7) {
            // Workaround for https://bugs.php.net/bug.php?id=74124
            ps_delete($this->psLabel);
        }
        $this->psLabel = null;
        unlink($this->tempFilename);
        $this->tempFilename = null;
        return $psdata;
    }

    protected function beginPage()
    {
        ps_begin_page($this->psLabel, $this->getPageWidth(), $this->getPageHeight());
        $this->setCoordinateOrientation();
    }

    /**
     * @return int In points
     */
    protected abstract function getPageHeight();

    /**
     * @return int In points
     */
    protected abstract function getPageWidth();

    protected function inchToPoint($inches)
    {
        return (int) round($inches * 72);
    }

    protected function setCoordinateOrientation()
    {
        /* override */
    }

    protected function setTextPosition($x, $y)
    {
        ps_set_text_pos($this->psLabel, $x, $y);
    }

    protected function writeText($text)
    {
        try {
            ps_show($this->psLabel, utf8ToAscii($text));
        } catch (\ErrorException $ex) {
            $this->handlePsException($ex);
        }
    }

    protected function handlePsException(\ErrorException $ex)
    {
        /* Really, who cares about missing ligatures? */
        if (stripos($ex->getMessage(), 'ligature') === false) {
            throw $ex;
        }
    }

    protected function continueText($text)
    {
        try {
            ps_continue_text($this->psLabel, utf8ToAscii($text));
        } catch (\ErrorException $ex) {
            $this->handlePsException($ex);
        }
    }

    protected function setPsFont(PostscriptFont $font)
    {
        $fontId = ps_findfont($this->psLabel, $font->getPathWithoutExtension(), null, 1);
        if (!$fontId) throw new \InvalidArgumentException(sprintf(
            "Unable to load font '%s'", $font->getName()
        ));
        ps_setfont($this->psLabel, $fontId, $font->getSize());
    }

    /**
     * @param string $imagePath
     * @param string $imageType
     * @return int
     *  The image resource ID.
     * @throws \InvalidArgumentException
     * @throws FilesystemException
     */
    protected function loadImage($imagePath, $imageType)
    {
        if (!is_file($imagePath)) throw new \InvalidArgumentException(
            "No such image file $imagePath"
        );
        $imageId = ps_open_image_file($this->psLabel, $imageType, $imagePath);
        if (!$imageId) {
            throw new FilesystemException($imagePath, 'unable to read image');
        }
        return $imageId;
    }

    protected function placeImage($imageId, $x, $y, $scale)
    {
        $success = ps_place_image($this->psLabel, $imageId, $x, $y, $scale);
        if (!$success) throw new \UnexpectedValueException(
            'Unable to place image into postscript label'
        );
    }

    protected function closeImage($imageId)
    {
        $result = ps_close_image($this->psLabel, $imageId);
        if (null !== $result) throw new \UnexpectedValueException(
            'Unable to close image for postscript label'
        );
    }

    protected function endPage()
    {
        ps_end_page($this->psLabel);
    }

    protected function splitTextIntoLines($text, $fontSize)
    {
        $maxCharsPerLine = $this->getMaxCharsPerLine($fontSize);
        $break = "\n";
        $wrapped = wordwrap($text, $maxCharsPerLine, $break);
        return explode($break, $wrapped);
    }

    protected function getMaxCharsPerLine($fontSize)
    {
        /* Estimate of how wide characters are. */
        $charWidth = 0.7 * $fontSize;
        return (int) ($this->getPageHeight() / $charWidth);
    }
}
