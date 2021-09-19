<?php


namespace Rialto\Stock;

/**
 * A location where stock can be stored; eg, a facility or stock transfer.
 */
interface Location
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return bool
     */
    public function equals(Location $other = null);
}
