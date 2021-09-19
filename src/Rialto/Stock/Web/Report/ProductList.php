<?php

namespace Rialto\Stock\Web\Report;

use Rialto\Accounting\Currency\Currency;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Category\StockCategory;
use Rialto\Web\Report\AuditTable;
use Rialto\Web\Report\BasicAuditReport;
use Rialto\Web\Report\RawSqlAudit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductList extends BasicAuditReport
{
    public function getFilterForm(FormBuilderInterface $builder)
    {
        return $builder->add('category', EntityType::class, [
                'class' => StockCategory::class,
                'required' => false,
                'placeholder' => '-- all --',
        ])
            ->add('salesType', EntityType::class, [
                'class' => SalesType::class
            ])
            ->add('update', SubmitType::class)
            ->getForm();
    }


    /**
     * @return string[]
     */
    protected function getDefaultParameters(array $query): array
    {
        return [
            'category' => StockCategory::PRODUCT,
            'salesType' => SalesType::DIRECT,
            'currency' => Currency::USD,
        ];
    }

    /**
     * @return AuditTable[]
     */
    public function getTables(array $params): array
    {
        $tables = [];

        $categoryFilter = empty($params['category']) ? '' :
            "where i.CategoryID = :category";

        $table = new RawSqlAudit('Stock item list', "
            select i.Description as Name
                , i.StockID as StockCode
                , i.LongDescription as Description
                , p.price as Price
            from StockMaster i
            left join Prices p on p.StockID = i.StockID
            $categoryFilter
            and p.TypeAbbrev = :salesType
            and p.CurrAbrev = :currency
            and i.Discontinued = 0
        ");

        $table->setScale('Price', 2);
        $tables[] = $table;

        return $tables;
    }

}
