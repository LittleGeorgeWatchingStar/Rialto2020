<?php

namespace Rialto\Security\User;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user of the web application.
 *
 * @UniqueEntity(fields="id", errorPath="username")
 */
class User implements RialtoEntity,
    AdvancedUserInterface,
    EquatableInterface,
    Serializable,
    Mailable
{
    const DEFAULT_THEME = 'claro';
    const DEFAULT_PAGESIZE = 'A4';

    /**
     * @var string
     * @Assert\NotBlank(message="Username cannot be blank")
     * @Assert\Regex(pattern="/^[a-z]/",
     *   message="Username must begin with a lowercase letter.")
     * @Assert\Regex(pattern="/^[a-z0-9_\.]+$/",
     *   message="Only lowercase letters, digits, underscore, and period are allowed.")
     * @Assert\Length(
     *   min=3,
     *   minMessage="Username must be at least {{ limit }} characters long.",
     *   max=20,
     *   maxMessage="Username cannot be longer than {{ limit }} characters.")
     */
    private $id;

    /**
     * Single-sign on accounts that are linked to this user.
     * @var SsoLink[]
     * @Assert\Valid(traverse=true)
     */
    private $ssoLinks = '';

    /**
     * @Assert\NotBlank(message="Name cannot be blank")
     */
    private $name = '';
    private $phone = '';

    /**
     * The user's email address.
     * @Assert\Email(message="Not a valid email address")
     * @Assert\Length(max=100)
     */
    private $email = '';

    /**
     * The user's XMPP (eg, Google talk) address.
     * @Assert\Email(message="XMPP must be a valid email address.")
     * @Assert\Length(max=100)
     */
    private $xmpp = '';

    private $lastLoginDate;
    private $defaultPageSize = self::DEFAULT_PAGESIZE;
    private $theme = 'claro';

    /**
     * @var string How the user prefers dates to be formatted.
     * @Assert\Choice(callback="getValidDateFormats", strict=true)
     */
    private $dateFormat = 'Y-m-d';
    private $language = 'en_GB';

    /**
     * @var Facility The stock location where this user usually works.
     *
     *  This is important for warehouse staff.
     */
    private $defaultLocation = null;

    /**
     * @var Supplier
     */
    private $supplier;

    /** @var CustomerBranch|null */
    private $customerBranch;

    /** @var Role[] */
    private $roles;

    public function __construct($username)
    {
        $this->id = strtolower(trim($username));
        $this->roles = new ArrayCollection();
        $this->ssoLinks = new ArrayCollection();
    }

    public function getDefaultPageSize(): string
    {
        return $this->defaultPageSize;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->getId();
    }

    public function __toString()
    {
        return $this->name ?: $this->id;
    }

    /** @return string[] */
    public function getUuids(): array
    {
        return $this->ssoLinks->map(function (SsoLink $link) {
            return $link->getUuid();
        })->toArray();
    }

    public function addUuid($uuid)
    {
        $this->ssoLinks[] = new SsoLink($uuid, $this);
    }

    public function removeUuid($uuid)
    {
        foreach ($this->ssoLinks as $link) {
            if ($link->isUuid($uuid)) {
                $this->ssoLinks->removeElement($link);
            }
        }
    }

    public function getPassword()
    {
        return '';  // SSO handles passwords
    }

    /** @return Role[] */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    public function addRole(Role $role)
    {
        $this->roles[] = $role;
    }

    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    public function getSalt()
    {
        return '';  // SSO handles passwords
    }

    public function eraseCredentials()
    {
        /* TODO: what should happen here? */
    }

    public function isEqualTo(UserInterface $other)
    {
        return ($this->getUsername() == $other->getUsername());
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

    public function getEmail()
    {
        return $this->email;
    }

    public function getEmailLabel()
    {
        return sprintf('%s <%s>', $this->getName(), $this->getEmail());
    }

    public function setEmail($email)
    {
        $this->email = trim($email);
    }

    public function getXmpp(): string
    {
        return $this->xmpp;
    }

    public function setXmpp($xmpp)
    {
        $this->xmpp = trim($xmpp);
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = trim($phone);
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme($theme)
    {
        $this->theme = trim($theme);
    }

    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(string $format)
    {
        $this->dateFormat = trim($format);
    }

    /** @return string[] */
    public static function getValidDateFormats(): array
    {
        return [
            'Y-m-d',
            'm/d/Y',
            'M d, Y',
            'F j, Y',
        ];
    }

    /** @return string[] */
    public static function getDateFormatOptions(): array
    {
        $options = [];
        foreach (self::getValidDateFormats() as $format) {
            $label = date($format);
            $options[$label] = $format;
        }
        return $options;
    }

    /** @return Customer|null */
    public function getCustomer()
    {
        return $this->customerBranch ?
            $this->customerBranch->getCustomer() :
            null;
    }

    /** @return CustomerBranch|null */
    public function getCustomerBranch()
    {
        return $this->customerBranch;
    }

    /** @return Supplier|null */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier = null)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return Facility|null
     */
    public function getDefaultLocation()
    {
        return $this->defaultLocation;
    }

    public function setDefaultLocation(Facility $location = null)
    {
        $this->defaultLocation = $location;
    }

    /**
     * @return DateTime|null
     */
    public function getLastLoginDate()
    {
        return $this->lastLoginDate ? clone $this->lastLoginDate : null;
    }

    /**
     * Sets the user's last login date to the current date and time.
     */
    public function updateLastLoginDate()
    {
        $this->lastLoginDate = new DateTime();
    }

    public function isEnabled()
    {
        return count($this->roles) > 0;
    }

    public function disable()
    {
        $this->roles->clear();
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function serialize()
    {
        return serialize([
            'id' => $this->id,
            'name' => $this->name,
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->id = $data['id'];
        $this->name = $data['name'];
    }
}
