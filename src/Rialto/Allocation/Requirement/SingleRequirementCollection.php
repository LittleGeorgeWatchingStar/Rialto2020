<?php

namespace Rialto\Allocation\Requirement;


use Rialto\Manufacturing\Customization\Customization;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * A trivial implementation of RequirementCollection that allows you to easily
 * allocate for a single requirement.
 */
class SingleRequirementCollection implements RequirementCollection
{
    /** @var Requirement */
    private $requirement;

    /** @var bool */
    private $shareBins = false;

    public function __construct(Requirement $req)
    {
        $this->requirement = $req;
    }

    /**
     * @return Requirement
     */
    public function getRequirement()
    {
        return $this->requirement;
    }

    /**
     * @return Requirement[] The requirements in this collection.
     */
    public function getRequirements()
    {
        return [$this->requirement];
    }

    public function getSku()
    {
        return $this->requirement->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return PhysicalStockItem */
    public function getStockItem()
    {
        return $this->requirement->getStockItem();
    }

    /**
     * @deprecated use getFullSku() instead.
     */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return string The full SKU, including revision and customization
     *   codes; eg "GS3503F-R1234-C10085"
     */
    public function getFullSku()
    {
        return $this->requirement->getFullSku();
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->requirement->getCustomization();
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->requirement->getVersion();
    }

    /**
     * @return boolean
     */
    public function isShareBins()
    {
        return $this->shareBins;
    }

    /**
     * @param boolean $shareBins
     */
    public function setShareBins($shareBins)
    {
        $this->shareBins = (bool) $shareBins;
    }

    /**
     * @return Facility The location where stock is required.
     */
    public function getFacility()
    {
        return $this->requirement->getFacility();
    }

    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }
}
