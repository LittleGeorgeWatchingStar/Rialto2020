<?php

namespace Rialto\Stock\Item\Web;

use Rialto\Stock\Item\NewSku;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\ValidSku;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * For cloning an existing stock item into a new one.
 */
class StockItemClone
{
    /** @var StockItem */
    private $source;

    /**
     * @var string
     * @Assert\NotBlank
     * @ValidSku
     * @NewSku
     */
    public $stockCode;

    /**
     * @var string
     */
    private $initialVersion = '';

    /** @var ItemVersion */
    public $copyBomFrom;

    public function __construct(StockItem $source)
    {
        $this->source = $source;
        $this->copyBomFrom = $source->getAutoBuildVersion();
        if ($source->isVersioned()) {
            $this->initialVersion = '1';
        }
    }

    public function isVersioned()
    {
        return $this->source->isVersioned();
    }

    public function getVersions()
    {
        return $this->source->getActiveVersions();
    }

    public function getInitialVersion()
    {
        return $this->initialVersion;
    }

    public function setInitialVersion($version)
    {
        $this->initialVersion = trim($version);
    }

    public function validateInitialVersion(ExecutionContextInterface $context)
    {
        if ($this->isVersioned() && $this->initialVersion == '') {
            $context->buildViolation("Initial version cannot be blank.")
                ->atPath('initialVersion')
                ->addViolation();
        }
    }

    public function validateBomSource(ExecutionContextInterface $context)
    {
        if ($this->source->hasSubcomponents() && (! $this->copyBomFrom)) {
            $context->buildViolation("Choose a version from which to copy the BOM.")
                ->atPath('copyBomFrom')
                ->addViolation();
        }
    }

    /** @return StockItem */
    public function createClone()
    {
        $newItem = $this->source->copy($this->stockCode);
        $version = $newItem->addVersion($this->initialVersion);
        $version->copyFrom($this->copyBomFrom);
        $newItem->setAutoBuildVersion($version);
        $newItem->setShippingVersion($version);
        return $newItem;
    }

}
