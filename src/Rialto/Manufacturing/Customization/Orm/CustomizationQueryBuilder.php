<?php

namespace Rialto\Manufacturing\Customization\Orm;


use Doctrine\ORM\EntityRepository;
use Gumstix\Doctrine\HighLevelQueryBuilder;

class CustomizationQueryBuilder extends HighLevelQueryBuilder
{
    public function __construct(EntityRepository $repo)
    {
        parent::__construct($repo, 'c');
    }

    public function byName($name)
    {
        $this->qb->andWhere('c.name like :name')
            ->setParameter('name', "%$name%");

        return $this;
    }

    public function bySku($sku)
    {
        $this->qb->andWhere(':sku like c.stockCodePattern')
            ->setParameter('sku', $sku);

        return $this;
    }

    public function bySubstitution($sub)
    {
        $this->qb->join('c.substitutions', 'sub')
            ->andWhere('sub.id = :sub')
            ->setParameter('sub', $sub);

        return $this;
    }

    public function orderBy($field)
    {
        switch ($field) {
            default:
                $this->qb->orderBy('c.name');
                break;
        }
        return $this;
    }
}
