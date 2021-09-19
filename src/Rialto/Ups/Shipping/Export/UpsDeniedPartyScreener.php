<?php

namespace Rialto\Ups\Shipping\Export;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Shipping\Export\DeniedPartyScreener;
use Rialto\Ups\UpsAccount;
use Rialto\Ups\Util\StringFormatter;
use SoapClient;
use SoapFault;
use SoapHeader;


/**
 * Service for screening a sales order to make sure it is not bound for a
 * "denied party"; that is, someone with whom the US Government has prohibited
 * trade (eg, terrorists).
 */
class UpsDeniedPartyScreener implements DeniedPartyScreener
{
    const SCHEMA_URI_AUTH = 'http://www.ups.com/schema/xpci/1.0/auth';

    /** @var UpsAccount */
    private $account;

    /** @var string */
    private $baseUri;

    private $enabled = true;
    private $screenType = 'Party';
    private $matchLevel = 'High';

    /** @var StringFormatter */
    private $formatter;

    public function __construct(UpsAccount $account, string $baseUri)
    {
        $this->account = $account;
        $this->baseUri = $baseUri;
    }

    /** @return bool */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }

    public function setScreenType($screenType)
    {
        $this->screenType = $screenType;
    }

    public function setMatchLevel($matchLevel)
    {
        $this->matchLevel = $matchLevel;
    }

    private function getUri()
    {
        return $this->baseUri . '/webservices/DeniedParty';
    }

    /**
     * Screens the given order and returns a response.
     * @param SalesOrderInterface $order
     * @return UpsDeniedPartyResponse
     */
    public function screen(SalesOrderInterface $order)
    {
        $wsdl = realpath(__DIR__ . "/DeniedParty.wsdl");
        $mode = [
            'soap_version' => SOAP_1_1,
            'trace' => 1
        ];

        $client = new SoapClient($wsdl, $mode);
        $client->__setLocation($this->getUri());
        $client->__setSoapHeaders($this->createSoapHeader());

        $request = $this->createDpsRequest($order);

        try {
            $response = $client->ProcessDPSRequest($request);
            return new UpsDeniedPartyResponse($response);
        }
        catch ( SoapFault $ex ) {
            return $this->handleSoapFault($client, $ex);
        }
    }

    private function createSoapHeader()
    {
        $auth = [
            'UserId' => $this->account->getUserId(),
            'Password' => $this->account->getPassword(),
            'AccessLicenseNumber' => $this->account->getAccessLicense(),
        ];

        return new SoapHeader(self::SCHEMA_URI_AUTH, 'AccessRequest', $auth);
    }

    private function createDpsRequest(SalesOrderInterface $order)
    {
        $request = [];
        $request['Request'] = $this->createRequestTransportType();
        $request['Party'] = $this->createPartyType($order);
        return $request;
    }

    private function createRequestTransportType()
    {
        $action = [];
        $action['RequestAction'] = 'DeniedPartyScreener';
        return $action;
    }

    private function createPartyType(SalesOrderInterface $order)
    {
        $party = [];
        $party['ScreenType'] = $this->screenType;
        $party['MatchLevel'] = $this->matchLevel;
        $party['CompanyName'] = $this->prepString($order->getDeliveryCompany(), 128);
        $party['ContactName'] = $this->prepString($order->getDeliveryName(), 128);
        $party['Address'] = $this->createAddressType($order->getDeliveryAddress());
        return $party;
    }

    private function createAddressType(PostalAddress $address)
    {
        $addressType = [];
        $addressType['AddressLine'] = $this->createAddressLines($address);
        $addressType['City'] = $this->prepString($address->getCity(), 30);
        $addressType['CountryCode'] = $address->getCountryCode();
        $addressType['PostalCode'] = $this->prepString($address->getPostalCode(), 20);
        $state = $address->getStateName();
        if ( $state ) {
            $addressType['State'] = $this->prepString($state, 35);
        }
        return $addressType;
    }

    private function createAddressLines(PostalAddress $address)
    {
        $addressLines = [];
        $addressLines[] = $this->prepString($address->getStreet1(), 35);
        if ( $address->getStreet2() ) {
            $addressLines[] = $this->prepString($address->getStreet2(), 35);
        }
        return $addressLines;
    }

    private function handleSoapFault(SoapClient $client, SoapFault $ex)
    {
        error_log('failed DPS request: ' . $client->__getLastRequest());
        $lastResponse = $client->__getLastResponse();
        error_log('failed DPS response: '. $lastResponse);

        if ( $this->isInvalidXmlError($ex) ) {
            return new InvalidXmlResponse($lastResponse);
        }

        $ex = UpsDeniedPartyException::fromException($ex, $this->getUri());
        $ex->setResponse($client->__getLastResponse());
        throw $ex;
    }

    private function isInvalidXmlError(SoapFault $ex)
    {
        return false !== stripos($ex->getMessage(), 'looks like we got no xml document');
    }

    /**
     * @see StringFormatter#prepString()
     */
    private function prepString($string, $max_length)
    {
        if (! $this->formatter ) {
            $this->formatter = new StringFormatter();
        }
        return $this->formatter->prepString($string, $max_length);
    }
}
