<?php

namespace Rialto\Manufacturing\Bom;

use Rialto\Exception\InvalidDataException;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * Thrown when there is an error involving a bill of materials (BOM).
 */
class BomException extends InvalidDataException
{
    /** @var Bom */
    private $bom;

    public function __construct(Bom $bom, $message, \Exception $previous = null)
    {
        $code = $previous ? $previous->getCode() : 0;
        parent::__construct($message, $code, $previous);
        $this->bom = $bom;
    }

    /**
     * @return Bom
     */
    public function getBom()
    {
        return $this->bom;
    }

    /** @return ItemVersion */
    public function getItemVersion()
    {
        return $this->bom->getParent();
    }

    public function getSku()
    {
        return $this->getItemVersion()->getSku();
    }
}
