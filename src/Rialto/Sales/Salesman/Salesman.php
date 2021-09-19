<?php

namespace Rialto\Sales\Salesman;

use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A salesperson.
 *
 * @UniqueEntity(fields="id", message="A salesperson already exists with that ID.")
 */
class Salesman implements RialtoEntity
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=3,
     *   maxMessage="ID cannot be longer than {{ limit }} characters.")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=30,
     *  maxMessage="Name cannot be longer than {{ limit }} characters.")
     */
    private $name;

    private $SManTel;
    private $SManFax;
    private $CommissionRate1;
    private $Breakpoint;
    private $CommissionRate2;

    public function __construct($id)
    {
        $this->id = strtoupper(trim($id));
    }

    public function getId()
    {
        return $this->id;
    }

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

