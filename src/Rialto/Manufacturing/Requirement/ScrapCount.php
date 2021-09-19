<?php

namespace Rialto\Manufacturing\Requirement;

use Rialto\Sales\Order\Allocation\Requirement;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Indicates how many units of a part should be written off as lost during
 * manufacturing of a work order.
 *
 * @see Requirement
 *
 * @UniqueEntity(fields={"package"})
 */
class ScrapCount
{
    /** @var int */
    private $id;

    /**
     * The scrap count depends on the StockItem's package, rather than
     * the item itself.
     *
     * Small items tend to lose more units per build than larger ones.
     *
     * @var string
     *
     * @Assert\NotBlank(message="Package is required.")
     */
    private $package;

    /**
     * The default quantity to write off per build.
     *
     * This can be overridden on a per-Requirement basis.
     *
     * @var float
     *
     * @Assert\Range(min=1)
     */
    private $scrapCount;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $package
     */
    public function setPackage($package)
    {
        $this->package = trim($package);
    }

    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param float $scrapCount
     */
    public function setScrapCount($scrapCount)
    {
        $this->scrapCount = $scrapCount;
    }

    /**
     * @return float
     */
    public function getScrapCount()
    {
        return $this->scrapCount;
    }
}

