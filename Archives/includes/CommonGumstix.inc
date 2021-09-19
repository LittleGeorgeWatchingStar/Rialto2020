<?php

use Rialto\PurchasingBundle\Entity\PurchasingData;

include_once("includes/labels.inc");
include_once("includes/SQL_CommonFunctions.inc");
require_once 'includes/work_order_requirements_db.inc';

function plural_form ($q, $u ) {
	$q_str = ($q<=0) ? 'no' : number_format($q,0);
	switch ($q_str) {
		case 1:		return 'is 1 ' . $u;
		case 'no':
		default:	return 'are ' . $q_str .  ' ' . $u .'s';
	}
}

function user_price_dec() {
	return 2;
}

function user_qty_dec() {
        return 2;
}


function GetReceipts_AttachedToSalesOrder( $SalesOrderNo, $db ) {
	$sql = "SELECT * FROM DebtorTrans WHERE Type=12 AND Order_='$SalesOrderNo'";
	$ret = DB_query( $sql, $db );
	return $ret;
}

function GetInvoices_AttachedToSalesOrder( $SalesOrderNo, $db ) {
        $sql = "SELECT * FROM DebtorTrans WHERE Type=10 AND Order_='$SalesOrderNo'";
        $ret = DB_query( $sql, $db );
        return $ret;
}

function Get_SalesOrder_Net_Status( $SalesOrderNo, $db ) {
        //
        //      First find all receipts that were originally assigned ot this sales order
        //      Remembering that receipts are tracked as a negative OvAmount
        //
        $payments_array = GetReceipts_AttachedToSalesOrder( $OrderNo, $db);
        $total_payments = 0;
        if ($payments_array) {
                while ($this_payment = DB_fetch_array($payments_array)) {
                        $total_payments += $this_payment['OvAmount'];
                }
        }
        //
        //      Next subtract all invoices previously issued against this sales order
        //
        $invoices_array = GetInvoices_AttachedToSalesOrder( $OrderNo, $db);
        $total_invoices = 0;
        if ($invoices_array) {
                while ($this_invoice = DB_fetch_array($invoices_array)) {
                        $total_invoices += $this_invoice['OvAmount'];
                }
        }
	return ( -$total_invoices - $total_payments);
}

function GetOpenSalesOrdersForCustomer( $CustomerID, $db, $BranchCode ) {
	$sql = " SELECT Distinct SalesOrders.* FROM SalesOrders
		 LEFT JOIN SalesOrderDetails ON SalesOrderDetails.OrderNo = SalesOrders.OrderNo
		 WHERE DebtorNo='$CustomerID' AND BranchCODE='$BranchCode' AND Completed=0";
        $ret = DB_query( $sql, $db );
        if (DB_num_rows($ret)==0) {
                return false;
        } else {
                return $ret;
        }
}

function SelectSalesOrderForCustomer($listControlName, $db, $CustomerID, $BranchCode, $selectedID ) {

	if ($result = GetOpenSalesOrdersForCustomer( $CustomerID, $db, $BranchCode ) ) {
	   echo "<SELECT NAME=$listControlName>";
	   while ($row = DB_fetch_array($result)) {
                        if ($selectedID==$row['OrderNo']) {
                                echo "<OPTION selected value=" . $row['OrderNo'] . ">";
                        } else {
                                echo "<OPTION value=" . $row['OrderNo'] . ">";
                        }
                        echo $row['OrderNo'];
	   }
           echo "</SELECT>";
	} else {
	   echo "No Open Orders";
	}
}

function ListSalesTypes( $db ) {
	$to_return = array();
	$sql = "SELECT TypeAbbrev, Sales_Type FROM SalesTypes";
	$ret = DB_query( $sql, $db );
	while ($row = DB_fetch_array( $ret ) ) {
		$to_return[$row['TypeAbbrev']] = $row['Sales_Type'];
	}
	return $to_return;
}

