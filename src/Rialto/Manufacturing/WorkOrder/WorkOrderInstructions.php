<?php

namespace Rialto\Manufacturing\WorkOrder;

use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Stock\Item;

/**
 * Controls the creation of the work order build instructions documents.
 */
class WorkOrderInstructions
{
    /** @var WorkOrder */
    private $wo;

    /** @var Requirement[] */
    private $requirements;

    private $refreshRequirements = false;

    private $customInstructions = '';

    public function __construct(WorkOrder $wo)
    {
        $this->wo = $wo;
        $this->requirements = $this->wo->getRequirements();
    }

    /** @return Requirement[] */
    public function getKeyComponents(): array
    {
        return array_filter([
            $this->getFabComponent(),
            $this->getMemoryComponent(),
            $this->getBoardComponent(),
        ]);
    }

    /**
     * True if the item being built is a flashed memory component.
     */
    public function isFlashBuild(): bool
    {
        $item = $this->wo->getStockItem();
        return $this->getBuildType($item) == "COD";
    }

    /**
     * Returns the build type code (eg, PCB, COD, KIT, etc) of the given item.
     *
     * @todo We should really not embed "meaning" into the SKU
     * other than as a mnemonic for human benefit; yet that is precisely
     * what this method does. Is there a better way?
     */
    private function getBuildType(Item $item): string
    {
        $sku = $item->getSku();
        $type = substr($sku, 0, 2);
        switch ($type) {
            case "GS":
                return 'BRD';
            case "WS":
            case "NS":
                return 'ASM';
            case "PF":
                return 'PCB';
            default:
                $type = substr($sku, 0, 3);
                switch ($type) {
                    case "BRD":
                    case "COD":
                    case "PCB":
                    case "ASM":
                        return $type;
                    case "ICM":
                        return 'COD';
                    case "GUM":
                    case "PKG":
                    case "KIT":
                        return 'ASM';
                }
        }
        return '';
    }

    /** @return Requirement|null */
    public function getFabComponent()
    {
        foreach ($this->requirements as $woReq) {
            $component = $woReq->getStockItem();
            if (!$component->isVersioned()) {
                continue;
            }
            switch ($this->getBuildType($component)) {
                case "PCB":
                case "BRD":
                case "ASM":
                    if ($component->isPCB()) {
                        return $woReq;
                    }
            }
        }
        return null;
    }

    /** @return Requirement|null */
    public function getMemoryComponent()
    {
        foreach ($this->requirements as $woReq) {
            $component = $woReq->getStockItem();
            if (!$component->isVersioned()) {
                continue;
            }
            switch ($this->getBuildType($component)) {
                case "COD":
                    return $woReq;
            }
        }
        return null;
    }

    /**
     * The blank memory part for a flash build.
     *
     * A flash build is one where a programming house is flashing software onto
     * a blank memory component.
     *
     * @return Requirement|null
     */
    public function getBlankMemoryComponent()
    {
        foreach ($this->requirements as $woReq) {
            $component = $woReq->getStockItem();
            if ($component->isVersioned()) {
                continue;
            }
            switch ($this->getBuildType($component)) {
                case "COD":
                    return $woReq;
            }
        }
        return null;
    }

    /** @return Requirement|null */
    public function getBoardComponent()
    {
        foreach ($this->requirements as $woReq) {
            $component = $woReq->getStockItem();
            if (!$component->isVersioned()) {
                continue;
            }
            switch ($this->getBuildType($component)) {
                case "BRD":
                    if ($component->isBoard()) {
                        return $woReq;
                    }
            }
        }
        return null;
    }

    public function getWorkOrder(): WorkOrder
    {
        return $this->wo;
    }

    /**
     * Assembles a short summary of the instructions, which is usually
     * written to the work order's Instructions field or the
     * purchase order's version field.
     */
    public function getVersionSummary(): string
    {
        $parent_item = $this->wo->getStockItem();
        $build_type = $this->getBuildType($parent_item);
        $has_instructions = ($build_type == 'ASM') ||
            ($build_type == 'BRD') ||
            ($build_type == 'COD');
        if (!$has_instructions) {
            return '';
        }
        $ins = sprintf(
            'Board version: %s-R%s',
            $parent_item->getId(), $this->wo->getVersion()
        );

        $fabComponent = $this->getFabComponent();
        if ($fabComponent) {
            $fabVersion = $fabComponent->getVersion();
            if ($fabVersion) {
                $ins .= sprintf(
                    ',Fab version: %s-R%s',
                    $fabComponent->getSku(), $fabVersion
                );
            }
        }

        $memComponent = $this->getMemoryComponent();
        if ($memComponent) {
            $memVersion = $memComponent->getVersion();
            if ($memVersion) {
                $ins .= sprintf(',Flash label: %s', $memVersion);
            }
        }
        if ($this->wo->hasCustomizations()) {
            $cstm = $this->wo->getCustomization();
            $ins .= sprintf(',Customization ID %s', $cstm->getId());
        }
        return $ins;
    }

    public function isRefreshRequirements(): bool
    {
        return $this->refreshRequirements;
    }

    public function setRefreshRequirements($refresh)
    {
        $this->refreshRequirements = (bool) $refresh;
    }

    public function getCustomInstructions(): string
    {
        return $this->customInstructions;
    }

    public function setCustomInstructions($instructions)
    {
        $this->customInstructions = trim($instructions);
    }

    public function isBoard(): bool
    {
        return $this->wo->getStockItem()->isBoard();
    }
}
