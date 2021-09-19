<?php

namespace Rialto\PcbNg\Service;

use PhpZip\ZipFile;
use Rialto\PcbNg\Api\Bundle;

/**
 * Renames files in the gerbers archive so that the PCB:NG API can parse the file names.
 */
class GerbersConverter
{
    const GEPPETTO_TOP_COPPER = 'cmp.gbr';
    const GEPPETTO_TOP_PASTE = 'crc.gbr';
    const GEPPETTO_BOTTOM_PASTE = 'crs.gbr';
    const GEPPETTO_DRILL = 'drd.gbr';

    const GEPPETTO_LAYER_2 = 'ly2.gbr';
    const GEPPETTO_LAYER_3 = 'ly3.gbr';
    const GEPPETTO_LAYER_4 = 'ly4.gbr';
    const GEPPETTO_LAYER_5 = 'ly5.gbr';

    const GEPPETTO_TOP_SILK = 'plc.gbr';
    const GEPPETTO_BOTTOM_SILK = 'pls.gbr';
    const GEPPETTO_BOTTOM_COPPER = 'sol.gbr';
    const GEPPETTO_TOP_MASK = 'stc.gbr';
    const GEPPETTO_BOTTOM_MASK = 'sts.gbr';

    public function convert(string $zipDataString,
                            ?string $boardOutlineGerberDataString,
                            ?string $drillExcellon24DataString): string
    {
        $zip = new ZipFile();
        $zip->openFromString($zipDataString);

        // We need an outline layer to have a preview on PCB:NG
        if ($boardOutlineGerberDataString) {
            $zip->addFromString('outline.gbr', $boardOutlineGerberDataString);
        }

        // Our drd.gbr does not work on PCB:NG.
        if ($drillExcellon24DataString) {
            $zip->addFromString('drd_excellon_24.gbr', $drillExcellon24DataString);
        }

        // Map with PCB:NG API instead.
//        $this->renameFile(self::GEPPETTO_TOP_COPPER, 'TopCopper.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_TOP_PASTE, 'TopPaste.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_BOTTOM_PASTE, 'BottomPaste.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_DRILL, 'Drill.gbr', $zip); // Doesn't work on PCB:NG
//
//        $this->renameFile(self::GEPPETTO_LAYER_2, 'Internal1.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_LAYER_3, 'Internal2.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_LAYER_4, 'Internal3.gbr', $zip); // PCB:NG doesn't have this layer.
//        $this->renameFile(self::GEPPETTO_LAYER_5, 'Internal4.gbr', $zip); // PCB:NG doesn't have this layer.
//
//        $this->renameFile(self::GEPPETTO_TOP_SILK, 'TopSilk.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_BOTTOM_SILK, 'BottomSilk.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_BOTTOM_COPPER, 'BottomCopper.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_TOP_MASK, 'TopMask.gbr', $zip);
//        $this->renameFile(self::GEPPETTO_BOTTOM_MASK, 'BottomMask.gbr', $zip);

        return $zip->outputAsString();
    }

    private function renameFile(string $oldName, string $newName, ZipFile &$zip): void
    {
        if ($zip->hasEntry($oldName)) {
            $zip->rename($oldName, $newName);
        }
    }

    /**
     * @return array layers_urls for PCB:NG API.
     */
    public function createMapping(Bundle $bundle): array
    {
        $layersUrls = [];
        $this->addMapping('outline', 'outline.gbr', $bundle, $layersUrls);

        $this->addMapping('top_copper', self::GEPPETTO_TOP_COPPER, $bundle, $layersUrls);
        $this->addMapping('top_paste', self::GEPPETTO_TOP_PASTE, $bundle, $layersUrls);
        $this->addMapping('bottom_paste', self::GEPPETTO_BOTTOM_PASTE, $bundle, $layersUrls);

        $this->addMapping('drill', 'drd_excellon_24.gbr', $bundle, $layersUrls);

        $this->addMapping('internal1', self::GEPPETTO_LAYER_2, $bundle, $layersUrls);
        $this->addMapping('internal2', self::GEPPETTO_LAYER_3, $bundle, $layersUrls);
        $this->addMapping('internal3', self::GEPPETTO_LAYER_4, $bundle, $layersUrls);
        $this->addMapping('internal4', self::GEPPETTO_LAYER_5, $bundle, $layersUrls);

        $this->addMapping('top_silk', self::GEPPETTO_TOP_SILK, $bundle, $layersUrls);
        $this->addMapping('bottom_silk', self::GEPPETTO_BOTTOM_SILK, $bundle, $layersUrls);
        $this->addMapping('bottom_copper', self::GEPPETTO_BOTTOM_COPPER, $bundle, $layersUrls);
        $this->addMapping('top_mask', self::GEPPETTO_TOP_MASK, $bundle, $layersUrls);
        $this->addMapping('bottom_mask', self::GEPPETTO_BOTTOM_MASK, $bundle, $layersUrls);

        return $layersUrls;
    }

    private function addMapping(string $pcbNgName,
                                string $geppettoFilename,
                                Bundle $bundle,
                                array &$layersUrls): void {
        if ($bundle->hasFilename($geppettoFilename)) {
            $layersUrls[$pcbNgName] = $bundle->getUrl($geppettoFilename);
        }
    }
}