function GetWOfromPO( $TransNo, $db) {
        $sql = "SELECT RequisitionNo FROM PurchOrders WHERE Initiator='WOSystem' AND OrderNo='$TransNo'";
        $ret = DB_query( $sql, $db );
        if (DB_num_rows($ret)!=1) {
                return false;
        } else {
                $ret = DB_fetch_array( $ret) ;
                return $ret['RequisitionNo'];
        }
}

function GetWORefFromIssueNo( $TransNo, $db) {
	$sql = "SELECT WorkOrderID FROM WOIssues WHERE IssueNo='$TransNo'";
        $ret = DB_query( $sql, $db );
        if (DB_num_rows($ret)!=1) {
                return false;
        } else {
                $ret = DB_fetch_array( $ret) ;
                return $ret['WorkOrderID'];
        }
}

function GetPOFromPODetailID( $TransNo, $db) {
        $sql = "SELECT OrderNo FROM PurchOrderDetails WHERE PODetailItem='$TransNo'";
        $ret = DB_query( $sql, $db );
        if (DB_num_rows($ret)!=1) {
                return false;
        } else {
                $ret = DB_fetch_array( $ret) ;
                return $ret['OrderNo'];
        }
}

function GetPO_AttachedToWO( $WOID, $db ) {
	$sql = 'SELECT OrderNo FROM WorksOrders WHERE WORef="' . $WOID .  '"';
//	$sql = "SELECT OrderNo FROM PurchOrders WHERE Initiator='WOSystem' AND RequisitionNo='$WOID' ORDER BY OrderNo DESC";
	$ret = DB_query( $sql, $db );
	if (DB_num_rows($ret)!=1) {
		return false;
	} else {
		$ret = DB_fetch_array( $ret) ;
		return $ret['OrderNo'];
	}
}

function GetBuildLocation ($WOID, $db ) {
	$sql = "SELECT * FROM WorksOrders WHERE WORef='$WOID'";
	$row = DB_fetch_array( DB_query( $sql, $db ));
	echo 'building at ' . $row['LocCode'] . '<BR>';
	return $row['LocCode'];
}

function GetParentBuild( $WOID, $db ) {
	$to_return = array();
	$sql = "SELECT * FROM WorksOrders WHERE WORef='$WOID'";
	$ret = DB_query( $sql, $db );
	if ( $row = DB_fetch_array( $ret ) ) {
		$to_return['ParentBuild'] = $row['ParentBuild'];
		$to_return['BuildLocation'] = GetBuildLocation( $row['ParentBuild'], $db );
		echo 'building parent (' . $row['ParentBuild'] . ')  at ' . $to_return['BuildLocation'];
		return $to_return;
	} else {
		return false;
	}
}

function GetInventoryAcctFromStockID( $StockID, $db ) {
	$sql = "SELECT StockAct FROM StockCategory
		LEFT JOIN StockCategory ON StockMaster.CategoryID =StockCategory.CategoryID
		WHERE StockID='$StockID'";
	if ($ret = DB_fetch_array(DB_query($sql,$db))) {
		return $ret['StockAct'];
	} else {
		return false;
	}
}

function GetAccountList($db)    {
        $accounts_list = array();
        $sql = 'SELECT AccountCode, AccountName FROM ChartMaster ORDER BY AccountCode';
        $Account = DB_query($sql,$db);
        while ($myrow=DB_fetch_array($Account,$db)){
                if ($myrow['AccountCode'] >= 60000) {
                        $accounts_list[$myrow['AccountCode'] . ' ' . $myrow['AccountName']] = $myrow['AccountCode']. ' ' . $myrow['AccountName'];
                }
        }
        return $accounts_list;
}

/**
 * @deprecated  Use Supplier::getName() instead.
 */
function GetSupplierName($suppID, $db)   {
        $sql = 'SELECT SuppName FROM Suppliers WHERE SupplierID="' . $suppID . '"';
        $res = DB_fetch_array( DB_query($sql,$db));
	return $res['SuppName'];
}

