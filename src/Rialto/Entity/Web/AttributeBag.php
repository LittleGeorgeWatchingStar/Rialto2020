<?php

namespace Rialto\Entity\Web;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Database\Orm\DbManager;
use Rialto\Entity\EntityAttribute;
use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A collection of attributes for an entity.
 */
class AttributeBag
{
    private $entity;

    /**
     * @Assert\Valid(traverse=true)
     */
    private $attributes;
    private $removed = [];

    public function __construct(RialtoEntity $entity, array $attributes)
    {
        $this->entity = $entity;
        $this->attributes = new ArrayCollection($attributes);
    }

    public function getAttributes()
    {
        return $this->attributes->toArray();
    }

    public function addAttribute(EntityAttribute $attribute)
    {
        $attribute->setEntity($this->entity);
        $this->attributes[] = $attribute;
    }

    public function removeAttribute(EntityAttribute $attribute)
    {
        $this->attributes->removeElement($attribute);
        $this->removed[] = $attribute;
    }

    public function persist(DbManager $dbm)
    {
        foreach ( $this->attributes as $attr ) {
            $dbm->persist($attr);
        }
        foreach ( $this->removed as $rem ) {
            $dbm->remove($rem);
        }
    }

}
