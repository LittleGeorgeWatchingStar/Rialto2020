<?php

namespace Rialto\Database\Orm;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Used by entity repositories to build select queries according to
 * a set of filter parameters.
 */
class FilterQueryBuilder
{
    /** @var QueryBuilder */
    private $qb;
    private $parameters = [];

    /** @var Join[] */
    private $joins = [];

    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * Use this method if you need to do an inner join on another table no
     * matter which filters are present.
     *
     * @param string $on
     *  The relationship on which to join
     * @param string $alias
     *  An alias for the joined entity
     * @return FilterQueryBuilder Fluent interface
     */
    public function join($on, $alias, $conditionType = null, $condition = null)
    {
        $this->joins[] = new InnerJoin($on, $alias, $conditionType, $condition);
        return $this;
    }

    /**
     * Like join(), but does a left join instead.
     * @see join()
     * @return FilterQueryBuilder Fluent interface
     */
    public function leftJoin($on, $alias, $conditionType = null, $condition = null)
    {
        $this->joins[] = new LeftJoin($on, $alias, $conditionType, $condition);
        return $this;
    }

    /**
     * Shortcut for adding a "fetch join" to the query builder.
     * @return FilterQueryBuilder Fluent interface
     */
    public function joinAndSelect($on, $alias, $conditionType = null, $condition = null)
    {
        $this->qb->join($on, $alias, $conditionType, $condition)
            ->addSelect($alias);
        return $this;
    }

    /**
     * Shortcut for adding a "fetch join" to the query builder.
     * @return FilterQueryBuilder Fluent interface
     */
    public function leftJoinAndSelect($on, $alias, $conditionType = null, $condition = null)
    {
        $this->qb->leftJoin($on, $alias, $conditionType, $condition)
            ->addSelect($alias);
        return $this;
    }

    /** @return FilterQueryBuilder Fluent interface */
    public function addSelect($columnDescription)
    {
        $this->qb->addSelect($columnDescription);
        return $this;
    }

    /** @return FilterQueryBuilder Fluent interface */
    public function distinct()
    {
        $this->qb->distinct();
        return $this;
    }

    /**
     * Maps a filter parameter to a callback function.  The callback
     * must take two arguments: a Doctrine\ORM\QueryBuilder object and the value of the
     * parameter.  The callback should modify the query according to the
     * value.  If the callback returns true, then no further parameters
     * will be processed, which is useful for things querying by primary
     * key.
     *
     * @param string $paramName
     * @param callback $callable
     */
    public function add($paramName, $callable)
    {
        $this->parameters[$paramName] = $callable;
    }

    /**
     * Returns the final Doctrine\ORM\Query, which has been processed by
     * all callbacks added via the add() method.
     *
     * @param array $filters
     *  The query string data of the request.
     * @return Query
     */
    public function buildQuery(array $filters)
    {
        $filters = $this->processSpecialFilters($filters);

        foreach ( $this->joins as $join ) {
            $join->addToQuery($this->qb);
        }

        foreach ( $this->parameters as $name => $callable ) {
            /* IMPORTANT: this has to be ! empty() instead of isset()
             * for filter forms to work. For boolean values, use strings 'yes'
             * and 'no' instead. */
            if ( ! empty($filters[$name]) ) {
                $value = $this->convertWildcards($filters[$name]);
                $stop = $callable($this->qb, $value);
                if ( $stop ) break;
            }
        }
        return $this->qb->getQuery();
    }

    private function processSpecialFilters(array $filters)
    {
        if (! empty($filters['_limit']) ) {
            $limit = (int) $filters['_limit'];
            if ( $limit > 0 ) {
                $this->qb->setMaxResults($limit);
            }
            if ( isset($filters['_start']) ) {
                $start = (int) $filters['_start'];
                $this->qb->setFirstResult($start);
            } elseif ( isset($filters['_page']) ) {
                // deprecated; use _start instead
                $page = (int) $filters['_page'];
                $this->qb->setFirstResult($page * $limit);
            }
        }
        if ( empty($filters['_order']) ) {
            $filters['_order'] = '_default';
        }

        return $filters;
    }

    private function convertWildcards($value)
    {
        if (! is_string($value) ) return $value;
        return str_replace('*', '%', $value);
    }
}

abstract class Join
{
    protected $on;
    protected $alias;
    protected $conditionType;
    protected $condition;

    public function __construct($on, $alias, $conditionType = null, $condition = null)
    {
        $this->on = $on;
        $this->alias = $alias;
        $this->conditionType = $conditionType;
        $this->condition = $condition;
    }

    public abstract function addToQuery(QueryBuilder $qb);
}

class InnerJoin
extends Join
{
    public function addToQuery(QueryBuilder $qb)
    {
        $qb->join($this->on, $this->alias, $this->conditionType, $this->condition);
    }
}

class LeftJoin
extends Join
{
    public function addToQuery(QueryBuilder $qb)
    {
        $qb->leftJoin($this->on, $this->alias, $this->conditionType, $this->condition);
    }
}
