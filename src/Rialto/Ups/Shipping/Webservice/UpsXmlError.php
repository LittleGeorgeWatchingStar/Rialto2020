<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\Shipping\ShippingException;
use SimpleXMLElement;


/**
 * An error returned by UPS XML web services.
 *
 * You can throw it as an exception or use it for informational purposes.
 */
class UpsXmlError extends \Exception implements ShippingException
{
    const ERROR_WARNING = 'Warning';
    const ERROR_HARD = 'Hard';
    const ERROR_TRANSIENT = 'Transient';

    private $nonFatalErrors = [self::ERROR_WARNING];
    private $fatalErrors = [self::ERROR_HARD, self::ERROR_TRANSIENT];

    private static $userErrors = [
        '111285', // bad postal code
        '111286', // bad state code
    ];

    private $request;
    private $severity;

    public function __construct(UpsXmlRequest $request, SimpleXMLElement $element)
    {
        $this->request = $request;
        $this->severity = $this->extractElement($element, 'ErrorSeverity');
        $message = $this->extractElement($element, 'ErrorDescription');
        $code = $this->extractElement($element, 'ErrorCode');
        parent::__construct($message, $code);
    }

    /** @return bool */
    public function isFatal()
    {
        if ( in_array($this->severity, $this->fatalErrors)) return true;
        elseif ( in_array($this->severity, $this->nonFatalErrors)) return false;
        throw new UpsXmlException(
            $this->request,
            sprintf('returned an error with an unknown severity \'%s\'.', $this->severity)
        );
    }

    public function getSeverity()
    {
        return $this->severity;
    }

    public function getDescription()
    {
        return $this->getMessage();
    }

    private function extractElement(SimpleXmlElement $parent, $name)
    {
        if (isset($parent->$name)) {
            return trim((string) $parent->$name);
        }
        throw new UpsXmlException($this->request, "returned an error with no $name.");
    }

    /**
     * Returns true if the error is something that the end user can fix, such
     * as a bad address.
     *
     * @return boolean
     */
    public function isUserError()
    {
        return in_array($this->getCode(), self::$userErrors);
    }
}
