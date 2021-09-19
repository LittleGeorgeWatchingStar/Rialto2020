<?php

namespace Rialto\Purchasing\Manufacturer;

use DateTime;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\User\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A manufacturer of a part that we buy.
 *
 * @UniqueEntity(fields={"name"}, message="A manufacturer with that name already exists.")
 * @UniqueEntity(fields={"supplier"}, message="A manufacturer is already associated with that supplier.")
 */
class Manufacturer implements RialtoEntity
{
    /**
     * No policy on conflict minerals found
     */
    const POLICY_NONE = 'N/A';

    /**
     * There is a policy but not detailed data and not affirmative exclusion
     * on conflict minerals
     */
    const POLICY_GENERAL = 'General';

    /**
     * The manufacturer has taken an affirmative stance that there will be
     * no conflict minerals
     */
    const POLICY_AFFIRMATIVE = 'Affirmative';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $name = '';

    /**
     * If this manufacturer sells their own parts, then set this to the
     * corresponding supplier.
     *
     * @var Supplier|null
     */
    private $supplier = null;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var string
     * @Assert\Url
     * @Assert\Length(max=255)
     */
    private $conflictUrl = '';

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="50M")
     */
    private $conflictFile;

    /**
     * @var string
     */
    private $conflictFilename = '';

    /**
     * Whether or not the conflict information contains smelter data.
     * @var boolean
     */
    private $smelterData = false;

    /**
     * This manufacturer's policy on conflict minerals.
     * @var string
     * @Assert\Choice(callback = "getValidPolicies", strict=true)
     */
    private $policy = self::POLICY_NONE;

    /**
     * When this record was lasted updated.
     * @var DateTime|null
     */
    private $dateUpdated;

    /**
     * The user who last updated this record.
     * @var User|null
     */
    private $updatedBy;

    /**
     * @var UploadedFile|null
     * @Assert\File(maxSize="50M")
     */
    private $logoFile;

    /** @var string */
    private $logoFilename = '';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function equals(Manufacturer $other = null)
    {
        return $other && $this->id == $other->id;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Supplier|null
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier = null)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = trim($notes) ?: null;
    }

    public function setConflictUrl(string $conflictUrl): self
    {
        $this->conflictUrl = trim($conflictUrl);
        return $this;
    }

    /**
     * @return string
     */
    public function getConflictUrl()
    {
        return $this->conflictUrl;
    }

    public function getConflictFile()
    {
        return $this->conflictFile;
    }

    public function setConflictFile(UploadedFile $file = null)
    {
        $this->conflictFile = $file;
    }

    public function hasConflictFile()
    {
        return (bool) $this->conflictFilename;
    }

    public function setConflictFilename(string $filename): self
    {
        $this->conflictFilename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getConflictFilename()
    {
        return $this->conflictFilename;
    }

    public function hasConflictDocument()
    {
        return $this->hasConflictFile() || $this->conflictUrl;
    }

    /**
     * @return UploadedFile|null
     */
    public function getLogoFile()
    {
        return $this->logoFile;
    }

    public function setLogoFile(UploadedFile $file = null)
    {
        $this->logoFile = $file;
    }

    public function getLogoFilename(): string
    {
        return $this->logoFilename;
    }

    public function setLogoFilename(string $filename)
    {
        $this->logoFilename = $filename;
    }

    public function hasLogoFile(): bool
    {
        return (bool) $this->logoFilename;
    }

    public function hasSmelterData()
    {
        return $this->smelterData;
    }

    public function setSmelterData($bool)
    {
        $this->smelterData = $bool;
    }

    public function getPolicy()
    {
        return $this->policy;
    }

    public function setPolicy($policy)
    {
        $this->policy = trim($policy);
    }

    public static function getValidPolicies()
    {
        return [
            self::POLICY_NONE,
            self::POLICY_GENERAL,
            self::POLICY_AFFIRMATIVE,
        ];
    }

    public static function getPolicyChoices()
    {
        $a = self::getValidPolicies();
        return array_combine($a, $a);
    }

    public function setUpdated(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;
        $this->dateUpdated = new DateTime();
    }

    /**
     * @return DateTime|null
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated ? clone $this->dateUpdated : null;
    }

    /**
     * @return null|User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
