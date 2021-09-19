<?php

namespace Rialto\Panelization;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gumstix\Geometry\Rectangle;
use Gumstix\Geometry\Vector2D;
use Gumstix\Storage\FileStorage;
use Rialto\IllegalStateException;
use Rialto\Panelization\Layout\Layout;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A collection of boards that are being built simultaneously on a single
 * PCB fab.
 *
 * @see https://en.wikipedia.org/wiki/Printed_circuit_board#Panelization
 */
class Panel
{
    /**
     * Default Panel dimensions in millimeters.
     */
    const PANEL_X_SIZE = 170.0;
    const PANEL_Y_SIZE = 270.0;

    const DEFAULT_MARGIN = 2.54;  // == 0.1 inch

    /** @var string|null */
    private $id;

    /**
     * Millimeters between boards.
     *
     * @var float
     *
     * @Assert\Range(min=0, minMessage="Margin cannot be less than {{ limit }}.")
     */
    private $margin = self::DEFAULT_MARGIN;

    /**
     * Panel width in millimeters.
     * @var float
     */
    private $width;

    /**
     * Panel height in millimeters.
     * @var float
     */
    private $height;

    /**
     * When the PCB vendor returns the panelized gerbers, the origin is
     * often not at the bottom-left of the panel.
     *
     * @var Vector2D
     */
    private $bottomLeft;

    /**
     * When the Manufacturer wants the origin in a different spot
     * (eg. the bottom-right corner).
     * NOTE: Datum is (0, 0), not {@see $bottomLeft}.
     *
     * @var Vector2D
     */
    private $outputOffset;

    /** @var Collection|PlacedBoard[] */
    private $boards;

    public function __construct($width=self::PANEL_X_SIZE,
                                $height=self::PANEL_Y_SIZE)

    {
        $this->width = $width;
        $this->height = $height;
        $this->bottomLeft = Vector2D::origin();
        $this->outputOffset = Vector2D::origin();
        $this->boards = new ArrayCollection();
    }

    /**
     * Factory method for repanelizing an existing PO.
     */
    public static function fromPurchaseOrder(PurchaseOrder $po,
                                             Layout $layout): self
    {
        $panel = new self();
        $boards = [];
        foreach ($po->getWorkOrders() as $wo) {
            for ($i = 0; $i < $wo->getBoardsPerPanel(); $i++) {
                $boards[] = new PlacedBoard($wo);
            }
        }
        $layout->placeBoards($panel, $boards);
        return $panel;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        foreach ($this->boards as $board) {
            // They should all be the same, so return the first one.
            return $board->getPurchaseOrder();
        }
        throw new IllegalStateException("Panel {$this->id} has no PO");
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * The X scaling factor to fit this panel on a sheet of A4 paper.
     */
    public function xScale(): float
    {
        return self::PANEL_X_SIZE / $this->width;
    }

    /**
     * @return float
     */
    public function getDefaultHeight()
    {
        return self::PANEL_X_SIZE;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * The Y scaling factor to fit this panel on a sheet of A4 paper.
     */
    public function yScale(): float
    {
        return self::PANEL_Y_SIZE / $this->height;
    }

    public function getBottomLeft(): Vector2D
    {
        return $this->bottomLeft;
    }

    public function setBottomLeft(Vector2D $bottomLeft)
    {
        $this->bottomLeft = $bottomLeft;
    }

    public function getOutputOffset(): Vector2D
    {
        return $this->outputOffset;
    }

    public function setOutputOffset(Vector2D $outputOffset)
    {
        $this->outputOffset = $outputOffset;
    }

    /**
     * @return float
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * @param float $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
        foreach ($this->boards as $board) {
            $board->setMargin($margin);
        }
    }

    public function addBoard(PlacedBoard $board, Pose $pos)
    {
        $panelIndex = count($this->boards);
        $board->setPanel($this, $panelIndex);
        $board->setPose($pos);
        $this->boards[] = $board;
    }

    public function removeBoardLocally(PlacedBoard $boardToRemove)
    {
        for ($i = 0; $i < count($this->boards); $i ++ ) {
            if ($this->boards[$i]->getId() == $boardToRemove->getId()) {
                unset($this->boards[$i]);
            }
        }
    }

    /**
     * @return PlacedBoard[]
     */
    public function getBoards(): array
    {
        return $this->boards->getValues();
    }

    public function isEmpty(): bool
    {
        return count($this->boards) == 0;
    }

    public function isOutOfBounds(PlacedBoard $board): bool
    {
        $boundingBox = $this->getBoundingBox();
        return ! $boundingBox->contains($board->getKeepout());
    }

    /**
     * @Assert\Callback
     */
    public function validateBoardPositions(ExecutionContextInterface $context)
    {
        $this->validateBoundaries($context);
        $this->validateOverlap($context);
    }

    private function validateBoundaries(ExecutionContextInterface $context)
    {
        foreach ($this->getBoards() as $board) {
            if ($this->isOutOfBounds($board)) {
                $context->addViolation("$board is too close to the edge.");
            }
        }
    }

    private function validateOverlap(ExecutionContextInterface $context)
    {
        // Ensure boards are numerically indexed.
        $boards = $this->getBoards();
        foreach ($boards as $i => $a) {
            foreach ($boards as $j => $b) {
                if ($j <= $i) {
                    continue;  // Avoid redundant error messages.
                } elseif ($a->overlaps($b)) {
                    $context->addViolation("$a and $b are too close.");
                }
            }
        }
    }

    public function getSize(): Vector2D
    {
        return new Vector2D($this->width, $this->height);
    }

    public function getBoundingBox(): Rectangle
    {
        return Rectangle::fromDimensions($this->bottomLeft, $this->getSize());
    }

    public function generateConsolidatedBom(): ConsolidatedBom
    {
        $bom = new ConsolidatedBom();
        foreach ($this->boards as $board) {
            $board->addComponentsToConsolidatedBom($bom);
        }
        return $bom;
    }

    public function generateConsolidatedXY(FileStorage $buildFilesStorage): ConsolidatedXY
    {
        $this->originToOffsetOutputCoordinates();
        $xy = new ConsolidatedXY();
        foreach ($this->boards as $board) {
            $board->addComponentsToConsolidatedXY($xy, $buildFilesStorage);
        }
        return $xy;
    }

    /**
     * If the bottom-left is not at the origin, this puts it there and
     * adjusts board positions accordingly.
     */
    public function normalizeCoordinates()
    {
        $this->translate($this->bottomLeft->scale(-1));
    }

    /**
     * Puts origin at output offset location.
     */
    public function originToOffsetOutputCoordinates()
    {
        $this->translate($this->outputOffset->scale(-1));
    }

    private function translate(Vector2D $delta)
    {
        $newBottomLeft = $this->bottomLeft->add($delta);
        $newOutputOffset = $this->outputOffset->add($delta);
        $this->setBottomLeft($newBottomLeft);
        $this->setOutputOffset($newOutputOffset);
        foreach ($this->boards as $board) {
            $board->translate($delta);
        }
    }
}
