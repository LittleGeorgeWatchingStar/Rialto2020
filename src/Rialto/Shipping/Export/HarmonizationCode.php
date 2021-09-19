<?php

namespace Rialto\Shipping\Export;

use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A code that classifies a product according to the Harmonized System
 * for tariff and export purposes.
 *
 * @see http://en.wikipedia.org/wiki/Harmonized_System
 */
class HarmonizationCode implements RialtoEntity
{
    /**
     * @var string
     * @Assert\Length(min=8, max=10,
     *   minMessage="Code must be exactly {{ limit }} characters long.",
     *   maxMessage="Code must be exactly {{ limit }} characters long.")
     * @Assert\Regex(pattern="/^[0-9]*$/", message="Invalid harmonization code.")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Name cannot be blank.")
     * @Assert\Length(max=255, maxMessage="Name is too long.")
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank(message="Description cannot be blank.")
     * @Assert\Length(max=255, maxMessage="Description is too long.")
     */
    private $description;

    /**
     * @var boolean
     */
    private $active = true;

    public function __construct($id)
    {
        $this->id = trim($id);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /** @return string */
    public function getLabel()
    {
        return sprintf('%s - %s', $this->id, $this->name);
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = trim($description);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }
}
