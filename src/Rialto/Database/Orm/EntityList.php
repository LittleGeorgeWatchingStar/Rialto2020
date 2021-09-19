<?php

namespace Rialto\Database\Orm;


use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Rialto\Database\RecordList;

/**
 * Filters and paginates entity records for the list view.
 */
class EntityList implements RecordList
{
    /** @var Paginator */
    private $paginator;

    public function __construct(
        FilteringRepository $repo,
        array $filters)
    {
        $filters = $this->limitResults($filters);
        $query = $repo->queryByFilters($filters);
        $this->paginator = new Paginator($query);
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
        try {
            return $this->paginator->getIterator();
        } catch (\RuntimeException $ex) {
            /* If the query has multiple FROM clauses, the paginator won't work.
             * In which case, just return the full result set. Fix should be
             * coming in Doctrine ORM 2.5:
             *   http://www.doctrine-project.org/jira/browse/DDC-2794
             */
            return new \ArrayIterator($this->paginator->getQuery()->getResult());
        } catch (MappingException $ex) {
            // For composite-key entities.
            return new \ArrayIterator($this->paginator->getQuery()->getResult());
        }
    }

    /**
     * The total number of records that would match the query if it were
     * not limited.
     */
    public function total()
    {
        return $this->paginator->count();
    }

    /**
     * The number of records shown by the iterator, as limited by
     * the query.
     */
    public function limit()
    {
        return min(
            $this->paginator->getQuery()->getMaxResults(),
            $this->total()
        );
    }

    /**
     * @return mixed The first matching result
     */
    public function first()
    {
        return $this->paginator->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
    }
}
