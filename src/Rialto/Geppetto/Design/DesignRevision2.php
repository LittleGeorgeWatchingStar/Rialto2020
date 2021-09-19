<?php

namespace Rialto\Geppetto\Design;

use Rialto\Geppetto\Module\Module;
use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Measurement\Dimensions;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a Geppetto design revision.
 */
class DesignRevision2
{
    const PCB_WEIGHT = 0.0004; // The weight of a PCB, in kg, per square cm of area.

    /**
     * @var StockItem|null
     */
    private $board;

    /**
     * @var StockItem|null
     */
    private $pcb;

    /**
     * @var string
     * @Assert\NotBlank(message="Design name is required.")
     * @Assert\Length(max=255,
     *   maxMessage="Design name cannot be longer than {{ limit }} characters.")
     */
    private $designName = '';

    /**
     * @var string
     * @Assert\NotBlank(message="Design description is required.")
     */
    private $designDescription = '';

    /**
     * @var string|null
     * @Assert\Url(message="Invalid permalink URL '{{ value }}'.")
     */
    private $designPermalink = null;

    /**
     * @var string
     * @Assert\NotBlank(message="Please provide a version code.")
     */
    private $versionCode = '';

    /* @var Dimensions
     * @Assert\NotNull(message="Valid PCB dimensions are required.")
     */
    private $pcbDimensions = null;

    /**
     * @var Dimensions
     * @Assert\NotNull(message="Valid board dimensions are required.")
     */
    private $boardDimensions = null;

    /**
     * @var Module[]
     * @Assert\Count(min=1, minMessage="At least one module is required.")
     * @Assert\Valid
     */
    private $modules = [];

    /**
     * @var float|null
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0.01, minMessage="Price must be at least {{ limit }}.")
     */
    private $price;

    /**
     * @var boolean
     */
    private $designPublic = false;

    /**
     * @var string
     * @Assert\NotBlank(message="Owner identifier is required.")
     */
    private $designOwnerIdentifier = '';

    /**
     * @var string
     * @Assert\NotBlank(message="Image URL is required.")
     * @Assert\Url(message="Invalid image URL '{{ value }}'.")
     */
    private $imageUrl = '';

    public function getBoard(): ?StockItem
    {
        return $this->board;
    }

    public function setBoard(?StockItem $board): void
    {
        $this->board = $board;
        $this->pcb = null;
        if ($this->board) {
            foreach ($this->board->getAutoBuildVersion()->getBom() as $bomItem ) {
                /* @var $bomItem BomItem */
                if( $bomItem->isCategory(StockCategory::PCB) ) {
                    $this->pcb = $bomItem->getComponent();
                    break;
                }
            }
        }
    }

    /**
     * @Assert\Callback()
     */
    public function validateBoard(ExecutionContextInterface $context)
    {
        if ($this->board && !$this->board->isCategory(StockCategory::BOARD) ) {
            $context->addViolation("{$this->board} is not a board.");
        }
    }

    public function getPcb(): ?StockItem
    {
        return $this->pcb;
    }

    public function getDesignName(): string
    {
        return $this->designName;
    }

    public function setDesignName(string $designName): void
    {
        $this->designName = $designName;
    }

    public function getDesignDescription(): string
    {
        return $this->designDescription;
    }

    public function setDesignDescription(string $designDescription): void
    {
        $this->designDescription = $designDescription;
    }

    public function getDesignPermalink(): ?string
    {
        return $this->designPermalink;
    }

    public function setDesignPermalink(string $designPermalink): void
    {
        $this->designPermalink = $designPermalink;
    }

    public function getVersionCode(): string
    {
        return $this->versionCode;
    }

    public function setVersionCode(string $versionCode): void
    {
        $this->versionCode = $versionCode;
    }

    /**
     * @Assert\Callback()
     */
    public function validateVersionCode(ExecutionContextInterface $context)
    {
        if ($this->board) {
            $this->validateVersionCodeForItem($this->board, $context);
        }
        if ( $this->pcb ) {
            $this->validateVersionCodeForItem($this->pcb, $context);
        }
    }

    private function validateVersionCodeForItem(
        StockItem $item,
        ExecutionContextInterface $context)
    {
        $code = $this->getVersionCode();
        if ( $item->hasVersion($code) ) {
            $context->buildViolation("Version $code already exists for $item")
                ->atPath('versionCode')
                ->addViolation();
        }
    }

    public function getPcbDimensions(): ?Dimensions
    {
        return $this->pcbDimensions;
    }

    public function setPcbDimensions(Dimensions $pcbDimensions): void
    {
        $this->pcbDimensions = $pcbDimensions;
    }

    public function getBoardDimensions(): ?Dimensions
    {
        return $this->boardDimensions;
    }

    public function setBoardDimensions(Dimensions $boardDimensions): void
    {
        $this->boardDimensions = $boardDimensions;
    }

    /**
     * @return Module[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @param Module[] $modules
     */
    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getPcbWeight(): float
    {
        $dims = $this->pcbDimensions->toArray();
        rsort($dims, SORT_NUMERIC);
        $area = array_shift($dims) * array_shift($dims);
        return $area * self::PCB_WEIGHT;
    }

    public function isDesignPublic(): bool
    {
        return $this->designPublic;
    }

    public function setDesignPublic(bool $designPublic): void
    {
        $this->designPublic = $designPublic;
    }

    public function getDesignOwnerIdentifier(): string
    {
        return $this->designOwnerIdentifier;
    }

    public function setDesignOwnerIdentifier(string $ownerIdentifier): void
    {
        $this->designOwnerIdentifier = $ownerIdentifier;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }
}