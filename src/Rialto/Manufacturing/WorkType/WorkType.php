<?php

namespace Rialto\Manufacturing\WorkType;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Indicates the type of work needed to manufacture a BOM item into
 * the final product.
 *
 * Examples are:
 *  "smt": Surface-mount technology for electrical components
 *  "flash": Flash software onto a memory device
 *  "package": Manually package a product
 *
 * @UniqueEntity(fields={"id"})
 * @UniqueEntity(fields={"name"})
 */
class WorkType implements RialtoEntity
{
    const SMT = 'smt';
    const THROUGH_HOLE = 'through-hole';
    const PACKAGE = 'package';
    const PRINTING = 'print';
    const REWORK = 'rework';

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $name;

    public function __construct($id)
    {
        $this->id = strtolower(trim($id));
    }

    /** @return WorkType|object */
    public static function fetchPackage(ObjectManager $om): WorkType
    {
        return $om->find(WorkType::class, self::PACKAGE);
    }

    /** @return WorkType|object */
    public static function fetchSmt(ObjectManager $om): WorkType
    {
        return $om->find(WorkType::class, self::SMT);
    }

    public static function fetchThroughHole(ObjectManager $om): WorkType
    {
        return $om->find(WorkType::class, self::THROUGH_HOLE);
    }

    /** @return WorkType|object */
    public static function fetchRework(ObjectManager $om): WorkType
    {
        return $om->find(WorkType::class, self::REWORK);
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /** @return bool */
    public function equals(WorkType $other = null)
    {
        return $other && ($this->id == $other->id);
    }

    /** @return bool */
    public function isType($type)
    {
        return $type == $this->id;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->name;
    }
}