function GetSupplierList($db)   {
        $supplier_list = array();
	$supplier_list['-'] = 'N/A';
        $sql = 'SELECT SupplierID, SuppName FROM Suppliers ORDER BY SuppName';
        $Account = DB_query($sql,$db);
        while ($myrow=DB_fetch_array($Account,$db)){
                $supplier_list[ /*$myrow['SupplierID'] . ' ' . */$myrow['SuppName']] = $myrow['SupplierID'];
        }
        return $supplier_list;
}

function GetLocationAddress( $LocCode, $db ) {
	$sql = "SELECT * FROM Locations WHERE LocCode='$LocCode'";
	$ret = DB_fetch_array( DB_query( $sql, $db ) );
	return $ret;
}

function GetSupplierLocCode($SupplierNo, $db)   {
        $sql = "SELECT LocCode FROM Locations WHERE SupplierID='$SupplierNo'";
        if ($res = DB_fetch_array(DB_query($sql,$db))) {
		return $res['LocCode'];
	} else {
		return 0;
	}
}


function GetNextSerialNo( $db ) {
        return GetNextTransNo( 200, $db);
}


function getPackageType( $StockID, $db ) {
        $sql = "SELECT Package FROM StockMaster WHERE StockID='$StockID'";
        $ret = DB_query( $sql, $db);
	if ( $res = DB_fetch_array( $ret ) ) {
		return ( $res['Package']  );
	} else {
		return false;
	}
}

function PartsPer_cm( $Package ) {
	switch ($Package)  {
		case 'C0402': 	return (5.0/1.0);
		case 'R0402':	return (5.0/1.0);
                case 'C0603':   return (5.0/2.0);
		case 'LED0603': 	return (5.0/2.0);
		case 'R0603':	return (5.0/2.0);
                case 'C0805':   return (5.0/2.0);
		case 'LED0805': 	return (5.0/2.0);
		case 'R0805':	return (5.0/2.0);
	}
	return false;
}

/**
 * @deprecated  Use StockItem::isControlled() instead.
 */
function isControlledItem( $StockID, $db ) {
        $sql = "SELECT Controlled FROM StockMaster WHERE StockID='$StockID'";
        $ret = DB_query( $sql, $db);
	if ( $res = DB_fetch_array( $ret ) ) {
		return ( $res['Controlled'] != 0 );
	} else {
		return false;
	}
}

function CellFormat($number,$places) {
	if ($number !=0) return number_format($number,$places);
	return '';
}

function GetBadReels( $LocCode, $db ) {
        $toreturn = array();
        $sql = "SELECT SerialNo,StockID,Quantity FROM StockSerialItems WHERE Quantity<0 AND LocCode = '$LocCode' ORDER BY StockID, SerialNo ";
        $ret = DB_query($sql,$db);
        while ($res = DB_fetch_array($ret)) {
		$reel_info = array( 'StockID' => $res['StockID'], 'Quantity' => $res['Quantity'], 'LocCode' => $LocCode, 'id' => $res['SerialNo']   );
                $toreturn[$res['SerialNo']] = $reel_info;
        }
        return $toreturn;
}

function GetEmptyReels( $LocCode, $db ) {
        $toreturn = array();
        $sql = "SELECT SerialNo,StockID FROM StockSerialItems WHERE Quantity=0 AND LocCode = '$LocCode' ORDER BY StockID, SerialNo ";
        $ret = DB_query($sql,$db);
        while ($res = DB_fetch_array($ret)) {
                $toreturn[$res['SerialNo']] = $res['StockID'];
        }
        return $toreturn;
}

