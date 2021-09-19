<?php

namespace Rialto\Manufacturing\BuildFiles;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Saves the build/engineering data files for a PCB to the filesystem.
 */
class PcbBuildFiles extends BuildFiles
{
    const IMAGE_TOP = 'imageTop';
    const IMAGE_BOTTOM = 'imageBottom';
    const NETLIST = 'netlist';
    const GERBERS = 'gerbers';
    const BOARD_OUTLINE = 'boardOutline';
    const DRILL_EXCELLON_24 = 'drillExcellon24';
    const PANELIZED = 'panelizedGerbers';
    const XY = 'xy';
    const SCHEMATIC = 'schematic';
    const TEST_INSTRUCTIONS = 'testInstructions';
    const EAGLE_DESIGN = 'eagleDesign';

    public function getSupportedFilenames()
    {
        return array_merge($this->getSupplierAccessibleFilenames(),
            $this->getInternalFilenames());
    }

    public static function getSupplierAccessibleFilenames()
    {
        return [
            self::IMAGE_TOP,
            self::IMAGE_BOTTOM,
            self::NETLIST,
            self::GERBERS,
            self::BOARD_OUTLINE,
            self::DRILL_EXCELLON_24,
            self::PANELIZED,
            self::XY,
            self::SCHEMATIC,
            self::TEST_INSTRUCTIONS,
        ];
    }

    public static function getInternalFilenames()
    {
        return [
            self::EAGLE_DESIGN,
        ];
    }

    /** @return File|null */
    public function getImageTop()
    {
        return $this->getUploaded(self::IMAGE_TOP);
    }

    public function setImageTop(UploadedFile $file = null)
    {
        $this->uploaded[ self::IMAGE_TOP ] = $file;
    }

    /** @return File|null */
    public function getImageBottom()
    {
        return $this->getUploaded(self::IMAGE_BOTTOM);
    }

    public function setImageBottom(UploadedFile $file = null)
    {
        $this->uploaded[ self::IMAGE_BOTTOM ] = $file;
    }

    /** @return File|null */
    public function getNetlist()
    {
        return $this->getUploaded(self::NETLIST);
    }

    public function setNetlist(UploadedFile $file = null)
    {
        $this->uploaded[self::NETLIST] = $file;
    }

    /** @return File|null */
    public function getGerbers()
    {
        return $this->getUploaded(self::GERBERS);
    }

    public function setGerbers(UploadedFile $file = null)
    {
        $this->uploaded[self::GERBERS] = $file;
    }

    /** @return File|null */
    public function getBoardOutline()
    {
        return $this->getUploaded(self::BOARD_OUTLINE);
    }

    public function setBoardOutline(UploadedFile $file = null)
    {
        $this->uploaded[self::BOARD_OUTLINE] = $file;
    }

    /** @return File|null */
    public function getDrillExcellon24()
    {
        return $this->getUploaded(self::DRILL_EXCELLON_24);
    }

    public function setDrillExcellon24(UploadedFile $file = null)
    {
        $this->uploaded[self::DRILL_EXCELLON_24] = $file;
    }

    /** @return File|null */
    public function getPanelizedGerbers()
    {
        return $this->getUploaded(self::PANELIZED);
    }

    public function setPanelizedGerbers(UploadedFile $file = null)
    {
        $this->uploaded[self::PANELIZED] = $file;
    }

    /** @return File|null */
    public function getXY()
    {
        return $this->getUploaded(self::XY);
    }

    public function setXY(UploadedFile $file = null)
    {
        $this->uploaded[self::XY] = $file;
    }

    /** @return File|null */
    public function getSchematic()
    {
        return $this->getUploaded(self::SCHEMATIC);
    }

    public function setSchematic(UploadedFile $file = null)
    {
        $this->uploaded[self::SCHEMATIC] = $file;
    }

    /** @return File|null */
    public function getTestInstructions()
    {
        return $this->getUploaded(self::TEST_INSTRUCTIONS);
    }

    public function setTestInstructions(UploadedFile $file = null)
    {
        $this->uploaded[self::TEST_INSTRUCTIONS] = $file;
    }

    /** @return File|null */
    public function getEagleDesign()
    {
        return $this->getUploaded(self::EAGLE_DESIGN);
    }

    public function setEagleDesign(UploadedFile $file = null)
    {
        $this->uploaded[self::EAGLE_DESIGN] = $file;
    }
}
