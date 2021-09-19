<?php

namespace Rialto\Geppetto\Design;

use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @deprecated
 * use @see DesignRevision2
 *
 * A new revision of an existing Geppetto design.
 */
class DesignRevision extends DesignAbstract
{
    /** @var StockItem */
    private $board;

    /**
     * @var StockItem
     * @Assert\NotNull(message="Requested board has no PCB component.")
     */
    private $pcb;

    public function __construct(StockItem $board)
    {
        $this->board = $board;
        $this->pcb = $this->findPcb($board);
    }

    private function findPcb(StockItem $board)
    {
        foreach ($board->getAutoBuildVersion()->getBom() as $bomItem ) {
            /* @var $bomItem BomItem */
            if ( $bomItem->isCategory(StockCategory::PCB) ) {
                return $bomItem->getComponent();
            }
        }
        return null;
    }

    /** @Assert\Callback */
    public function validateBoard(ExecutionContextInterface $context)
    {
        if (! $this->board->isCategory(StockCategory::BOARD) ) {
            $context->addViolation("{$this->board} is not a board.");
        }
    }

    /**
     * @return StockItem
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @return StockItem
     */
    public function getPcb()
    {
        return $this->pcb;
    }

    /** @Assert\Callback */
    public function validateVersionCode(ExecutionContextInterface $context)
    {
        $this->validateVersionCodeForItem($this->board, $context);
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

    public function createPcbVersion()
    {
        $template = $this->createPcbVersionTemplate();
        $template->setStockItem($this->pcb);
        return $template->create();
    }

    public function createBoardVersion()
    {
        $template = $this->createBoardVersionTemplate();
        $template->setStockItem($this->board);
        return $template->create();
    }

}