function SetVersionOnReel( $stock_id, $db, $loc_code, $id, $new_version ) {
	if ( ($stock_id!='') && ($id!='') && ($loc_code!='') ) {
		echo $sql = 'SELECT COUNT(*) FROM StockSerialItems  WHERE StockID="' .$stock_id. '" AND LocCode=' .$loc_code . ' AND SerialNo=' .$id;
		$ret = DB_query( $sql, $db );
		echo '<BR>Returning rows: ';
		$row = DB_fetch_row( $ret );
		echo $row[0] . '<BR>';
		if ( $row[0] != 1 ) {
			return false;
		} else {
			$versql = 'UPDATE StockSerialItems SET Version="' .$new_version . '" WHERE StockID="' .$stock_id. '" AND LocCode=' .$loc_code . ' AND SerialNo=' .$id;
			echo $versql . '<BR>';
		        return DB_query( $versql, $db );
		}
	}
	return false;
}

function GetVersionFromID( $stock_id, $db, $id, $loc_code ) {
	$versql = 'SELECT Version FROM StockSerialItems WHERE StockID="' .$stock_id. '" AND LocCode=' .$loc_code .' AND SerialNo=' .$id;
	$vers = DB_fetch_array( DB_query( $versql, $db ) );
	return $vers['Version'];
}

function GetAvailableReels( $StockID, $db, $LocCode, $Version=''  ) {
	$toreturn = array();
        $sql = "SELECT SerialNo,Quantity FROM StockSerialItems WHERE Quantity>0 AND StockID='$StockID' AND LocCode = '$LocCode'";
	if ( ( $Version !='') && ( is_numeric( $Version )) ) {
		$sql .= ' AND Version = ' . $Version;
	}
        $ret = DB_query($sql,$db);
	while ($res = DB_fetch_array($ret)) {
		$toreturn[$res['SerialNo']] = $res['Quantity'];
	}
	return $toreturn;
}


/**
 * @deprecated  Use StockItem::MBflag instead.
 */
function GetMBflag($StockID, $db) {
        $sql = "SELECT MBflag FROM StockMaster WHERE StockID='$StockID'";
	$res = DB_fetch_array(DB_query($sql,$db));
	return $res['MBflag'];
}

function GetProductName($StockID, $db) {
        $sql = "SELECT Description FROM StockMaster WHERE StockID='$StockID'";
	$res = DB_fetch_array(DB_query($sql,$db));
	return $res['Description'];
}

function GetProductUPC($StockID, $db) {
        $sql = "SELECT BarCode FROM StockMaster WHERE StockID='$StockID'";
	$res = DB_fetch_array(DB_query($sql,$db));
	return $res['BarCode'];
}

function GetComponentsOf($ParentID,$db) {
        $toreturn = array();
        $res = DB_query("SELECT * FROM BOM WHERE Parent='$ParentID'", $db);
        if (DB_num_rows($res)==0) {
                return 0;
        } else {
                while ($this_component=DB_fetch_array($res)) {
                        $toreturn[$this_component['Component']] += $this_component['Quantity'];
                        $to_add_toreturn = GetComponentsOf($this_component['Component'],$db);
                        if (is_array($to_add_toreturn)) {
                                foreach ($to_add_toreturn as $id => $qty) {
                                        $toreturn[$id] += $qty;
                                }
                        }
                }
        }
        return $toreturn;
}

function LabelLink($LabelLine1, $LabelLine2, $LabelLine3, $BoxID ) {
	$target_URL = 'http://shipstix.gumstix.com/shipstix/labelit_2.php?';
	$label_URL  = 'ITERATIONS=1' .
		'&LABEL_TEXT_1=' . rawurlencode( $LabelLine1) .
		'&LABEL_TEXT_2=' . rawurlencode( $LabelLine2 ) .
		'&LABEL_TEXT_3=' . rawurlencode(  $LabelLine3 ) .
		'&BARCODE_TEXT=' . rawurlencode(  $BoxID ) ;
	return $target_URL . $label_URL ;
}

