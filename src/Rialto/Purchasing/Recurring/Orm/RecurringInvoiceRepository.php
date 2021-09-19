<?php

namespace Rialto\Purchasing\Recurring\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;

class RecurringInvoiceRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('i');
        return $builder->buildQuery($params);
    }

    /**
     * Finds recurring invoices which are supposed to be entered on the given date.
     * @param \DateTime $date (optional)
     *  Defaults to today.
     */
    public function findByDate(\DateTime $date = null)
    {
        $date = $date ?: new \DateTime();

        $dayOfMonth = $date->format('j');
        $qb = $this->createQueryBuilder('ri');
        $qb->where("concat(',', concat(ri.dates, ',')) like :day")
            ->setParameter('day', "%,$dayOfMonth,%");
        return $qb->getQuery()->getResult();
    }
}
