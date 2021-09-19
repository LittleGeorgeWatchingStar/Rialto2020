<?php

namespace Rialto\Shipping\Export;

use Rialto\Sales\Order\SalesOrderInterface;

/**
 * Use this in dev and testing environments to force a certain denied party
 * response.
 */
class FakeDeniedPartyScreener implements DeniedPartyScreener
{
    /**
     * @var bool Whether the order will be marked as a denied party.
     */
    private $deny;
    private $enabled;

    public function __construct($deny, $enabled = true)
    {
        $this->deny = $deny;
        $this->enabled = $enabled;
    }


    /** @return boolean */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /** @return DeniedPartyResponse */
    public function screen(SalesOrderInterface $order)
    {
        return new FakeDeniedPartyResponse($this->deny, $order);
    }

}

class FakeDeniedPartyResponse implements DeniedPartyResponse
{
    /** @var bool */
    private $isDenied;

    /** @var SalesOrderInterface */
    private $order;

    public function __construct($isDenied, SalesOrderInterface $order)
    {
        $this->isDenied = $isDenied;
        $this->order = $order;
    }

    /** @return boolean */
    public function hasDeniedParties()
    {
        return $this->isDenied;
    }

    /** @return string[] */
    public function getMatchingParties()
    {
        return [$this->order->getDeliveryName()];
    }

}
