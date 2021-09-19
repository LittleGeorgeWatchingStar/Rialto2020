<?php

namespace Rialto\Sales\Web\Report;

use Doctrine\DBAL\Connection;
use Rialto\Accounting\Period\Period;
use Rialto\Stock\Item\StockItemAttribute;

/**
 * Reports how many LCD displays were sold.
 *
 * Apparently there are tax laws related to LCD displays.
 */
class LcdDisplayReport
{
    /**
     * The minimum screen size that is taxable, in inches.
     * @var int
     */
    const MIN_TAXABLE_SCREEN_SIZE = 4;

    private $data = [];

    /** @var Connection */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadData(Period $start, Period $end)
    {
        $sql = "
            SELECT
                sysType.TypeName as typeName,
                -SUM(move.quantity) as qtySold,
                case
                    when address.stateCode in ('CA', 'California') then customer.StateStatus
                    when address.stateCode is null then ''
                    else 'Out of state'
                end as taxStatus
            FROM StockMove move
            join Accounting_Transaction glTrans
                on move.transactionId = glTrans.id
            JOIN SysTypes sysType
                ON glTrans.sysType = sysType.TypeID
            LEFT JOIN DebtorTrans t
                on glTrans.id = t.transactionId
            LEFT JOIN DebtorsMaster customer
                ON customer.DebtorNo = t.customerID
            LEFT JOIN SalesOrders so
                on so.OrderNo = t.Order_
            LEFT JOIN Geography_Address address
              on so.shippingAddressID = address.id
            JOIN StockMaster item
                ON move.stockCode = item.StockID
            JOIN StockItemAttribute attr
                ON item.StockID = attr.stockCode
            WHERE move.periodID >= :startPeriod
              AND move.periodID <= :endPeriod
              AND attr.attribute = :lcdDisplaySize
              AND attr.value >= :minSize
            GROUP BY typeName, taxStatus";

        $params = [
            'startPeriod' => $start->getId(),
            'endPeriod' => $end->getId(),
            'lcdDisplaySize' => StockItemAttribute::LCD_SIZE,
            'minSize' => self::MIN_TAXABLE_SCREEN_SIZE,
        ];
        $stmt = $this->conn->executeQuery($sql, $params);
        $this->data = $stmt->fetchAll();
    }

    public function getItems()
    {
        return $this->data;
    }

    public function getMinTaxableSize()
    {
        return self::MIN_TAXABLE_SCREEN_SIZE;
    }
}
