<?php

namespace Rialto\Ups\Shipping\Export;

use Rialto\Shipping\Export\DeniedPartyResponse;
use stdClass;

/**
 * Response from UPS denied party query.
 */
class UpsDeniedPartyResponse implements DeniedPartyResponse
{
    const STATUS_NOT_DENIED = "No Denied Parties Found";
    const STATUS_DENIED = "Denied Parties Found";

    private $response;

    public function __construct(stdClass $response)
    {
        $this->response = $response;
    }

    public function hasDeniedParties()
    {
        $status = $this->response->Response->DeniedPartySearchStatus;
        switch ($status) {
            case self::STATUS_NOT_DENIED:
                return false;
            case self::STATUS_DENIED:
                return true;
            default:
                throw new \UnexpectedValueException(sprintf(
                    'Unknown DPS response string %s.', $status
                ));
        }
    }

    public function getMatchingParties()
    {
        $parties = [];
        foreach ($this->getGovernmentLists() as $govtList) {
            foreach ($this->getDeniedPartyList($govtList) as $deniedParty) {
                $parties[] = $this->partyToString($deniedParty);
            }
        }
        return $parties;
    }

    private function getGovernmentLists()
    {
        if (is_array($this->response->GovernmentList)) {
            return $this->response->GovernmentList;
        }
        return [$this->response->GovernmentList];
    }

    private function getDeniedPartyList(stdClass $govtList)
    {
        if (is_array($govtList->DeniedParty)) {
            return $govtList->DeniedParty;
        } else return [$govtList->DeniedParty];
    }

    private function partyToString(stdClass $party)
    {
        logDebug(json_encode($party), 'party');
        $parts = [];
        if (isset($party->Names->Name)) {
            $parts[] = is_array($party->Names->Name) ?
                join('; ', $party->Names->Name) :
                $party->Names->Name;
        }
        if (isset($party->Addresses->Address)) {
            $parts[] = $this->addressToString($party->Addresses->Address);
        }
        if (isset($party->Remarks)) {
            $parts[] = $party->Remarks;
        }
        return join('; ', $parts);
    }

    private function addressToString($address)
    {
        return join(', ', $this->addressToArray($address));
    }

    private function addressToArray($address)
    {
        if (is_array($address)) {
            $result = [];
            foreach ($address as $elem) {
                $result = array_merge($result, $this->addressToArray($elem));
            }
            return $result;
        }
        return get_object_vars($address);
    }
}
