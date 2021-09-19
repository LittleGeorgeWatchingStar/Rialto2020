<?php

namespace Rialto\Sales\Gauge\Web;

use Doctrine\DBAL\Connection;
use Rialto\Accounting\Period\Period;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class GaugeController extends RialtoController
{
    /**
     * @Route("/Sales/Gauge/",
     *   name="Sales_Gauge_show")
     * @Method("GET")
     */
    public function showAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);

        $basketCutoff = new \DateTime('-30 days');
        return $this->render('sales/gauge/show.html.twig', [
            'basketCutoff' => $basketCutoff,
            'basketDetails' => $this->getBasketDetails($basketCutoff),
        ]);
    }

    private function getBasketDetails(\DateTime $cutoff)
    {
        /* TODO: this used to be queries to the osCommerce database. */
        return [];
    }

    /**
     * @Route("/Sales/Gauge.json/",
     *   name="Sales_Gauge_data",
     *   defaults={"_format"="json"},
     *   options={"expose"="true"})
     * @Method("GET")
     */
    public function dataAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $today = date('Y-m-d');
        $thisPeriod = Period::fetchCurrent($this->dbm);

        $sql = "
        SELECT	SalesStage, SalesOrders.OrderType,
        COUNT(DISTINCT SalesOrders.OrderNo) AS Q,
        COUNT( DISTINCT( IF ( DateToShip!=0, SalesOrders.OrderNo,0)))-1 AS QQ,
        SUM( ( Quantity-QtyInvoiced) * finalUnitPrice  ) AS Total
        FROM SalesOrders
        LEFT JOIN SalesOrderDetails ON SalesOrderDetails.OrderNo=SalesOrders.OrderNo
        LEFT JOIN SalesTypes ON SalesTypes.TypeAbbrev=SalesOrders.OrderType
        WHERE SalesOrderDetails.Completed=0 AND SalesOrders.FromStkLoc = '7'
        GROUP BY SalesOrders.OrderType, SalesStage;
        ";
        /* @var $conn Connection */
        $conn = $this->get(Connection::class);

        $theSum = [];
        $types = [
            'OS' => 'osCommerce',
            'RM' => 'RMA',
            'DI' => 'webERP',
            'SUM' => 'Total',
        ];
        foreach ($types as $id => $name) {
            $theSum[$id] = 0;
        }
        $theAnswers = [];
        foreach ($conn->fetchAll($sql) as $row) {
            $qq = ucfirst($row['SalesStage']);
            $theAnswers[] = ['name' => $qq . '_' . $row['OrderType'] . '_' . 'Orders', 'value' => $row['Q']];
            $theAnswers[] = ['name' => $qq . '_' . $row['OrderType'] . '_' . 'Shippable', 'value' => $row['QQ']];
            $theAnswers[] = ['name' => $qq . '_' . $row['OrderType'] . '_' . 'Backlog', 'value' => $row['Total']];

            if ($row['SalesStage'] == SalesOrder::ORDER) {
                $theSum[$row['OrderType']] = $row['Total'];
            }
        }

        /* TODO: these used to be queries to the osCommerce database.
         * I've set them to zero until we can hook them up to another
         * storefront. */
        //	osCommerce total baskets and creation date < 1 week, 1 month, 3 months
        $theAnswers[] = ['name' => 'OnlineWithin30', 'value' => 0];
        //	osCommerce users last click within 10 minutes, 30 minutes, 60 minutes
        $theAnswers[] = ['name' => 'OnlineCount', 'value' => 0];

//	today's credit card transactions
        $sql = "SELECT SUM(Amount) AS TotalSweeps FROM CardTrans WHERE Posted = 0 AND dateCreated = :today";
        if (($row = $conn->fetchAssoc($sql, ['today' => $today]))) {
            $theAnswers[] = ['name' => 'TotalSweeps', 'value' => $row['TotalSweeps']];
        }

// sales MTD: online, weberp, budget online, budget weberp
        $sql = "SELECT  AccountCode, Actual, Budget
        FROM ChartDetails
        WHERE AccountCode IN ( 40000, 40001)
            AND Period = :period";
        foreach ($conn->fetchAll($sql, ['period' => $thisPeriod->getId()]) as $row) {
            switch ($row['AccountCode']) {
                case 40000:
                    $theAnswers[] = ['name' => 'mtdOEMActual', 'value' => -$row['Actual']];
                    $theAnswers[] = ['name' => 'mtdOEMBudget', 'value' => -$row['Budget']];
                    $theSum['DI'] -= $row['Actual'];
                    break;
                case 40001:
                    $theAnswers[] = ['name' => 'mtdOnlineActual', 'value' => -$row['Actual']];
                    $theAnswers[] = ['name' => 'mtdOnlineBudget', 'value' => -$row['Budget']];
                    $theSum['OS'] -= $row['Actual'];
                    break;
            }
        }

        foreach ($theSum as $key => $sum) {
            $theAnswers[] = ['name' => 'Order_' . $key . '_' . 'Total', 'value' => $sum];
        }

