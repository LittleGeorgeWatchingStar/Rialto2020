<?php

namespace Rialto\Database\Orm;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Rialto\Database\DatabaseException;

/**
 * Database entity manager for Rialto entities.
 */
class ErpDbManager extends DoctrineDbManager
{
    /**
     * @var DbManager
     *  Note that this can be ANY implementation of DbManager, which is useful
     *  for automated testing purposes.
     */
    private static $instance = null;

    /**
     * @deprecated
     */
    public static function getInstance(): DbManager
    {
        if (! self::$instance ) {
            throw new DatabaseException(
                "ErpDbManager instance has not been set"
            );
        }
        return self::$instance;
    }

    public static function setInstance(DbManager $dbm = null)
    {
        self::$instance = $dbm;
    }

    public function __construct(EntityManager $em)
    {
        parent::__construct($em, null);
        if (! self::$instance ) {
            self::$instance = $this;
        }
        $conn = $em->getConnection();
        $this->setSqlModes($conn);
    }

    private function setSqlModes(Connection $conn)
    {
        $sqlModes = [
            'TRADITIONAL',
        ];
        $modeString = join(',', $sqlModes);
        $conn->query("set session sql_mode='$modeString'");
    }
}
