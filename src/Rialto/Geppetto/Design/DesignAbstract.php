<?php

namespace Rialto\Geppetto\Design;

use Rialto\Geppetto\Module\Module;
use Rialto\Measurement\Dimensions;
use Rialto\Stock\Item\Version\ItemVersionTemplate;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @deprecated
 * use @see DesignRevision2
 *
 * Base class for Design and DesignRevision.
 *
 * Includes fields required when creating either a new design or
 * adding a design revision.
 */
abstract class DesignAbstract
{
    /**
     * @var string
     * @Assert\NotBlank(message="Please provide a version code.")
     */
    private $versionCode;

    /**
     * @var Dimensions
     * @Assert\NotNull(message="Valid PCB dimensions are required.")
     */
    private $pcbDimensions;


    /**
     * @var Dimensions
     * @Assert\NotNull(message="Valid board dimensions are required.")
     */
    private $boardDimensions;

    /**
     * @var Module[]
     * @Assert\Count(min=1, minMessage="At least one module is required.")
     * @Assert\Valid
     */
    private $modules = [];

    /**
     * The weight of a PCB, in kg, per square cm of area.
     * @var float
     */
    private $pcbWeight = 0.0004;

    /**
     * @var float
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0.01, minMessage="Price must be at least {{ limit }}.")
     */
    public $price;

    public function getVersionCode()
    {
        return $this->versionCode;
    }

    public function setVersionCode($version)
    {
        $this->versionCode = trim($version);
    }

    public function getPcbDimensions()
    {
        return $this->pcbDimensions;
    }

    public function setPcbDimensions(Dimensions $pcbDimensions)
    {
        $this->pcbDimensions = $pcbDimensions;
    }

    public function getBoardDimensions()
    {
        return $this->boardDimensions;
    }

    public function setBoardDimensions(Dimensions $boardDimensions)
    {
        $this->boardDimensions = $boardDimensions;
    }

    /** @return Module[] */
    public function getModules()
    {
        return $this->modules;
    }

    public function addModule(Module $module)
    {
        $this->modules[$module->getSku()] = $module;
    }

    public function removeModule(Module $module)
    {
        unset($this->modules[$module->getSku()]);
    }

    protected function createPcbVersionTemplate()
    {
        $vt = new ItemVersionTemplate();
        $vt->setVersionCode($this->versionCode);
        $vt->setDimensions($this->pcbDimensions);
        $vt->setWeight($this->calculatePcbWeight());
        $vt->setAutoBuildVersion(true);
        $vt->setShippingVersion(true);
        return $vt;
    }

    private function calculatePcbWeight()
    {
        $dims = $this->pcbDimensions->toArray();
        rsort($dims, SORT_NUMERIC);
        $area = array_shift($dims) * array_shift($dims);
        return $area * $this->pcbWeight;
    }

    protected function createBoardVersionTemplate()
    {
        $vt = new ItemVersionTemplate();
        $vt->setVersionCode($this->versionCode);
        $vt->setDimensions($this->boardDimensions);
        $vt->setAutoBuildVersion(true);
        $vt->setShippingVersion(true);
        return $vt;
    }

    protected function createCadVersionTemplate()
    {
        $vt = new ItemVersionTemplate();
        $vt->setVersionCode($this->versionCode);
        $vt->setDimensions(new Dimensions(0, 0, 0));
//        $vt->setAutoBuildVersion(true); // TODO: confirm this.
//        $vt->setShippingVersion(true); // TODO: confirm this.
        return $vt;
    }
}
