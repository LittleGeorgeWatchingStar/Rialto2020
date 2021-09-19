<?php

namespace Rialto\Entity;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * Allows us to store arbitrary values associated with an entity.
 */
abstract class EntityAttribute implements RialtoEntity
{
    /**
     * @var string
     * @Assert\NotBlank(message="Attribute cannot be blank.")
     */
    private $attribute;

    /**
     * @var string
     * @Assert\Length(max=255, maxMessage="Value cannot be longer than {{ limit }} characters.")
     */
    private $value = '';

    /**
     * Get attribute
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param string $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $this->normalize($attribute);
    }

    /**
     * @param string $attribute
     * @return string The normalized attribute.
     */
    public static function normalize($attribute)
    {
        return strtolower(trim($attribute));
    }

    public function isAttribute($attribute)
    {
        return $this->normalize($attribute) === $this->attribute;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = trim($value);
    }

    public abstract function setEntity(RialtoEntity $entity);
}
