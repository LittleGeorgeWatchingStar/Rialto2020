<?php

namespace Rialto\Database\Orm;

use Doctrine\ORM\EntityManagerInterface;


/**
 * Determines whether an entity has dependent records.
 *
 * This is typically used to determine whether the entity can be deleted.
 */
class DependentRecordFinder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function hasDependentRecords($entity, array $dependents)
    {
        foreach ($dependents as $class => $fields) {
            foreach ($fields as $field) {
                $qb = $this->em->createQueryBuilder()
                    ->select('count(d)')
                    ->from($class, 'd')
                    ->where("d.$field = :e")
                    ->setParameter('e', $entity);
                $count = (int) $qb->getQuery()->getSingleScalarResult();
                if ($count > 0) {
                    return true;
                }
            }
        }
        return false;
    }

}
