<?php

namespace Rialto\Payment;

use AuthorizeNetResponse;

class AuthorizeNetException extends \UnexpectedValueException implements GatewayException
{
    const REASON_NOT_FOUND = 16;

    public function __construct(AuthorizeNetResponse $response, $message = null)
    {
        $message = $message ?: $this->getMessageFromResponse($response);
        parent::__construct($message, $response->response_reason_code);
    }

    private function getMessageFromResponse($response)
    {
        return $response->error ?
            $response->error_message :
            $response->response_reason_text;
    }

    /** @return boolean */
    public function isTransactionNotFound()
    {
        return $this->getCode() == self::REASON_NOT_FOUND;
    }
}