function ProductLabelLink($StockID, $db, $Iterations ) {
	$target_URL = 'http://shipstix.gumstix.com/shipstix/labelit_2.php?';
	$label_URL  = "ITERATIONS=$Iterations" .
                '&LABEL_TEXT_2=' . rawurlencode( GetProductName($StockID, $db) ) ;
//		'&LABEL_TEXT_2=' . rawurlencode( $StockID );
//		Add the following line in when we begin using barcodes...
//		'&BARCODE_TEXT=' . rawurlencode(  GetProductUPC($StockID, $db) ) ;
	return $target_URL . $label_URL ;
}

function PrintLabel( $id, $db, $qty, $print_loc ) {
    $sql = "SELECT * FROM StockMaster WHERE StockID='$id'";
    $ret =  DB_query($sql, $db);
    $vals = DB_fetch_array( $ret );
    $MainLine =  $vals['StockID'] . '  #' . number_format( $qty,0) ;
    $Line2  = $vals['Description'];
    $Line3  = 'No ID#    ' . Date( 'Y-m-d' );

    echo $MainLine . '<BR>';

    $theURL = LabelLink( '', $MainLine, $Line2, $Line3 );
    //        echo '<A  target="_blank" HREF="' . $theURL . '"><center>Reprint ' . $SerialNo . '</A>';
    print_label('', $MainLine, $Line2, $Line3, $print_loc );
}

function PrintReelLabel( $SerialNo, $db, $print_loc, $locCode=7 ) {
    $sql = "SELECT * FROM StockSerialItems
            LEFT JOIN StockMaster ON StockMaster.StockID = StockSerialItems.StockID
            WHERE Quantity!=0 AND SerialNo = '$SerialNo' AND LocCode='$locCode'";
    $ret =  DB_query($sql, $db);
    if (DB_num_rows($ret)!=1) {
        echo 'Invalid Split <br>';
        return 0;
    }
    $vals = DB_fetch_array( $ret );
    $MainLine =  $vals['StockID'];
    if ( is_numeric( $vals['Version'])) {
        $MainLine .= '-R';
    }
    $MainLine .= $vals['Version'] . ' #' . number_format( $vals['Quantity'],0) ;
    $Line2  = $vals['Description'];
    $Line3  = $SerialNo . '    ' . Date( 'Y-m-d' );
    $theURL = LabelLink( '', $MainLine, $Line2, $Line3 );
//    echo '<A  target="_blank" HREF="' . $theURL . '"><center>Reprint ' . $SerialNo . '</A>';
    print_label('', $MainLine, $Line2, $Line3, $print_loc  );
}

function PrintStockLabels($StockID, $db, $print_loc=0 ) {
	$sql = "SELECT SerialNo FROM StockSerialItems WHERE StockID='$StockID' AND Quantity != 0";
	$ret = DB_query( $sql, $db );
	while ( $res = DB_fetch_array( $ret ) ) {
		PrintReelLabel( $res['SerialNo'], $db, $print_loc );
	}
}

function EchoSelectFromOptions( $label, $name, $choices, $incumbent='' ) {
        echo $label;
	echo '<SELECT NAME=' . $name . '>';
        foreach ($choices as $id => $choice ) {
                $existing =  ($id == $Incumbent) ? ' SELECTED ' : '';
                echo '<OPTION ' . $existing . ' VALUE=' . $id . '> ' . $choice ;
        }
        echo '</SELECT>';
}

function EchoSelectReel( $label, $db, $StockID, $LocCode, $Incumbent='' ) {
	$available_reels = GetAvailableReels($StockID, $db, $LocCode );
	echo "<SELECT NAME=$label>";
	foreach ($available_reels as $id => $qty ) {
		$Existing =  ($id == $Incumbent) ? ' SELECTED ' : '';
		echo "<OPTION $Existing VALUE=$id> $id ($qty)";
	}
	echo "</SELECT>";
}

