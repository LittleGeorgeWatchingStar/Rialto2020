<?php

namespace Rialto\Printing\Job;

use Rialto\Database\Orm\RialtoRepositoryAbstract;

/**
 * @see PrintJob
 */
class PrintJobRepository extends RialtoRepositoryAbstract
{
    /** How old is an "old" print job? */
    const OLD = '-1 week';

    /**
     * @return PrintJob[]
     */
    public function findOutstandingJobs($limit = null)
    {
        $constraints = [
            'datePrinted' => null,
            'error' => '',
        ];
        $orderBy = ['dateCreated' => 'asc'];
        return $this->findBy($constraints, $orderBy, $limit);
    }

    /**
     * @return integer The number of jobs deleted.
     */
    public function deleteOldJobs(\DateTime $before = null)
    {
        $before = $before ?: new \DateTime(self::OLD);
        $qb = $this->createQueryBuilder('job');
        $qb->delete()
            ->where('job.datePrinted is not null')
            ->andWhere('job.datePrinted < :old')
            ->setParameter('old', $before);

        $query = $qb->getQuery();
        return $query->execute();
    }
}
