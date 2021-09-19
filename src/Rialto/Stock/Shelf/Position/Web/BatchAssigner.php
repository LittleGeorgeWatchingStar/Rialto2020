<?php

namespace Rialto\Stock\Shelf\Position\Web;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\Label\BinLabelPrintQueue;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Shelf\Position\PositionAssigner;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BatchAssigner
{
    const ASSIGN = 'assign';

    const UNASSIGN = 'unassign';

    const SET_BIN_STYLE = 'style';

    /**
     * @var StockBin[]
     * @Assert\Count(min=1, minMessage="Please select at least one bin.")
     */
    private $selected;

    /**
     * Those bins that were actually modified by the most recent action.
     *
     * @var StockBin[]
     */
    private $updated = [];

    /**
     * @var string
     * @Assert\Choice(callback="getValidActions", strict=true)
     */
    private $action;

    /** @var BinStyle|null */
    public $binStyle = null;

    private $printLabels = true;

    /**
     * Storing the session allows the assigner to remember certain values,
     * such as whether to print labels.
     *
     * @var SessionInterface
     */
    private $session = null;

    public function __construct()
    {
        $this->selected = new ArrayCollection();
    }

    /**
     * @param SessionInterface $session
     */
    public function setSession($session)
    {
        $this->session = $session;
        $this->printLabels = $session->get('shelf.assign.labels', $this->printLabels);
    }

    public static function getValidActions()
    {
        return [self::ASSIGN, self::UNASSIGN, self::SET_BIN_STYLE];
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return StockBin[]
     */
    public function getSelected()
    {
        return $this->selected->getValues();
    }

    public function addSelected(StockBin $bin)
    {
        $this->selected[] = $bin;
    }

    public function getNumSelected()
    {
        return count($this->selected);
    }

    public function removeSelected(StockBin $bin)
    {
        $this->selected->removeElement($bin);
    }

    public function updateSelected(PositionAssigner $assigner)
    {
        switch ($this->action) {
            case self::ASSIGN:
                $this->updated = $assigner->assignPositions($this->getSelected());
                break;
            case self::UNASSIGN:
                $this->updated = $assigner->unassignPositions($this->getSelected());
                break;
            case self::SET_BIN_STYLE:
                $this->updated = $this->updateBinStyle();
                break;
        }
    }

    public function getNumUpdated()
    {
        return count($this->updated);
    }

    /**
     * @return bool
     */
    public function isPrintLabels()
    {
        return $this->printLabels;
    }

    /**
     * @param bool $print
     */
    public function setPrintLabels($print)
    {
        $this->printLabels = $print;
        if ($this->session) {
            $this->session->set('shelf.assign.labels', $print);
        }
    }

    public function printLabelsIfNeeded(BinLabelPrintQueue $queue)
    {
        if ($this->shouldPrintLabels()) {
            $queue->printLabels($this->updated);
        }
    }

    private function shouldPrintLabels()
    {
        return $this->printLabels && in_array($this->action, [
                self::ASSIGN, self::UNASSIGN,
            ]);
    }

    /**
     * @Assert\Callback
     */
    public function validateBinStyle(ExecutionContextInterface $context)
    {
        if ($this->isAction(self::SET_BIN_STYLE) && !$this->binStyle) {
            $context->buildViolation("Select a bin style.")
                ->atPath('binStyle')
                ->addViolation();
        }
    }

    private function isAction($action)
    {
        return $action == $this->action;
    }

    /**
     * @return StockBin[]  Those bins that were actually modified.
     */
    private function updateBinStyle()
    {
        assertion(null !== $this->binStyle);
        $updated = [];
        foreach ($this->selected as $bin) {
            if (!$bin->isBinStyle($this->binStyle)) {
                $bin->setBinStyle($this->binStyle);
                $updated[] = $bin;
            }
        }
        return $updated;
    }
}
