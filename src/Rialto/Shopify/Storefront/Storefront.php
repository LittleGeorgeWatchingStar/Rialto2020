<?php

namespace Rialto\Shopify\Storefront;

use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Shopify storefront from which we will accept sales orders.
 */
class Storefront implements Persistable, RialtoEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * The user account for this storefront's API access.
     * @var User
     * @Assert\NotNull(message="User is required.")
     */
    private $user;

    /**
     * @var PaymentMethod
     * @Assert\NotNull(message="Payment method is requried.")
     */
    private $paymentMethod;

    /**
     * The sales type to be used for orders from this storefront.
     * @var SalesType
     * @Assert\NotNull
     */
    private $salesType;

    /**
     * The salesman that new customers will be associated with.
     * @var Salesman
     * @Assert\NotNull
     */
    private $salesman;

    /**
     * The domain name of the storefront.
     * @var string
     * @Assert\NotBlank
     */
    private $domain;

    /**
     * The API key for making API requests.
     * @var string
     * @Assert\NotBlank
     */
    private $apiKey;

    /**
     * The API password for making API requests.
     * @var string
     * @Assert\NotBlank
     */
    private $apiPassword;

    /**
     * The shared secret for authenticating incoming API requests.
     * @var string
     * @Assert\NotBlank
     */
    private $sharedSecret;


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param PaymentMethod $method
     */
    public function setPaymentMethod(PaymentMethod $method)
    {
        $this->paymentMethod = $method;
    }

    public function getSalesType()
    {
        return $this->salesType;
    }

    public function setSalesType(SalesType $salesType)
    {
        $this->salesType = $salesType;
    }

    public function getSalesman()
    {
        return $this->salesman;
    }

    public function setSalesman(Salesman $salesman)
    {
        $this->salesman = $salesman;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain): self
    {
        $this->domain = trim($domain);
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    public function __toString()
    {
        return $this->domain;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = trim($apiKey);
    }

    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * If no password is given, the old one will be kept.
     */
    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = trim($apiPassword) ?: $this->apiPassword;
    }

    /**
     * The base URL for making API requests.
     *
     * @return string
     */
    public function getApiBaseUrl()
    {
        return "https://{$this->apiKey}:{$this->apiPassword}@{$this->domain}";
    }

    /**
     * If no secret is given, the old one will be kept.
     * @param string $sharedSecret
     */
    public function setSharedSecret($sharedSecret)
    {
        $this->sharedSecret = trim($sharedSecret) ?: $this->sharedSecret;
    }

    /**
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->sharedSecret;
    }

    public function getEntities()
    {
        return [$this];
    }
}
