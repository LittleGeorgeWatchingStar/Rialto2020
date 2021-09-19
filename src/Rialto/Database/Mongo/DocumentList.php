<?php

namespace Rialto\Database\Mongo;


use MongoDB\Collection;
use Rialto\Database\RecordList;

class DocumentList implements RecordList
{
    /**
     * @var Collection
     */
    private $coll;

    private $filters = [];

    private $options = [];

    private $optMap = [
        '_order' => 'sort',
        '_sort' => 'sort',
        '_limit' => 'limit',
    ];

    public function __construct(Collection $collection, array $filters = [])
    {
        $this->coll = $collection;

        $filters = array_filter($filters); // remove empty strings, etc.
        $filters = $this->limitResults($filters);
        foreach ($this->optMap as $filter => $option) {
            if (isset($filters[$filter])) {
                $this->options[$option] = $filters[$filter];
                unset($filters[$filter]);
            }
        }
        $this->filters = $filters;
    }

    private function limitResults(array $filters)
    {
        if (! isset($filters['_limit'])) {
            $filters['_limit'] = RecordList::MAX_RECORDS;
        } elseif ($filters['_limit'] > RecordList::HARD_MAX_RECORDS) {
            $filters['_limit'] = RecordList::HARD_MAX_RECORDS;
        }
        return $filters;
    }

    public function getIterator()
    {
        $cursor = $this->coll->find($this->filters, $this->options);
        $cursor->setTypeMap(['document' => 'array']);
        return new \IteratorIterator($cursor);
    }

    public function limit()
    {
        return min($this->total(), $this->options['limit']);
    }

    public function total()
    {
        return $this->coll->count($this->filters);
    }

}
