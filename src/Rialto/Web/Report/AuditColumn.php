<?php

namespace Rialto\Web\Report;

/**
 * A column in an AuditTable.
 */
class AuditColumn
{
    private $key;
    private $heading = null;
    private $scale = null;
    private $width = null;
    private $linkRoute = null;
    private $linkParams = null;
    private $delimiter = null;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function setScale($scale)
    {
        $this->scale = $scale;
    }

    public function isNumeric()
    {
        return null !== $this->scale;
    }

    public function hasSpecifiedWidth()
    {
        return $this->width !== null;
    }

    public function getHeading()
    {
        return $this->heading ?: $this->key;
    }

    public function setHeading($heading)
    {
        $this->heading = $heading;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function isLink()
    {
        return null !== $this->linkRoute;
    }

    public function setLink($route, callable $paramGenerator)
    {
        $this->linkRoute = $route;
        $this->linkParams = $paramGenerator;
    }

    public function isList(): bool
    {
        return $this->delimiter != null;
    }

    public function getListDelimiter(): string
    {
        return $this->delimiter;
    }

    public function setListDelimiter(string $delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return string|null
     */
    public function getLinkRoute()
    {
        return $this->linkRoute;
    }

    public function getLinkParams(array $result)
    {
        return call_user_func($this->linkParams, $result);
    }

    public function getValue(array $result)
    {
        return $result[$this->key];
    }

    public function renderValue(array $result): string
    {
        $value = $this->getValue($result);
        return $this->format($value);
    }

    private function format($value): string
    {
        if ($this->isNumeric() && is_numeric($value) && $value !== null) {
            return number_format($value, $this->scale);
        }
        return trim($value); // convert to string
    }

    public function getTotal(array $results)
    {
        if (!$this->isNumeric()) {
            return null;
        }
        $total = 0;
        foreach ($results as $result) {
            $total += $this->getValue($result);
        }
        return $total;
    }

    public function renderTotal(array $results)
    {
        if (!$this->isNumeric()) {
            return null;
        }
        $total = $this->getTotal($results);
        return $this->format($total);
    }
}
