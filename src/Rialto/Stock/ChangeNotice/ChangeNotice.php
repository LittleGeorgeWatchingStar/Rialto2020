<?php

namespace Rialto\Stock\ChangeNotice;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An engineering change notice (ECN) that records a change to a stock item.
 *
 * Also known as a product change notice (PCN).
 */
class ChangeNotice implements Persistable, RialtoEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var DateTime
     */
    private $dateCreated;

    /**
     * @var DateTime
     */
    private $effectiveDate;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $description;

    /** @var ChangeNoticeItem[] */
    private $items;

    /**
     * If published, the ID of the blog post; zero otherwise.
     * @var integer
     */
    private $postID = 0;

    /**
     * Indicates whether this notice should be published.
     * @var boolean
     */
    private $publish = false;

    public function __construct()
    {
        $this->dateCreated = new DateTime();
        $this->effectiveDate = new DateTime();
        $this->items = new ArrayCollection();
    }

    /**
     * Get ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return 'change notice '. $this->id;
    }

    public function addStockItem(StockItem $item)
    {
        if (! $this->hasItem($item) ) {
            $this->items[] = new ChangeNoticeItem($this, $item);
        }
    }

    public function getStockItems()
    {
        $array = $this->items->map(function(ChangeNoticeItem $item) {
            return $item->getStockItem();
        })->toArray();
        return array_unique($array);
    }

    public function addVersion(ItemVersion $version)
    {
        if (! $this->hasItem($version) ) {
            $link = new ChangeNoticeItem($this, $version->getStockItem());
            $link->setVersion($version);
            $this->items[] = $link;
        }
    }

    private function hasItem(Item $other)
    {
        foreach ( $this->items as $item) {
            if ( $item->matches($other) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get dateCreated
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return clone $this->dateCreated;
    }

    /**
     * Set effectiveDate
     *
     * @param DateTime $effectiveDate
     */
    public function setEffectiveDate(DateTime $effectiveDate = null)
    {
        if ( $effectiveDate ) {
            $this->effectiveDate = clone $effectiveDate;
        }
    }

    /**
     * Get effectiveDate
     *
     * @return DateTime
     */
    public function getEffectiveDate()
    {
        return clone $this->effectiveDate;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = trim($description);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getPostID()
    {
        return $this->postID;
    }

    public function setPostID($postID)
    {
        $this->postID = (int) $postID;
    }

    /**
     * Whether this notice has already been published.
     * @return boolean
     */
    public function isPublished()
    {
        return $this->postID > 0;
    }

    /**
     * Whether this notice should be published.
     * @return boolean
     */
    public function shouldBePublished()
    {
        return $this->publish;
    }

    /**
     * Alias of shouldBePublished() for form binding.
     * @return boolean
     */
    public function getPublish()
    {
        return $this->shouldBePublished();
    }

    /**
     * Whether this notice should be published.
     */
    public function setPublish($publish)
    {
        $this->publish = (bool) $publish;
    }


    public function getEntities()
    {
        return [$this];
    }
}
