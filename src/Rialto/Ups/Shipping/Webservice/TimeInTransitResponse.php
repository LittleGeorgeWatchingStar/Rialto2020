<?php

namespace Rialto\Ups\Shipping\Webservice;

use DateTime;
use SimpleXMLElement;

class TimeInTransitResponse extends UpsXmlResponse
{
    /**
     * @var array
     */
    private $serviceSummaries = [];

    /**
     * @param string $xml
     */
    protected function parseResults(SimpleXMLElement $xml)
    {
        foreach ($xml->TransitResponse->ServiceSummary as $serviceSummary) {
            $result = [];

            $result['date'] = (string) $serviceSummary->EstimatedArrival->Date;
            $result['time'] = (string) $serviceSummary->EstimatedArrival->Time;

            $code = (string) $serviceSummary->Service->Code;
            $this->serviceSummaries[$code] = $result;
        }
    }

    /**
     * Returns a list of valid shipping method codes.
     *
     * @return string[]
     */
    public function getMethodCodes(): array
    {
        $codes = array_keys($this->serviceSummaries);
        return $codes;
    }

    public function getEstimatedArrival($code): ?DateTime
    {
        if (!isset($this->serviceSummaries[$code])) {
            return null;
        }
        $date = $this->serviceSummaries[$code]['date'];
        $time = $this->serviceSummaries[$code]['time'];
        return new DateTime("$date $time");
    }
}