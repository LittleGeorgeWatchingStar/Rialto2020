<?php
/* $Revision: 1.3 $ */

$PageSecurity = 5;

include ('includes/session.inc');
$title = _('Produce Stock Quantities CSV');
include ('includes/header.inc');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", "", $str);
}

$SQL  = array();
$FB   = array();

$FN["AllStock"] = 'Stock_All';
$SQL["AllStock"] = 'SELECT L.StockID, Description, sum(Quantity) FROM StockMaster S LEFT JOIN LocStock L ON L.StockID=S.StockID GROUP BY S.StockID ORDER BY StockID';

$FN["Meadowood"] = 'Stock_Meadowood';
$SQL["Meadowood"] = 'SELECT L.StockID, Description, Quantity FROM StockMaster S LEFT JOIN LocStock L ON L.StockID=S.StockID WHERE LocCode=7 AND Quantity!=0 ORDER BY StockID';

$FN["Bestek"] = 'Stock_Bestek';
$SQL["Bestek"] = 'SELECT L.StockID, Description, Quantity FROM StockMaster S LEFT JOIN LocStock L ON L.StockID=S.StockID WHERE LocCode=8 AND Quantity!=0 ORDER BY StockID';

$FN["Innerstep"] = 'Stock_Innerstep';
$SQL["Innerstep"] = 'SELECT L.StockID, Description, Quantity FROM StockMaster S LEFT JOIN LocStock L ON L.StockID=S.StockID WHERE LocCode=9 AND Quantity!=0 ORDER BY StockID';

$FN["Products"] = 'Stock_Products';
$SQL["Products"] = 'SELECT L.StockID, Description, Quantity FROM StockMaster S LEFT JOIN LocStock L ON L.StockID=S.StockID WHERE CategoryID=2 GROUP BY L.StockID ORDER BY L.StockID ';


$ErrMsg = _('The SQL to get the stock quantites failed with the message');

echo    "<CENTER><TABLE>";
echo    "<TR>" .
        "<TD align='right'> </TD>" .
        "<TD align='right'> </TD>" .
        "<TD align='right'> </TD>" .
        "<TD align='right'> </TD>" .
        "</TR>";
echo "<TR>";

foreach ($SQL as $searchKey => $sql) {
        $result = DB_query( $sql, $db, $ErrMsg );
        echo "<TD VALIGN=TOP><TABLE>";
        echo "<TR BGCOLOR=BEIGE><TD COLSPAN=5>$searchKey</TD></TR>";
	$fp = fopen($reports_dir . '/' . $FN[$searchKey] . '.csv', "w");
	while ($myrow = DB_fetch_row($result)){
	        $line = stripcomma($myrow[0]) . ', ' . stripcomma($myrow[1]) . ', ' . stripcomma($myrow[2]);
	        fputs($fp, $line . "\n");
	}
	fclose($fp);
 	echo "<TR><td><A HREF='" . $rootpath . '/' . $reports_dir .'/'.  $FN[$searchKey] . ".csv'>" . _('click here') . '</A> ' . _('to view the file') . '</td></TR>';
	echo "</TABLE></td>";
}

echo "</TR>";
echo "</TABLE>";


include('includes/footer.inc');

?>