function GetReelQuantity ( $SerialNo, $db ) {
        $sql = "SELECT Quantity FROM StockSerialItems WHERE Quantity!=0 AND SerialNo = '$SerialNo'";
        $ret =  DB_query($sql, $db);
        if (DB_num_rows($ret)!=1) {
                echo 'Invalid Reel<br>';
                return 0;
        }
        $vals = DB_fetch_array( $ret );
        return $vals['Quantity'];
}


/**
 * @deprecated  Use StockItem#getBuildType() instead.
 */
function GetBuildType($BuildStockID) {
    $dbm = ErpDbManager::getInstance();
    $item = $dbm->find('stock\StockItem', $BuildStockID);
    if (! $item ) return '';
    return $item->getBuildType();
}

function GetBuildName($VersionReference) {
	$names = split("-R",$VersionReference);
	return $names[0];
}

function GetBuildVersion($VersionReference) {
        $names = split("-R",$VersionReference);
        return '-R' . $names[1];
}

/**
 get_links($tinynode,$baseurl)
 return : list of the links
*/

function gl($t,$b) {
  $r=array();
  $c=count($t->child);
  for ($i=0;$i<$c;$i++) {
   $e=&$t->child[$i];
   if ($e->name=="a") { // a link
     $h=$e->attribute["href"]; // url
       $r[]=substr($h,0,1)=="/"?$h:$b.$h;
   } else { // not a link, search recursively inside
     $r=array_merge($r,gl($e,$b));
   }
  }
  return $r;
}

function SelectFromDirectory() {
	$baseurl = "http://weberp:saywhat@svn.gumstix.com/svn/gumstix-hardware/Production/ASM/";
	$tidy = new tidy;
	$tidy->parseFile($baseurl);
	$tidy->cleanRepair();
	$body = tidy_get_body($tidy);
	$r = gl($body,'');
	foreach ($r as $r_link) {
		$r_link = str_replace('/','',$r_link);
		if (GetBuildType($r_link) !='') {
			echo "<OPTION VALUE='$r_link'>$r_link";
		}
	}
        echo "<OPTION SELECTED VALUE=''> ";

}

function ColorCodeOf( $BinStyleCode ) {
        switch ($BinStyleCode) {
                case 'Tube':    $ret = "RED";		break;
                case 'Reel7':   $ret = "GREEN";		break;
                case 'Reel13':  $ret = "BLACK";		break;
                case 'Tray':    $ret = "YELLOW";	break;
                case 'Pouch':   $ret = "BLUE";		break;
        }
        return $ret;
}

function BinStyleSelect( $label, $name, $starting_value='' ) {
    $BinStyles = PurchasingData::getBinStyles();
    /* Include blank as the first option */
    array_unshift($BinStyles, '');
    $Options   = array( );
    foreach ($BinStyles as $bs) {
        $Options[$bs] = $bs;
    }
    Input_Option( $label, $name, $Options, $starting_value );
}

function BinStyleOf( $ID, $db ) {
        $sql = "SELECT BinStyle FROM StockSerialItems WHERE SerialNo='$ID'";
        $res = DB_fetch_array( DB_query( $sql, $db) );
        return $res['BinStyle'];
}

function get_BinStyle( $StockID, $db, $SupplierID ) {
	$sql = "SELECT BinStyle FROM PurchData WHERE StockID='$StockID' ORDER BY ABS(SupplierNo-" .$SupplierID . ")" ;
	$res = DB_fetch_array( DB_query( $sql, $db) );
        return $res['BinStyle'];
}

function HasCustomizations( $SelectedParent, $db ) {
	$sql = "SELECT Count(CustomizationID) FROM Customizations WHERE ParentID='$SelectedParent'";
	$res = DB_fetch_row( DB_query( $sql, $db ) );
	return ($res[0]>0);
}

/**
 * @deprecated  Use WorkOrder#hasCustomizations() instead.
 */
