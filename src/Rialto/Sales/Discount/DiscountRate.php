<?php

namespace Rialto\Sales\Discount;

use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;

class DiscountRate implements RialtoEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var DiscountGroup
     * @Assert\NotNull(groups={"Default", "discounts"})
     */
    private $discountGroup;

    /**
     * @var int
     * @Assert\Type(type="numeric", groups={"Default", "discounts"})
     * @Assert\Range(min=0, groups={"Default", "discounts"})
     */
    private $threshold;

    /**
     * @var float
     * @Assert\Type(type="numeric", groups={"Default", "discounts"})
     * @Assert\Range(min=0.0, max=1.0, groups={"Default", "discounts"})
     */
    private $discountRate;

    /**
     * @var float
     * @Assert\Type(type="numeric", groups={"Default", "discounts"})
     * @Assert\Range(min=0.0, max=1.0, groups={"Default", "discounts"})
     */
    private $discountRateRelated;



    public function getId()
    {
        return $this->id;
    }

    public function setDiscountGroup(DiscountGroup $group)
    {
        $this->discountGroup = $group;
    }

    public function getThreshold()
    {
        return $this->threshold;
    }

    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }

    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    public function setDiscountRate($discountRate)
    {
        $this->discountRate = $discountRate;
    }

    public function getDiscountRateRelated()
    {
        return $this->discountRateRelated;
    }

    public function setDiscountRateRelated($discountRateRelated)
    {
        $this->discountRateRelated = $discountRateRelated;
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
