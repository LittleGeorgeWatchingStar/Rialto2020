<?php

namespace Rialto\Stock\Bin;

use Rialto\Database\Orm\DbManager;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a type of container that a StockBin can be stored in.
 *
 * @UniqueEntity(fields={"id"}, message="That ID already exists.")
 * @UniqueEntity(fields={"name"}, message="That name is already in use.")
 */
class BinStyle
implements RialtoEntity
{
    const DEFAULT_STYLE = 'bin';

    /** @return BinStyle */
    public static function fetchDefault(DbManager $dbm)
    {
        return $dbm->need(BinStyle::class, self::DEFAULT_STYLE);
    }

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     * @Assert\Regex(pattern="/^[a-zA-Z0-9]+$/",
     *     message="ID can only contain letters and numbers.")
     */
    private $id;

    /**
     * @var string
     * @Assert\Length(max=20)
     */
    private $name = null;

    /**
     * The number of copies to print when printing bin labels for a bin
     * of this style.
     *
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    private $numLabels = 1;

    /**
     * Factory method -- useful for unit tests.
     *
     * @param string $id
     * @return BinStyle
     */
    public static function withId($id = self::DEFAULT_STYLE)
    {
        $style = new self();
        $style->setId($id);
        return $style;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        assertion(!$this->id); // Can only be initialized, not changed.
        $this->id = $this->normalizeId($id);
    }

    private function normalizeId($id)
    {
        return strtolower(trim($id));
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function equals($other)
    {
        if ($other instanceof BinStyle) {
            $other = $other->getId();
        }
        return $this->normalizeId($other) === $this->id;
    }

    public function __toString()
    {
        return $this->name ?: $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name) ?: null;
    }

    /**
     * The general category of bin; eg "box", "reel", "tube".
     *
     * @return string
     */
    public function getCategory()
    {
        $string = mb_strtolower("{$this->name} {$this->id}");
        $string = preg_replace('/[^a-z ]/', ' ', $string);
        $parts = explode(' ', $string);
        $parts = array_filter($parts);
        $lastPart = array_shift($parts);
        return $lastPart ?: 'bin';
    }

    /**
     * @param integer $numLabels
     */
    public function setNumLabels($numLabels)
    {
        $this->numLabels = $numLabels;
    }

    /**
     * @return integer
     */
    public function getNumLabels()
    {
        return $this->numLabels;
    }
}
