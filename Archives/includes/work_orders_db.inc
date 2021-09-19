<?php


//--------------------------------------------------------------------------------------


//--------------------------------------------------------------------------------------

function add_work_order($WORef, $db, $LocCode, $UnitsReqd, $StockID,
					$Type, $Date, $RequiredBy, $Costs, $Memo)
{
	if (!($Type == wo_types::advanced()))
		return add_work_order_quick($WORef, $LocCode, $UnitsReqd, $StockID, $Type, $Date, $Costs, $Memo);

//	BeginTransaction();

	$SQL_Date = FormatDateForSQL($Date);
	$SQL_RequiredBy = FormatDateForSQL($RequiredBy);

	$sql = "INSERT INTO  WorksOrders (WORef, LocCode, UnitsReqd, StockID,
					Type, Date, RequiredBy)
    			VALUES ('$WORef', '$LocCode', $UnitsReqd, '$StockID',
					$Type, '$SQL_Date', '$SQL_RequiredBy')";
	$result = DB_query($sql, $db, "could not add work order");

	$WOID = DB_Last_Insert_ID($db);

	add_comments(systypes::work_order(), $WOID, $RequiredBy, $Memo);

	addFormsForSysType(systypes::work_order(), $WOID);

	references::save_last($WORef, systypes::work_order());

//	CommitTransaction();

	return $WOID;
}

//--------------------------------------------------------------------------------------

function update_work_order($WOID, $db, $LocCode, $UnitsReqd, $StockID,
					$Date, $RequiredBy, $Memo)
{
//	BeginTransaction();

	$SQL_Date = FormatDateForSQL($Date);
	$SQL_RequiredBy = FormatDateForSQL($RequiredBy);

	$sql = "UPDATE  WorksOrders SET LocCode='$LocCode',
				UnitsReqd=$UnitsReqd, StockID='$StockID',
				RequiredBy='$SQL_RequiredBy',
				Date='$SQL_Date'
				WHERE WORef = $WOID";

	$result = DB_query($sql, $db, "could not update work order");

	update_comments(systypes::work_order(), $WOID, null, $Memo);

//	CommitTransaction();
}


//--------------------------------------------------------------------------------------

function work_order_has_productions($WOID, $db)
{
	$sql = "SELECT COUNT(*) FROM wo_manufacture WHERE WorkOrderID=$WOID";
	$result = DB_query($sql, $db, "query work order for productions");

	$myrow = DB_fetch_row($result);
	return ($myrow[0]>0);
}


//--------------------------------------------------------------------------------------

function work_order_has_payments($WOID, $db)
{
    $result = get_bank_trans(null, null, PaymentPersonTypes::WorkOrder(), $WOID);

    return (DB_num_rows($result)!=0);
}


//--------------------------------------------------------------------------------------

function close_work_order($WOID, $db) {
	$sql = "UPDATE  WorksOrders SET Closed='" . '1' .
			"' WHERE WORef  = $WOID";
	$result = DB_query($sql, $db, "could not close work order");
}

//--------------------------------------------------------------------------------------

function work_order_is_closed($WOID, $db) {
	$sql = "SELECT Closed FROM  WorksOrders WHERE WORef = $WOID";
	$result = DB_query($sql, $db, "could not query work order");
	$row = DB_fetch_row($result);
	return ($row[0] > 0);
}

//--------------------------------------------------------------------------------------

/**
 * @deprecated  Use WorkOrder#addQtyReceived() instead.
 *
 * @param unknown_type $WOID
 * @param unknown_type $db
 * @param unknown_type $quantity
 * @param unknown_type $force_close
 */
function work_order_update_finished_quantity($WOID, $db, $quantity, $force_close=0) {
	$sql = "UPDATE  WorksOrders SET UnitsRecd= UnitsRecd+ $quantity,
				Closed = ((UnitsRecd >= UnitsReqd) OR $force_close)
				WHERE WORef  = $WOID";

	$result = DB_query($sql, $db, "The work order issued quantity couldn't be updated");
}

?>
