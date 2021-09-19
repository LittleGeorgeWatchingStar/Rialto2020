<?php

namespace Rialto\Magento2\Storefront;

use Gumstix\Magento\Oauth\OAuthParams;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Sales\Type\SalesType;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Magento2-powered storefront.
 *
 * @UniqueEntity(fields={"user"},
 *     message="There is already a storefront associated with that user.")
 */
class Storefront implements RialtoEntity
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
     * The sales type to be used for orders from this storefront.
     * @var SalesType
     * @Assert\NotNull
     */
    private $salesType;

    /**
     * The sales type to be used for quotations from this storefront.
     * @var SalesType
     * @Assert\NotNull
     */
    private $quoteType;

    /**
     * The salesman that new customers will be associated with.
     * @var Salesman
     * @Assert\NotNull
     */
    private $salesman;

    /**
     * Where stock for this storefront will be drawn from.
     *
     * @var Facility
     * @Assert\NotNull
     */
    private $shipFromFacility;

    /**
     * The base URL of the storefront.
     * @var string
     * @Assert\NotBlank
     * @Assert\Url
     */
    private $storeUrl;

    /**
     * The API Key used to verify initial magento post request for OAuth handshake.
     * @var string
     */
    private $apiKey = '';

    /**
     * OAuth Credentials needed for OAuth handshake
     */
    private $consumerKey = '';
    private $consumerSecret = '';
    private $oauthVerifier = '';
    private $accessToken = '';
    private $accessTokenSecret = '';

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
     * Set user
     *
     * @param User $user
     *
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getSalesType()
    {
        return $this->salesType;
    }

    public function setSalesType(SalesType $salesType)
    {
        $this->salesType = $salesType;
    }

    /**
     * @return SalesType
     */
    public function getQuoteType()
    {
        return $this->quoteType;
    }

    public function setQuoteType(SalesType $quoteType)
    {
        $this->quoteType = $quoteType;
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
     * @return Facility
     */
    public function getShipFromFacility()
    {
        return $this->shipFromFacility;
    }

    public function setShipFromFacility(Facility $location)
    {
        $this->shipFromFacility = $location;
    }

    public function setStoreUrl($url)
    {
        $this->storeUrl = trim($url);
    }

    public function getStoreUrl()
    {
        return $this->storeUrl;
    }

    public function __toString()
    {
        return $this->storeUrl;
    }

    private function getUrl($path)
    {
        $base = rtrim($this->storeUrl, '/');
        $path = ltrim($path, '/');
        return "$base/$path";
    }


    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }


    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function regenerateApiKey()
    {
        $this->setApiKey($this->randomString());
    }

    /**
     * Create a random string
     *
     * @param int $length length of the string to create
     * @return string the string
     */
    private function randomString($length = 10)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    /**
     * @return bool true if Magento should capture the credit card payment
     *   for $order.
     */
    public function shouldCapturePayment(SalesOrder $order)
    {
        return $order->isSalesType($this->salesType);
    }

    /**
     * @param  OAuthParams $oauthParams
     */
    public function setOAuthCredentials($oauthParams)
    {
        $this->consumerKey = $oauthParams->oauth_consumer_key;
        $this->consumerSecret = $oauthParams->oauth_consumer_secret;
        $this->oauthVerifier = $oauthParams->oauth_verifier;
    }

    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }


    public function getOauthVerifier()
    {
        return $this->oauthVerifier;
    }

    /**
     * @param string $accessToken
     * @param string $accessTokenSecret
     */
    public function setAccessTokens($accessToken, $accessTokenSecret)
    {
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getAccessTokenSecret()
    {
        return $this->accessTokenSecret;
    }
}
