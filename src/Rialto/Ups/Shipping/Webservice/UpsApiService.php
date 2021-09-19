<?php

namespace Rialto\Ups\Shipping\Webservice;

use DateTime;
use Gumstix\GeographyBundle\Model\PostalAddress;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Ups\Shipping\UpsShipment;
use Rialto\Ups\UpsAccount;
use SimpleXMLElement;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

class UpsApiService
{
    /** @var Client */
    private $http;

    /** @var UpsAccount */
    private $account;

    /** @var TemplatingEngine */
    private $templating;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(UpsAccount $account,
                                Client $http,
                                TemplatingEngine $templating,
                                LoggerInterface $logger)
    {
        $this->account = $account;
        $this->templating = $templating;
        $this->http = $http;
        $this->logger = $logger;
    }

    /**
     * Sends this request and returns the XML response from UPS.
     *
     * @return SimpleXMLElement
     * @throws UpsWebServiceException
     */
    private function sendRequest(UpsXmlRequest $request)
    {
        $uri = $this->getUri($request);
        $requestBody = $this->renderXml($request);
        $this->logger->debug($requestBody);

        try {
            $httpResp = $this->http->post($uri, [
                'body' => $requestBody,
            ]);
        } catch (GuzzleException $ex) {
            throw new UpsWebServiceException($uri, $ex->getMessage());
        }
        return new SimpleXMLElement($httpResp->getBody());
    }

    /**
     * @return string
     *  The URI to which this request will be sent.
     */
    private function getUri(UpsXmlRequest $request)
    {
        return '/ups.app/xml/' . $request->getName();
    }

    /**
     * Renders this request into an XML string that is ready to be sent.
     *
     * @return string
     */
    private function renderXml(UpsXmlRequest $request)
    {
        return $this->renderHeader() .
            $request->render($this->templating);
    }

    private function renderHeader()
    {
        $accessRequest = new AccessRequest($this->account);
        return $accessRequest->render($this->templating);
    }

    public function Shop(RatableOrder $order, $accountNumber): RateResponse
    {
        $request = new ShopRequest($order, $accountNumber);
        $xml = $this->sendRequest($request);
        return new RateResponse($request, $xml);
    }

    public function shipConfirm(UpsShipment $shipment): ShipConfirmResponse
    {
        $request = new ShipConfirmRequest($shipment);
        $xml = $this->sendRequest($request);
        return new ShipConfirmResponse($request, $xml);
    }

    public function shipAccept(string $shipDigest): ShipAcceptResponse
    {
        $request = new ShipAcceptRequest($shipDigest);
        $xml = $this->sendRequest($request);
        return new ShipAcceptResponse($request, $xml);
    }

    public function rate(UpsShipment $shipment): RateResponse
    {
        $request = new RateRequest($shipment);
        $xml = $this->sendRequest($request);
        return new RateResponse($request, $xml);
    }

    public function timeInTransit(PostalAddress $from,
                                  PostalAddress $to,
                                  DateTime $pickup): TimeInTransitResponse
    {
        $request = new TimeInTransitRequest($from, $to, $pickup);
        $xml = $this->sendRequest($request);
        return new TimeInTransitResponse($request, $xml);
    }

    public function track(string $trackingNumber): TrackResponse
    {
        $request = new TrackRequest($trackingNumber);
        $xml = $this->sendRequest($request);
        return new TrackResponse($request, $xml);
    }
}
