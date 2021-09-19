<?php
/* $Revision: 1.4 $ */
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Recalculate QOH');
include('includes/header.inc');
include('includes/DateFunctions.inc');

function SetLocQty( $StockID, $db, $loc, $Qty ) {
        $sql = "SELECT * FROM LocStock WHERE LocCode='$loc' AND StockID='$StockID'";
//        echo $sql . '<br>';
        $ret = DB_query( $sql, $db);
        if ($res = DB_fetch_array($ret)) {
                $sql_a = "UPDATE LocStock SET Quantity='$Qty' WHERE LocCode='$loc' AND StockID='$StockID'";
//		echo $sql_a . '<br>';
                $ret_a = DB_query( $sql_a, $db );
        } else {
                $sql_a = "INSERT INTO LocStock (StockID, LocCode, Quantity) VALUES ('$StockID','$loc','$Qty') ";
                $ret_a = DB_query( $sql_a, $db );
//                echo $sql_a . '<br>';
        }
}

$sql = "SELECT	StockID, LocCode, Qty, StkMoveNo  FROM StockMoves 	WHERE HideMovt=0 
	ORDER BY  StockMoves.StockID, StockMoves.TranDate, StkMoveNo";
$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';

$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
$LastStockID='';
$locs = array( 7,8,9,10);
while ($myrow=DB_fetch_array($MovtsResult)) {
	if ( $myrow['StockID'] != $LastStockID ) {
		if ( $LastStockID != '') {
			foreach ( $locs as $loc ) {
				SetLocQty( $LastStockID, $db, $loc, $CalcNewQOH[$loc]);
			}
		}
		$LastStockID = $myrow['StockID'];
		$CalcNewQOH=array();
		$NewQOH = 0;
	}
	$CalcNewQOH[$myrow['LocCode']] += $myrow['Qty'];
	$NewQOH += $myrow['Qty'];
	$update_sql = "	UPDATE StockMoves SET NewQOH='$NewQOH' WHERE StkMoveNo='" . $myrow['StkMoveNo'] . "'";
//	echo $update_sql . '<BR>';
	$update_res = DB_query( $update_sql, $db );
}
include('includes/footer.inc');
?>
