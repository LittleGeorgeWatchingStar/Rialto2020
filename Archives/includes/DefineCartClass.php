<?php

use Rialto\SalesBundle\Entity\Customer;
use Rialto\ShippingBundle\Model\SalesOrderShipment;
use Rialto\ShippingBundle\Model\ShipmentPackage;
use Rialto\SalesBundle\Model\SalesOrderItem;

use Rialto\StockBundle\Entity\StockItem;
use Rialto\CoreBundle\Database\ErpDbManager;

use Rialto\SalesBundle\Model\ICustomer;
use Rialto\GeographyBundle\Model\Country;
require_once 'gumstix/geography/IAddress.php';

class Cart
implements IAddress, SalesOrderShipment
{
    var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
    var $total; /*total cost of the items ordered */
    var $totalVolume;
    var $totalWeight;
    var $ItemsOrdered; /*no of different line items ordered */
    var $DeliveryDate;
    var $DefaultSalesType;
    var $SalesTypeName;
    var $DefaultCurrency;
    var $DeliverTo;
    var $Addr1;
    var $Addr2;
    var $City;
    var $State;
    var $Zip;
    var $Country;
    var $PhoneNo;
    var $Email;
    var $CustRef;
    var $Comments;
    var $Location;

    var $DebtorNo;
    var $CustomerName;
    var $Orig_OrderDate;
    var $Branch;
    var $TransID;
    var $ShipVia;
    var $FreightCost;
    Var $OrderNo;
    Var $Consignment;
    var $ShipmentType;

    /* A unique id for this cart to prevent data corruption */
    private $cart_id;

    function Cart(){
        /*Constructor function initialises a new shopping cart */
        $this->LineItems = array();
        $this->total=0;
        $this->ItemsOrdered=0;
        $this->DefaltSalesType="";
        $this->cart_id = uniqid();
    }

    function add_to_cart(
        $StockID,
        $Qty,
        $Descr,
        $Price,
        $Disc,
        $DiscAcct,
        $UOM,
        $Volume,
        $Weight,
        $QOHatLoc=0,
        $MBflag='B',
        $ActDispatchDate=NULL,
        $QtyInvoiced=0,
        $DiscCat='',
        $Controlled=0,
        $Serialised=0,
        $DecimalPlaces=0,
        $Narrative='',
        $Custom,
        $CustomizationID,
        $UpdateDB='No')
    {
        if (isset($StockID) AND $StockID!="" AND $Qty>0 AND isset($Qty))
        {
            if ($Price<0){ /*madness check - use a credit note to give money away!*/
                $Price=0;
            }

            $this->LineItems[$StockID] = new LineDetails($StockID,
            $Descr,
            $Qty,
            $Price,
            $Disc,
            $DiscAcct,
            $UOM,
            $Volume,
            $Weight,
            $QOHatLoc,
            $MBflag,
            $ActDispatchDate,
            $QtyInvoiced,
            $DiscCat,
            $Controlled,
            $Serialised,
            $DecimalPlaces,
            $Narrative,
            $Custom,
            $CustomizationID
            );
            $this->ItemsOrdered++;

            if ($UpdateDB=='Yes'){
                /*ExistingOrder !=0 set means that an order is selected or created for entry
                 of items - ExistingOrder is set to 0 in scripts that should not allow
                 adding items to the order - New orders have line items added at the time of
                 committing the order to the DB in DeliveryDetails.php
                 GET['ModifyOrderNumber'] is only set when the items are first
                 being retrieved from the DB - dont want to add them again - would return
                 errors anyway */

                global $db;
                $sql = "INSERT INTO SalesOrderDetails (OrderNo,
                                            StkCode,
                                            Quantity,
                                            UnitPrice,
                                            DiscountPercent,
                                            DiscountAccount)
                                VALUES(" . $_SESSION['ExistingOrder'] . ",
                                    '" . $StockID ."',
                                    " . $Qty . ",
                                    " . $Price . ",
                                    " . $Disc . ", $DiscAcct )";
                $result = DB_query($sql,
                $db ,
                _('The order line for') . ' ' . $StockID . ' ' ._('could not be inserted'));
            }

            Return 1;
        }
        Return 0;
    }

    /**
     * @return Customer
     */
    function fetchCustomer()
    {
        return Customer::fetch($this->DebtorNo);
    }

    public function getCity()
    {
        return $this->DelCity ? $this->DelCity : $this->City;
    }

    public function getComments()
    {
        return $this->Comments;
    }

    public function getDeliveryCompany()
    {
        return $this->DelCompanyName ? $this->DelCompanyName : $this->CompanyName;
    }

    public function getDeliveryName()
    {
        return $this->DeliverTo;
    }

    /**
     * Returns this object, which implements IAddress.
     *
     * @return IAddress
     */
    public function getDeliveryAddress()
    {
        return $this;
    }

    public function getCountryCode()
    {
        $country = $this->DelCountry ? $this->DelCountry : $this->Country;
        return Country::resolveCountryCode( $country );
    }

    public function getCountryName()
    {
        $country = $this->DelCountry ? $this->DelCountry : $this->Country;
        return Country::resolveCountryName( $country );
    }

    /**
     * Returns this object, which implements ICustomer.
     *
     * @return ICustomer
     */
    public function getCustomer()
    {
        return $this;
    }

    public function getShippingCost()
    {
        return $this->FreightCost;
    }

    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * Returns the cart_id of this shopping cart.  The integrity of the cart
     * can be ensured by passing the cart_id along in the request data (POST
     * or GET) and comparing it at every step to the id of the cart stored in
     * the session.
     * @return string  The unique ID of this cart.
     */
    public function getId()
    {
        return $this->cart_id;
    }

    public function getLineItems()
    {
        return $this->LineItems;
    }

    public function getSubtotalPrice()
    {
        $total = 0;
        foreach ( $this->getLineItems() as $item )
        {
            $total += $item->getExtendedPrice();
        }
        return $total;
    }

    public function getTrackingNumber()
    {
        return $this->Consignment;
    }

    public function getMailStop()
    {
        return '';
    }

    public function getName()
    {
        return $this->DeliverTo; // TODO: CustomerName?
    }

    public function getOrderNumber()
    {
        return $this->OrderNo;
    }

    public function getContactPhone()
    {
        return $this->PhoneNo;
    }

    public function getPostalCode()
    {
        return $this->DelZip ? $this->DelZip : $this->Zip;
    }

    public function getContactName()
    {
        return $this->DeliverTo;
    }

    public function getStateCode()
    {
        $cc = $this->getCountryCode();
        if (! $cc ) {
            prnMsg('Invalid country name '. $this->getCountryName(), 'warn');
            return null;
        }
        $country = new Country( $cc );
        if (! $country ) {
            prnMsg("Invalid country code '$cc'", 'warn');
            return null;
        }
        $state = $this->DelState ? $this->DelState : $this->State;
        return $country->resolveStateCode( $state );
    }

    public function getStateName()
    {
        $cc = $this->getCountryCode();
        if (! $cc ) {
            prnMsg('Invalid country name '. $this->getCountryName(), 'warn');
            return null;
        }
        $country = new Country( $cc );
        if (! $country ) {
            prnMsg("Invalid country code '$cc'", 'warn');
            return null;
        }
        $state = $this->DelState ? $this->DelState : $this->State;
        return $country->resolveStateName( $state );
    }

    public function getStreet1()
    {
        return $this->DelAddr1 ? $this->DelAddr1 : $this->Addr1;
    }

    public function getStreet2()
    {
        return $this->DelAddr2 ? $this->DelAddr2 : $this->Addr2;
    }

    public function getTotalWeight()
    {
        $total = 0;
        foreach ( $this->getLineItems() as $item )
        {
            $total += $item->getTotalWeight();
        }
        return $total;
    }

    public function getWeight()
    {
        return $this->totalWeight;
    }

    public function isReplacement()
    {
        return $this->SalesTypeName == 'RM';
    }

    function update_cart_item(
        $UpdateItem,
        $Qty,
        $Price,
        $Disc,
        $DiscAcct,
        $Narrative,
        $Custom,
        $CustomizationID,
        $UpdateDB='No')
    {
        if ($Qty>0){
            $this->LineItems[$UpdateItem]->Quantity = $Qty;
        }
        $this->LineItems[$UpdateItem]->Price = $Price;
        $this->LineItems[$UpdateItem]->DiscountPercent = $Disc;
        $this->LineItems[$UpdateItem]->DiscountAccount = $DiscAcct;
        $this->LineItems[$UpdateItem]->Narrative = $Narrative;
        $this->LineItems[$UpdateItem]->Custom= $Custom;
        $this->LineItems[$UpdateItem]->CustomizationID= $CustomizationID;

        if ($UpdateDB=='Yes'){
            global $db;
            $sql = "UPDATE    SalesOrderDetails SET
                    Quantity=" . $Qty . ",
                    UnitPrice=" . $Price . ",
                    DiscountPercent=" . $Disc . ",
                    DiscountAccount=$DiscAcct,
                    Custom='" . $Custom . "',
                    CustomizationID='" . $CustomizationID . "',
                    Narrative ='" . $Narrative . "'
                WHERE OrderNo=" . $_SESSION['ExistingOrder'] . " AND StkCode='" . $UpdateItem . "'";
            $result = DB_query ( $sql, $db, 'The order line for ' . $UpdateItem . ' could not be updated');
        }
    }

    function remove_from_cart(&$StockID,$UpdateDB='No'){
        if (isset($StockID)){
            unset($this->LineItems[$StockID]);
            $this->ItemsOrdered--;
        }
        if ($UpdateDB=='Yes'){
            global $db;
            $result = DB_query("DELETE FROM SalesOrderDetails
                        WHERE OrderNo=" . $_SESSION['ExistingOrder'] . "
                        AND StkCode='" . $StockID ."'",
            $db,
            _('The order line for') . ' ' . $StockID . ' ' . _('could not be deleted'));
        }
    }

    function Get_StockID_List(){
        /* Makes a comma seperated list of the stock items ordered
         for use in SQL expressions */

        $StockID_List="";
        foreach ($this->LineItems as $StockItem) {
            $StockID_List .= ",'" . $StockItem->StockID . "'";
        }

        return substr($StockID_List, 1);

    }

    function Any_Already_Delivered(){
        /* Checks if there have been deliveries of line items */

        foreach ($this->LineItems as $StockItem) {
            if ($StockItem->QtyInv !=0){
                return 1;
            }
        }

        return 0;

    }

    function Some_Already_Delivered($StockID){
        /* Checks if there have been deliveries of a specific line item */

        if ($this->LineItems[$StockID]->QtyInv !=0){
            return 1;
        }
        return 0;
    }

    public function getSalesOrder()
    {
        return $this;
    }

    public function getShippingMethod()
    {
        if (! $this->ShipmentType ) return null;
        $shipper = $this->getShipper();
        return $shipper ? $shipper->getShippingMethod($this->ShipmentType) : null;
    }

    public function getShippingPrice()
    {
        return $this->FreightCost;
    }

    public function getPackages()
    {
        return array(
            new ShipmentPackage($this->getTotalWeight()),
        );
    }

    public function getShipper()
    {
        if (! $this->ShipVia ) return null;
        $dbm = ErpDbManager::getInstance();
        return $dbm->need('shipping\Shipper', $this->ShipVia);
    }

} /* end class Cart */



