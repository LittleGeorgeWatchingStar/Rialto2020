<?php

namespace Rialto\Stock\Publication;

use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A document that provides specifications or other information about
 * a stock item.
 */
abstract class Publication implements RialtoEntity
{
    /**
     * A publication that the public can download.
     */
    const PURPOSE_PUBLIC = 'public';

    /**
     * A publication that is for internal use.
     */
    const PURPOSE_INTERNAL = 'internal';

    /**
     * A publication that should be included in the build instructions.
     */
    const PURPOSE_BUILD = 'build';

    /**
     * A publication that should be printed and shipped to the customer
     * when the product is sold.
     */
    const PURPOSE_SHIP = 'ship';

    /**
     * @var integer
     */
    private $id;

    /**
     * The item to which this publication refers.
     * @var StockItem
     */
    private $stockItem;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max = "50")
     */
    private $description = '';

    /**
     * The URL or filename of the publication document.
     * @var string
     * @Assert\Length(max = "255")
     */
    protected $content = '';

    /**
     * Indicates the purpose of this publication.
     */
    protected $purpose = self::PURPOSE_PUBLIC;

    public function __construct(StockItem $stockItem)
    {
        $this->stockItem = $stockItem;
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    /**
     * @deprecated
     */
    public function getStockCode()
    {
        return $this->getSku();
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    public function setDescription($description): self
    {
        $this->description = trim($description);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->description;
    }

    /**
     * True if this publication is a URL; false if it is an uploaded file.
     * @return boolean
     */
    public function isUrl()
    {
        return $this instanceof UrlPublication;
    }

    /**
     * True if this publication is an uploaded file; false if it is a URL.
     * @return boolean
     */
    public function isFile()
    {
        return $this instanceof UploadPublication;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getPurpose()
    {
        return $this->purpose;
    }

    public function isBuild()
    {
        return self::PURPOSE_BUILD == $this->purpose;
    }

    public function isPublic(): bool
    {
        return self::PURPOSE_PUBLIC == $this->purpose;
    }
}
