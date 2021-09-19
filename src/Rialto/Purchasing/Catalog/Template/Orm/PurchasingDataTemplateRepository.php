<?php


namespace Rialto\Purchasing\Catalog\Template\Orm;


use Rialto\Database\Orm\FilteringRepositoryAbstract;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;


class PurchasingDataTemplateRepository extends FilteringRepositoryAbstract
{
    public function queryByFilters(array $params)
    {
        $builder = $this->createRestBuilder('p');
        return $builder->buildQuery($params);
    }

    /**
     * @return PurchasingDataTemplate[]
     */
    public function findTemplatesForStrategy($strategy): array
    {
        $qb = $this->createQueryBuilder('template')
            ->andWhere('template.strategy = :strategy')
            ->setParameter('strategy', $strategy);
        return $qb->getQuery()->getResult();
    }

}
