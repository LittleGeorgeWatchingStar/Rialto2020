<?php

namespace Rialto\Web\Report;


use Doctrine\ORM\Query;
use Rialto\Database\Orm\DoctrineDbManager;

class DqlAudit extends AbstractAudit
{
    /** @var string the DQL query */
    private $dql;

    public function __construct($title, $dql)
    {
        parent::__construct($title);
        $this->dql = $dql;
    }

    protected function fetchResults(DoctrineDbManager $dbm, array $params)
    {
        $query = $dbm->getEntityManager()->createQuery($this->dql);
        $query->setParameters($params);
        return $query->getResult(Query::HYDRATE_SCALAR);
    }

    protected function supportsParameter($paramName)
    {
        return preg_match("/:$paramName\b/", $this->dql);
    }

}