// backlog deliverable this month oscommerce, weberp
//	delivery interval queries (use webERP order - delivery dates)
        $sql = "SELECT COUNT(DISTINCT SalesOrders.OrderNo) AS Q
          , COUNT( DISTINCT( IF ( DateToShip!=0, SalesOrders.OrderNo,0)))-1 AS QQ
          , SalesTypes.Sales_Type, SalesOrders.OrderType
        FROM SalesOrders
        LEFT JOIN SalesOrderDetails ON SalesOrderDetails.OrderNo=SalesOrders.OrderNo
        LEFT JOIN SalesTypes ON SalesTypes.TypeAbbrev=SalesOrders.OrderType
        WHERE SalesOrderDetails.Completed=0
        AND SalesOrders.FromStkLoc = '7'
        AND SalesStage = '" . SalesOrder::ORDER . "'
        GROUP BY SalesOrders.OrderType";

//	webERP stock status for products
//  list of orders to assemble
        $sql = "SELECT DISTINCT  WorksOrders.StockID, StockMaster.Description, WorksOrders.OrderNo, WORef,UnitsReqd  FROM WorksOrders
                LEFT JOIN StockMaster ON StockMaster.StockID=WorksOrders.StockID
                WHERE WorksOrders.LocCode = 7 AND UnitsIssued>0 AND UnitsReqd>0 AND dateClosed IS NULL ORDER BY WorksOrders.StockID";

//  list of orders with stock at CM waiting for confirmation date by date since last shipment
        $sql = "SELECT DISTINCT  WorksOrders.StockID, Locations.LocationName, WorksOrders.OrderNo, WORef FROM WorksOrders
                LEFT JOIN Locations ON Locations.LocCode=WorksOrders.LocCode
                WHERE UnitsIssued>0 AND OrderNo!=0 AND UnitsReqd>0 AND dateClosed IS NULL AND CommitmentDate IS NULL ORDER BY WorksOrders.LocCode";

//  list of orders to kit
        $sql = "SELECT DISTINCT  WorksOrders.StockID, Locations.LocationName, WorksOrders.OrderNo, WORef FROM WorksOrders
                LEFT JOIN Locations ON Locations.LocCode=WorksOrders.LocCode
                WHERE UnitsIssued>0 AND OrderNo!=0 AND UnitsReqd>0 AND dateClosed IS NULL AND CommitmentDate IS NULL ORDER BY WorksOrders.LocCode";

//	number of orders to kit, orders to confirm
        $sql = "SELECT WorksOrders.LocCode, Locations.LocationName, Flags,
            Count(*) AS Q
            FROM WorksOrders
            LEFT JOIN Locations ON Locations.LocCode = WorksOrders.LocCode
            LEFT JOIN StockMaster ON WorksOrders.StockID = StockMaster.StockID
            WHERE Flags!='PKG' AND UnitsIssued=0 AND OrderNo!=0
            AND UnitsReqd>0 AND dateClosed IS NULL
            GROUP BY WorksOrders.LocCode";

//	pending orders to place
        $sql = "SELECT DISTINCT (PurchOrders.OrderNo) FROM PurchOrders
	        LEFT JOIN PurchOrderDetails ON PurchOrders.OrderNo=PurchOrderDetails.OrderNo
	        WHERE Initiator != 'WOSystem' AND Completed=0 AND DatePrinted IS NULL";

//  pending orders to recieve
        $sql = "SELECT DISTINCT (PurchOrders.OrderNo) FROM PurchOrders
                LEFT JOIN PurchOrderDetails ON PurchOrders.OrderNo=PurchOrderDetails.OrderNo
                WHERE Initiator != 'WOSystem' AND Completed=0 AND DatePrinted IS NOT NULL AND QuantityOrd IS NOT NULL";

//	pending invoices in the email
//  users on gumstix.org
//  users on the wiki
//	articles on google


        $additions = [];
        foreach ($theAnswers as $pairs) {
            $additions[] = [
                'name' => 'F_' . $pairs['name'],
                'value' => number_format($pairs['value'], 0),
                'type' => 'text',
            ];
        }
        foreach ($additions as $toAdd) {
            $theAnswers[] = $toAdd;
        }
        return new JsonResponse([
            'identifier' => 'name',
            'label' => 'name',
            'items' => $theAnswers,
        ]);
    }

}
