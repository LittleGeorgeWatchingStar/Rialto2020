<?php

namespace Rialto\Stock;

/**
 * A list of IStockItem objects, indexed by stock code.
 */
class ItemIndex implements \IteratorAggregate, \Countable
{
    private $index;

    public function __construct($list = [])
    {
        $this->index = [];
        foreach ( $list as $object ) {
            $this->add($object);
        }
    }

    public function add(Item $object)
    {
        $this->index[$object->getSku()] = $object;
    }

    public function remove(Item $obj)
    {
        unset( $this->index[ $obj->getSku() ] );
    }

    public function get($key)
    {
        if ( $key instanceof Item ) {
            $key = $key->getSku();
        }
        return isset($this->index[$key]) ? $this->index[$key] : null;
    }

    /** @return boolean */
    public function contains($item)
    {
        return (bool) $this->get($item);
    }

    /**
     * Removes all objects from the index.
     */
    public function clear()
    {
        $this->index = [];
    }

    public function toArray()
    {
        return $this->index;
    }

    public function count()
    {
        return count($this->index);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->index);
    }

    /**
     * Sorts the contents by stock code.
     */
    public function sort()
    {
        ksort($this->index);
    }
}