function WO_HasCustomizations( $woid, $db ) {
	$sql = 'SELECT CustomizationID FROM WorksOrders WHERE WORef=' . $woid;
	$res = DB_fetch_array( DB_query( $sql, $db) );
	return ( ( $res['CustomizationID'] != 0 ) ? $res['CustomizationID'] : false );
}

function GetCustomizationPriceChange( $CustomizationID, $db ) {
	if ( ($CustomizationID=='') OR ($CustomizationID==0)) {
		return 0;
	} else {
		$sql = 'SELECT SUM(PriceChange) FROM Customizations
			LEFT JOIN Substitutions ON Customizations.SubstitutionID=Substitutions.ID
			WHERE Customizations.CustomizationID=' . $CustomizationID;
		$res = DB_fetch_row( DB_query( $sql, $db ));
		echo 'Please take into account a price change for the customer of ' . $res['0'] ./* ' == ' . $sql . */ '<br>';
		return 0;	//	$res['0'];
	}
}

function TypeNoName( $TypeNo, $db, $Type ) {
        $sql = "SELECT Name FROM Names WHERE TypeNo='$TypeNo' AND Type='$Type'";
        $ret = DB_fetch_array( DB_query( $sql, $db ) );
        return $ret['Name'];
}

function NameTheTypeno( $TypeNo, $db, $Type, $NewName ) {
        $sql = "SELECT Count(Name) FROM Names WHERE TypeNo='$TypeNo' AND Type='$Type'";
        $ret = DB_fetch_row( DB_query( $sql, $db ) );
        if ($ret[0]==0) {
                $sql="INSERT INTO Names (Type,TypeNo,Name) VALUES ('$Type','$TypeNo','$NewName')";
        } else {
                $sql="UPDATE Names SET Name='$NewName' WHERE Type='$Type' AND TypeNo='$TypeNo'";
        }
        $res = DB_query( $sql, $db );
}

function IsCustomized( $CustomizationID, $db, $ID ){
	$sql = "SELECT COUNT(*) AS Counter FROM Customizations WHERE CustomizationID='$CustomizationID' AND SubstitutionID='$ID'";
	$ret = DB_fetch_array( DB_query( $sql, $db) );
	return ($ret['Counter']!=0);
}

function PossibleSubstitutions( $SelectedParent, $db ) {
	$sub_sql = "    SELECT * FROM BOM
			LEFT JOIN Substitutions
			ON BOM.Component=Substitutions.ComponentID AND (Substitutions.ParentID=BOM.Parent OR Substitutions.ParentID='')
			WHERE BOM.Parent='$SelectedParent' AND ComponentID IS NOT NULL";
	$ErrMsg = _('Could not retrieve the BOM components because');
	$DbgMsg = _('The SQL used to retrieve the components was');
	$sub_result = DB_query($sub_sql,$db,$ErrMsg,$DbgMsg);
	return $sub_result;
}



function SelectCustomization($CustomizationID, $db, $SelectedParent) {
    $sql  = " SELECT DISTINCT ParentID, CustomizationID FROM Customizations
		  WHERE ParentID='$SelectedParent'";
    $ErrMsg = _('Could not retrieve the BOM components because');
    $DbgMsg = _('The SQL used to retrieve the components was');
    $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    if (DB_num_rows( $result ) != 0 ) {
        echo '<SELECT NAME="CustomizationID_' . $SelectedParent . '">';
        $Existing =  ($CustomizationID  == 0 ) ? ' SELECTED ' : '';
        echo '<OPTION ' . $Existing . ' VALUE=0> None' ;
        while ( $myrow = DB_fetch_array( $result) ) {
            $Existing =  ($CustomizationID  == $myrow['CustomizationID']) ? ' SELECTED ' : '';
            echo '<OPTION ' . $Existing . ' VALUE=' . $myrow['CustomizationID'] . '> ' . TypeNoName( $myrow['CustomizationID'], $db, 300)   . ' ';
        }
        echo "</SELECT>";
    }
}