class LineDetails
implements SalesOrderItem
{
    Var $StockID;
    Var $ItemDescription;
    Var $Quantity;
    Var $Price;
    Var $DiscountPercent;
    Var $DiscountAccount;
    Var $Units;
    Var $Volume;
    Var $Weight;
    Var $ActDispDate;
    Var $QtyInv;
    Var $QtyDispatched;
    Var $StandardCost;
    Var $QOHatLoc;
    Var $MBflag;    /*Make Buy Dummy, Assembly or Kitset */
    Var $DiscCat; /* Discount Category of the item if any */
    Var $TaxRate;
    Var $Controlled;
    Var $Serialised;
    Var $DecimalPlaces;
    Var $SerialItems;
    Var $Custom;
    Var $CustomizationID;
    Var $Narrative;

    /**
     * Constructor function to add a new LineDetail object with passed params.
     */
    function LineDetails(
        $StockItem,
        $Descr,
        $Qty,
        $Prc,
        $DiscPercent,
        $DiscAccount,
        $UOM,
        $Volume,
        $Weight,
        $QOHatLoc,
        $MBflag,
        $ActDispatchDate,
        $QtyInvoiced,
        $DiscCat,
        $Controlled,
        $Serialised,
        $DecimalPlaces,
        $Narrative,
        $Custom,
        $CustomizationID )
    {
        $this->StockID =$StockItem;
        $this->ItemDescription = $Descr;
        $this->Quantity = $Qty;
        $this->Price = $Prc;
        $this->DiscountPercent = $DiscPercent;
        $this->DiscountAccount = $DiscAccount;
        $this->Units = $UOM;
        $this->Volume = $Volume;
        $this->Weight = $Weight;
        $this->ActDispDate = $ActDispatchDate;
        $this->QtyInv = $QtyInvoiced;
        if ($Controlled==1){
            $this->QtyDispatched =0;
        } else {
            $this->QtyDispatched = $Qty - $QtyInvoiced;
        }
        $this->QOHatLoc = $QOHatLoc;
        $this->MBflag = $MBflag;
        $this->DiscCat = $DiscCat;
        $this->Controlled = $Controlled;
        $this->Serialised = $Serialised;
        $this->DecimalPlaces = $DecimalPlaces;
        $this->SerialItems = array();
        $this->Narrative = $Narrative;
        $this->Custom= $Custom;
        $this->CustomizationID= $CustomizationID;
    }

    public function getStockCode()
    {
        return $this->StockID;
    }

    public function getStockItem()
    {
        return StockItem::fetch($this->StockID);
    }

    public function getDescription()
    {
        return $this->ItemDescription;
    }

    public function getEccnCode()
    {
        return '';
    }

    /**
     * Returns the price of a single unit of this item, with any discounts
     * factored in.
     *
     * @return double
     */
    public function getDiscountedUnitPrice()
    {
        return $this->Price * (1 - $this->DiscountPercent);
    }

    /**
     * Returns the total price of this line item, with any discounts factored
     * in.
     *
     * @return double
     */
    public function getDiscountedTotalPrice()
    {
        return $this->QtyDispatched * $this->Price * (1 - $this->DiscountPercent);
    }

    public function getQuantity()
    {
        return $this->QtyDispatched;
    }

    public function getExtendedPrice()
    {
        return $this->getDiscountedUnitPrice() * $this->getQuantity();
    }

    public function getTotalWeight()
    {
        return $this->QtyDispatched * $this->Weight;
    }

    public function getFullUnitPrice()
    {
       return $this->Price;
    }

    public function getCountryOfOrigin()
    {
        return $this->getStockItem()->getCountryOfOrigin();
    }

    public function getHarmonizationCode()
    {
        return $this->getStockItem()->getHarmonizationCode();
    }

    public function getWeight()
    {
        return $this->getStockItem()->getWeight();
    }

    public function hasWeight()
    {
        return $this->getStockItem()->hasWeight();
    }
}
