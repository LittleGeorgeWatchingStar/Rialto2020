<?php

namespace Rialto\Tax\Regime;

use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * TaxRegime
 */
class TaxRegime implements RialtoEntity, Persistable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $county = '';

    /**
     * @var string
     */
    private $city = '';

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var string
     */
    private $acronym = '';

    /**
     * @var string
     */
    private $regimeCode = '';

    /**
     * @var float
     * @Assert\Range(min=0.000001)
     */
    private $taxRate;

    /**
     * @var \DateTime
     * @Assert\NotNull
     * @Assert\Date
     */
    private $startDate;

    /**
     * @var \DateTime
     * @Assert\Date
     */
    private $endDate = null;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set county
     *
     * @param string $county
     * @return TaxRegime
     */
    public function setCounty($county)
    {
        $this->county = trim(strtolower($county));

        return $this;
    }

    /**
     * Get county
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return TaxRegime
     */
    public function setCity($city)
    {
        $this->city = trim(strtolower($city));

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return TaxRegime
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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

    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * Set acronym
     *
     * @param string $acronym
     * @return TaxRegime
     */
    public function setAcronym($acronym)
    {
        $this->acronym = trim(strtoupper($acronym));

        return $this;
    }

    /**
     * Get acronym
     *
     * @return string
     */
    public function getAcronym()
    {
        return $this->acronym;
    }

    /**
     * Set regimeCode
     *
     * @param string $regimeCode
     * @return TaxRegime
     */
    public function setRegimeCode($regimeCode)
    {
        $code = trim($regimeCode);
        if ( $code != '' ) $code = str_pad($code, 3, '0', STR_PAD_LEFT);
        $this->regimeCode = $code;

        return $this;
    }

    /**
     * Get regimeCode
     *
     * @return string
     */
    public function getRegimeCode()
    {
        return $this->regimeCode;
    }

    /**
     * Set taxRate
     *
     * @param float $taxRate
     * @return TaxRegime
     */
    public function setTaxRate($taxRate)
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    /**
     * Get taxRate
     *
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return TaxRegime
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return TaxRegime
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getEntities()
    {
        return [$this];
    }
}
