<?php

namespace Rialto\Web\Report;

use Rialto\Database\Orm\DoctrineDbManager;

abstract class AbstractAudit implements AuditTable
{
    private $title;

    /** @var AuditColumn[] */
    private $columns = [];
    private $columnAliases = [];
    private $columnWidths = [];
    private $scale = [];
    private $linkRoutes = [];
    private $linkParams = [];
    private $listDelimiters = [];
    private $description;
    private $results;

    private static $numeric = [
        'amount' => 2,
        'cleared' => 2,
        'value' => 2,
        'qty' => 0,
        'onhand' => 0,
    ];

    /**
     * @param string $title
     * @param string $description
     */
    public function __construct($title, $description = "")
    {
        $this->title = $title;
        $this->description = $description;
        $this->scale = self::$numeric;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setAlias($column, $alias)
    {
        $this->columnAliases[$column] = $alias;
    }

    public function setWidth($column, $width)
    {
        $this->columnWidths[$column] = $width;
    }

    public function setScale($key, $scale)
    {
        $this->scale[$key] = $scale;
    }

    private function getScale($column)
    {
        // Pick most recently added ones first.
        $options = array_reverse($this->scale);
        foreach ($options as $pattern => $scale) {
            if (stripos($column, $pattern) !== false) {
                return $scale;
            }
        }
        return null;
    }

    /**
     * @deprecated use setScale() instead
     */
    public function setPrecision($key, $precision)
    {
        $this->setScale($key, $precision);
    }

    /**
     * @deprecated use getScale() instead
     */
    public function getPrecision($column)
    {
        return $this->getScale($column);
    }

    public function setLink($key, $route, callable $paramGenerator)
    {
        $this->linkRoutes[$key] = $route;
        $this->linkParams[$key] = $paramGenerator;
    }

    public function setListDelimiter(string $key, string $delimiter = ',')
    {
        $this->listDelimiters[$key] = $delimiter;
    }

    public function getKey()
    {
        return str_replace(' ', '_', $this->title);
    }

    public function loadResults(DoctrineDbManager $dbm, array $params = [])
    {
        $params = $this->filterParameters($params);
        $this->results = $this->fetchResults($dbm, $params);
        $this->createColumns();
    }

    /**
     * @param DoctrineDbManager $dbm
     * @param string[] $params
     * @return string[][]
     */
    protected abstract function fetchResults(DoctrineDbManager $dbm, array $params);


    private function filterParameters(array $params)
    {
        $supported = [];
        foreach ($params as $key => $value) {
            if ($this->supportsParameter($key)) {
                $supported[$key] = $value;
            }
        }
        return $supported;
    }

    protected abstract function supportsParameter($paramName);

    private function createColumns()
    {
        $this->columns = [];
        if (count($this->results) === 0) return;

        $keys = array_keys($this->results[0]);
        foreach ($keys as $key) {
            $column = new AuditColumn($key);
            $column->setScale($this->getScale($key));
            if (isset($this->columnAliases[$key])) {
                $column->setHeading($this->columnAliases[$key]);
            }
            if (isset($this->columnWidths[$key])) {
                $column->setWidth($this->columnWidths[$key]);
            }
            if (isset($this->linkRoutes[$key])) {
                $column->setLink($this->linkRoutes[$key], $this->linkParams[$key]);
            }
            if (isset($this->listDelimiters[$key])) {
                $column->setListDelimiter($this->listDelimiters[$key]);
            }

            $this->columns[$key] = $column;
        }
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getTotal($key = 'Amount')
    {
        if (isset($this->columns[$key])) {
            $column = $this->columns[$key];
            return $column->getTotal($this->results);
        }
        return null;
    }
}
