<?php

namespace Rialto\Manufacturing\Customization\Orm;

use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Substitution;

class SubstitutionRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('s');
        $builder->leftJoinAndSelect('s.dnpComponent', 'dnp');
        $builder->leftJoinAndSelect('s.addComponent', 'add');
        return $builder->buildQuery($params);
    }

    /**
     * @param Customization $customization
     * @return Substitution[]
     */
    public function findByCustomization(Customization $customization)
    {
        if (! $customization->getId() ) return [];

        $qb = $this->createQueryBuilder('substitution')
            ->from(Customization::class, 'cust')
            ->join('cust.substitutions', 'custSubstitution')
            ->where('custSubstitution = substitution')
            ->andWhere('cust = :customization')
            ->setParameter('customization', $customization->getId());
        return $qb->getQuery()->getResult();
    }

    /** @return Substitution[] */
    public function findExtendedTemperature()
    {
        $qb = $this->queryByFlag(Substitution::FLAG_EXT_TEMP);
        $qb->andWhere('sub.type = :swap_all')
            ->setParameter('swap_all', Substitution::TYPE_SWAP_ALL);
        return $qb->getQuery()->getResult();
    }

    private function queryByFlag($flag)
    {
        $qb = $this->createQueryBuilder('sub');
        $qb->andWhere('sub.flags like :ext_temp')
            ->setParameter('ext_temp', "%$flag%");
        return $qb;
    }
}
