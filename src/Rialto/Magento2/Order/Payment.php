<?php

namespace Rialto\Magento2\Order;

use JMS\Serializer\Annotation\Type;

/**
 * A deserialized sales order payment from the Magento API.
 */
class Payment
{
    const BANK_TRANSFER = 'banktransfer';

    const AUTHORIZENET = 'authorizenet';

    /**
     * Constants to index authorize_card information in the
     * additional_information array for magetno 1 orders.
     */
    const AUTHORIZE_CARDS_ID = 0;
    const REQUESTED_AMOUNT = 1;
    const BALANCE_ON_CARD = 2;
    const LAST_TRANS_ID = 3;
    const PROCESSED_AMOUNT = 4;
    const CC_TYPE = 5;
    const CC_OWNER = 6;
    const CC_LAST4 = 7;
    const CC_EXP_MONTH = 8;
    const CC_EXP_YEAR = 9;
    const CC_SS_ISSUE = 10;
    const CC_SS_START_MONTH = 11;
    const CC_SS_START_YEAR = 12;
    const CAPTURED_AMOUNT = 13;

    const MAGENTO2_CC_TYPE = 4;

    /** @Type("double") */
    public $amount_authorized;

    /** @Type("array") */
    public $additional_information;

    /** @Type("string") */
    public $method;

    /** @Type("string") */
    public $last_trans_id;

    /** @Type("string") */
    public $cc_type;

    /** @Type("double") */
    public $base_amount_paid;


    public function isBankTransfer()
    {
        return $this->method == self::BANK_TRANSFER;
    }

    public function getLastTransId()
    {
        if ($this->isMagento1Order()) {
            return $this->additional_information[self::LAST_TRANS_ID] ?? null;
        }
        return $this->last_trans_id;
    }

    public function getProcessedAmount()
    {
        if ($this->isMagento1Order()) {
            return $this->additional_information[self::PROCESSED_AMOUNT] ?? null;
        }
        return $this->amount_authorized;
    }

    public function getCcType()
    {
        if ($this->isMagento1Order()) {
            return $this->additional_information[self::CC_TYPE] ?? null;
        }
        return $this->additional_information[self::MAGENTO2_CC_TYPE] ?? null;
    }

    public function getCapturedAmount()
    {
        if ($this->isMagento1Order()) {
            return $this->additional_information[self::CAPTURED_AMOUNT] ?? null;
        }
        return $this->base_amount_paid ?? null;
    }

    private function isMagento1Order()
    {
        return $this->method == self::AUTHORIZENET;
    }
}
