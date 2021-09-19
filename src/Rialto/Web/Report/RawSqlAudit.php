<?php

namespace Rialto\Web\Report;

use Doctrine\DBAL\Connection;
use PDO;
use Rialto\Database\Orm\DoctrineDbManager;

/**
 * An implementation of AuditTable that is generated from a raw SQL query.
 */
class RawSqlAudit extends AbstractAudit
{
    private $sql;

    /**
     * @param string $title
     * @param string $sql
     * @param string $description
     */
    public function __construct($title, $sql, $description = "")
    {
        parent::__construct($title, $description);
        $this->sql = $sql;
    }


    public function fetchResults(DoctrineDbManager $dbm, array $params)
    {
        $conn = $dbm->getConnection();
        $stmt = $conn->executeQuery($this->sql, $params, $this->getTypes($params));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTypes(array $params)
    {
        $types = [];
        foreach ($params as $key => $val) {
            $types[$key] = is_array($val) ?
                Connection::PARAM_STR_ARRAY :
                PDO::PARAM_STR;
        }
        return $types;
    }

    protected function supportsParameter($paramName)
    {
        return preg_match("/:$paramName\b/", $this->sql);
    }
}
