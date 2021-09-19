<?php
/* $Revision: 1.3 $ */

$PageSecurity = 15;

include ('includes/session.inc');
$title = _('Reorder requirements');
include ('includes/header.inc');
include('includes/manufacturing_ui.inc');


$BOM    = array();
$MBflag = array();

function QtyUsedBy($ComponentID, $ParentID) {
	global $BOM;
	global $MBflag;
	$qty	= $BOM[$ParentID][$ComponentID];
	if ( $MBflag[$ParentID] =='B') {
		return $qty;
	} else {
		if (is_array($BOM[$ParentID])) foreach ($BOM[$ParentID]["Name"] as $SubComponent => $SubQuantity) {
			$qty += $SubQuantity * QtyUsedBy( $ComponentID, $SubComponent);
		}
		return  $qty;
	}
}

$sql = "SELECT Parent, Component, Quantity,MBflag FROM BOM
	LEFT JOIN StockMaster ON StockID=Parent ";
$res = DB_query($sql, $db);
while ($myrow=DB_fetch_array($res)) {
	$ParentID	 = $myrow['Parent'];
	$ComponentID	 = $myrow['Component'];
	$Quantity	 = $myrow['Quantity'];
	if (!is_array($BOM[$ParentID])) {
		$BOM[$ParentID]			= array();
		$BOM[$ParentID]["Name"]		= $ParentID;
		$BOM[$ParentID][$ParentID]	= 1;
		$MBflag[$ParentID]		= $myrow['MBflag'];
	}
	$BOM[$ParentID][$ComponentID] = $Quantity;
}

$sql = "SELECT orders_status_history.date_added,  products.products_model, products.products_model, StockMaster.MBflag,orders_products.products_quantity
	FROM osc_dev.orders_status_history
	LEFT JOIN osc_dev.orders_products ON orders_products.orders_id = orders_status_history.orders_id
	LEFT JOIN osc_dev.products ON products.products_id = orders_products.products_id
	LEFT JOIN StockMaster ON StockMaster.StockID=products.products_model
	WHERE orders_status_history.date_added > '2006-01-01'
	AND Discontinued=0 
	AND orders_status_history.orders_status_id = 1
	ORDER BY products.products_model, orders_status_history.date_added";

$product_list	= array();
$quantity	= array();
$MBflag		= array();

$result = DB_query( $sql, $db );
while ($myrow = DB_fetch_array($result)){
	$this_product = $myrow["products_model"];
	if ( !is_array( $timing_list[$this_product]) ) {
		$product_list[] = $this_product;
	        $timing_list[$this_product] = array();
	}
        $timing_list[$this_product][] = $myrow["date_added"];
        $quantity[$this_product] += $myrow["products_quantity"];
	$MBflag[$this_product] = $myrow["MBflag"];
}

$intervals = array();
$cum_intervals = array();
$daily_sales = array();
$QOH=array();

foreach ($product_list as $this_product) {
	$intervals[$this_product] = array();
	$last_time = 0;
	foreach ($timing_list[$this_product] as $this_time) {
		if ( $last_time != 0) {
			$this_interval = (strtotime($this_time) - strtotime($last_time));
			$intervals[$this_product][] = $this_interval;
			$cum_intervals[$this_product] += $this_interval;
		}
		$last_time = $this_time;
	}
	$numIntervals = count($intervals[$this_product]);
        $maxInterval = max($intervals[$this_product]);
	$filtered_quantity = (1-1/$numIntervals) * $quantity[$this_product];
	$filtered_cum_intervals = $cum_intervals[$this_product]-$maxInterval;
	$daily_rate =  24/ (  ($filtered_cum_intervals/$filtered_quantity) /3600) ;
	$daily_sales[$this_product] = $daily_rate;
}
$consumption = array();
$days_left = array();
$sql = "SELECT StockID,MBflag FROM StockMaster WHERE Discontinued=0";
$res = DB_query($sql, $db);
while ($myrow=DB_fetch_array($res)) {
	$StockID = $myrow['StockID'];
	$consumption[$StockID] = 0;
	$MBflag[$StockID] = $myrow['MBflag'];
        $QOH[$StockID] = getQOH($StockID, $db, 7);
	foreach ($product_list as $this_product) {
		$thisqty = QtyUsedBy($StockID,$this_product);
		$consumption[$StockID] += $thisqty * $daily_sales[$this_product];
	}
	if ( $consumption[$StockID] != 0) $days_left[$StockID] = $QOH[$StockID] / $consumption[$StockID];
}
echo "<table>";
asort($days_left);
foreach ($days_left as $this_product => $days) {
	if (($consumption[$this_product] != 0) && (($MBflag[$this_product]=='B') ||  ($MBflag[$this_product]=='M'))) {
		echo "<tr>";
                echo "<td>";
                echo "</td><td>";
                echo "$this_product";
                echo "</td><td>";
		echo $quantity[$this_product]; 
                echo "</td><td>";
		echo number_format($consumption[$this_product],2);
                echo "</td><td>";
		echo number_format($QOH[$this_product],0);
                echo "</td><td>";
		echo number_format($days_left[$this_product],0) . "<BR>";
                echo "</td><td>";
                echo "</tr>";
	}
}
echo "</table>";
include('includes/footer.inc');
?>
