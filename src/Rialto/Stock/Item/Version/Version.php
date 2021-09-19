<?php

namespace Rialto\Stock\Item\Version;

use Rialto\Stock\Item\StockItem;

/**
 * Represents the version of a stock item.
 *
 * Some items, such as products and PCBs, go through multiple versions or
 * revisions as they are improved. Versions can also be used to specify
 * the software version flashed onto memory or storage components.
 *
 * @see StockItem
 */
class Version
{
    const ANY = '-any-';
    const UNKNOWN = '-unknown-';
    const NONE = '';
    const AUTO = '-auto-';

    public static function any(): self
    {
        return new self(self::ANY);
    }

    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    /**
     * @var string
     */
    protected $version;

    /**
     * @param string|Version $version
     */
    public function __construct($version)
    {
        $this->version = trim($version);
    }

    /** @return string */
    public function __toString()
    {
        return $this->getVersionCode();
    }

    public function getVersionCode(): string
    {
        return $this->version;
    }

    public function getStockCodeSuffix(): string
    {
        if ( $this->isNone() ) {
            return '';
        }
        if ( $this->isSpecified() ) {
            return "-R$this";
        }
        return '';
    }

    public function equals($other): bool
    {
        return $this->version == trim($other);
    }

    /**
     * @param Version|string $other
     */
    public function matches($other): bool
    {
        $other = new Version($other);
        if ( $this->isAny() ) return true;
        if ( $other->isAny() ) return true;
        if ( $this->isAuto() && $other->isSpecified() ) {
            return true;
        }
        if ( $this->isSpecified() && $other->isAuto() ) {
            return true;
        }
        if ( $this->isUnknown() ) return false;
        return $this->version == $other->version;
    }

    public function isAny(): bool
    {
        return (self::ANY == $this->version);
    }

    public function isSpecified(): bool
    {
        return ! in_array($this->version, [
            self::ANY,
            self::AUTO,
            self::UNKNOWN,
        ]);
    }

    public function isUnknown(): bool
    {
        return self::UNKNOWN == $this->version;
    }

    public function isNone(): bool
    {
        return self::NONE == $this->version;
    }

    public function isAuto(): bool
    {
        return self::AUTO == $this->version;
    }
}