function ListCustomizations($CustomizationID, $db, $SelectedParent, $Action=null) {
        $sql = "SELECT DISTINCT ParentID, CustomizationID FROM Customizations";
        if ( $SelectedParent != '') {
                $sql .= " WHERE ParentID='$SelectedParent'";
        }
        $ErrMsg = _('Could not retrieve the BOM components because');
        $DbgMsg = _('The SQL used to retrieve the components was');
        $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
        echo '<center><table width=100%>';
        $NewCustomizationID = '';
        $CustomizationName = $_POST['CustomizationName'];
	if ($_POST['SELECT_' . $SelectedParent ] == 'SELECT_NONE') {
		$NewCustomizationID = '';
	} else {
	        $NewCustomizationID	= $_POST['SELECT_' . $SelectedParent ];
		if ( ( $_POST['CustomizationName'] !='' ) AND ($NewCustomizationID==$_POST['CustomizationID']) ) {
			if ($_POST['CustomizationName'] != TypeNoName($NewCustomizationID, $db, 300)) {
				$sql = "UPDATE Names SET Name='$CustomizationName' WHERE Type=300 AND TypeNo='$NewCustomizationID'";
				$ret = DB_query( $sql, $db );
			}
			$CustomizationName      = $_POST['CustomizationName'];
		} else {
			$CustomizationName	= TypeNoName($NewCustomizationID, $db, 300);
		}
	        if ( ($Action=='Edit') || ($Action=='List')) {
		        echo "<TR BGCOLOR=GREEN><TD COLSPAN=3><B>$SelectedParent: $NewCustomizationID</B></TD><TD>";
				if ($Action=='Edit') {
		                Input_Text('Name','CustomizationName',$CustomizationName, 32, 32, true);
		        } else {
	        	        Input_Hidden('CustomizationName',$CustomizationName);
			        echo $CustomizationName;
		        }
		        echo "</TD>";
		} else {
			Input_Hidden('CustomizationName',$CustomizationName);
		}
	}
        echo    "<TR><TD width=190>" .
                        "<input type='radio' name='SELECT_" . $SelectedParent . "'" .
                        " onclick='this.form.submit();' " .
                        "value='SELECT_NONE'" . ( ($_POST['SELECT_' . $SelectedParent]) ? 'checked': 'off' ) . ">None</TD>" .
                        "</TR>";

	while ( $myrow = DB_fetch_array( $result) ) {
                if ( $_POST['SELECT_' . $myrow['ParentID']] == $myrow['CustomizationID'] ) {
                	$checked = ' checked ';
		} else {
			$checked = '';
		}
                echo    "<TR><TD width=190>" .
			"<input type='radio' name='" . 'SELECT_' . $myrow['ParentID'] . "'" .
			" onclick='this.form.submit();' " .
			"value='" . $myrow['CustomizationID'] .  "' $checked >" .
			TypeNoName( $myrow['CustomizationID'], $db, 300) . "</TD>" .
                        "</TR>";
        }
	echo '</table>';
    return $NewCustomizationID;
}


function getSubstitutionList( $WOID, $db) {
        $sql = 'SELECT Substitutions.* FROM WorksOrders
                LEFT JOIN Customizations ON WorksOrders.CustomizationID=Customizations.CustomizationID
                LEFT JOIN Substitutions ON Substitutions.ID=Customizations.SubstitutionID
                WHERE WorksOrders.CustomizationID !=0 AND WorksOrders.WORef=' . $WOID;
        $res = DB_query( $sql, $db );
        return $res;
}

function getStdCost($StockID, $db) {
        $sql = "SELECT MaterialCost + LabourCost + OverheadCost AS StdCost
                        FROM StockMaster WHERE StockID='$StockID'";
        $result = DB_query($sql, $db, "The standard cost cannot be retrieved");
        $myrow = DB_fetch_row($result);
        return $myrow[0];
}


