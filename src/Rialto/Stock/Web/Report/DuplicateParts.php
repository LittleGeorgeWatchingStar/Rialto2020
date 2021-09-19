<?php

namespace Rialto\Stock\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;

class DuplicateParts extends BasicAuditReport
{
    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];
        $table = new RawSqlAudit("Possible duplicate parts", "
          select group_concat(StockID) as SKUs
          , count(StockID) as numSKUs
          , Package
          , PartValue
          , group_concat(ifnull(minTemperature, 'N/A')) as minTemperatures
          , group_concat(ifnull(maxTemperature, 'N/A')) as maxTemperatures
          from StockMaster 
          where Package !='' 
          and PartValue != '' 
          group by Package, PartValue 
          having numSKUs > 1
          order by SKUs
        ");
        $table->setScale('value', null);
        $tables[] = $table;
        return $tables;
    }

    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        return [];
    }

    public function getAllowedRoles()
    {
        return [Role::STOCK, Role::ENGINEER];
    }
}
