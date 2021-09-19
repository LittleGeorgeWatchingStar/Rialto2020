<?php

namespace Rialto\Purchasing\Web\Report;

use Rialto\Security\Role\Role;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Generates a summary of manufacturer and their CMRT compliance per Board SKU.
 */
class CmrtReport extends BasicAuditReport
{
    public function getAllowedRoles()
    {
        return [Role::CUSTOMER_SERVICE];
    }

    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder
            ->add('sku', SearchType::class, [
                'label' => 'CMRT Summary for Board',
                'required' => false,
                'attr' => ['placeholder' => 'SKU...'],
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
    }

    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        return [
            'sku' => '',
        ];
    }

    public function getTables(array $params): array
    {
        $tables = [];

        $sql = "
            SELECT stock.StockID AS SKU
            , ifnull(manu.name, '-- unknown --') AS ManufacturerName
            , manu.policy AS Policy
            , manu.notes AS 3TGSummary
            FROM StockMaster stock
              JOIN BOM bom ON stock.StockID = bom.Parent 
                AND bom.ParentVersion = stock.ShippingVersion
              JOIN PurchData pd ON bom.Component = pd.StockID
              LEFT JOIN Manufacturer manu ON pd.manufacturerID = manu.id
            WHERE pd.Preferred = 1
            AND stock.StockID LIKE :sku
            GROUP BY stock.StockID, manu.id
            ORDER BY SKU, ManufacturerName
            LIMIT 1000
        ";

        $table = new RawSqlAudit('CMRT Summary', $sql);

        $tables[] = $table;
        return $tables;
    }
}
