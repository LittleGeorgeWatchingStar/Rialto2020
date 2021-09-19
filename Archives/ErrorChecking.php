<?php
/* $Revision: 1.3 $ */

$PageSecurity = 7;

include ('includes/session.inc');
$title = _('Error Checking the Supply Chain');
include ('includes/header.inc');
include_once("includes/WO_ui_input.inc");

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", "", $str);
}

function drawgraph( $datapoints, $filenamer  ) {
	$graph_width = 500;
	$graph_height = 50;
	$numbins = max(8,count($datapoints) / 8);
	$binwidth = $graph_width / $numbins;
	$im = imagecreate($binwidth * $numbins, $graph_height);
	$background_color = imagecolorallocate($im, 255, 255, 255);
	$text_color = imagecolorallocate($im, 233, 14, 91);
	$bins = array();
	$seconds_per_bin = max($datapoints) / $numbins;
	foreach ( $datapoints as $dot ) {
		$bin_number = ceil($dot/$seconds_per_bin);
		$bins[$bin_number] ++;
	}
	$x = 0;
	$binscale = $graph_height / max($bins);
	foreach ( $bins as $binno => $bin ) {
		imagefilledrectangle($im, $binno * $binwidth, $graph_height, ($binno+1)*$binwidth , $graph_height - ($bin * $binscale) + 5, 233 );
	}
		$filename = sprintf("reports/graphs/%s.png", $filenamer );
	ImagePNG($im,$filename);
	ImageDestroy($im);
	echo "<img src=$filename>"; 
}

echo	"<CENTER><TABLE>";
echo	"<TR>" .
	"<TD> Product </TD>" . 
        "<TD align='right'> </TD>" .
        "<TD align='right'> </TD>" .
        "<TD align='right'> </TD>" .
        "<TD align='right'> </TD>" .
	"</TR>";
echo "<TR>";

$SQL  = array();
$URL  = array();


$URL["Products without StdCost"] = 'StockCostUpdate';
$SQL["Products without StdCost"]
        = "     SELECT SM.*
                FROM StockMaster SM
                WHERE (Materialcost + labourcost)=0
			AND Discontinued=0
			AND SM.CategoryID=2
			AND MBflag IN ('M','B')  ";

$URL["Components s/ supplier"] = 'PurchData';
$SQL["Components s/ supplier"]
	= "	SELECT SM.*
		FROM StockMaster SM 
		LEFT JOIN PurchData PD ON PD.StockID=SM.StockID
		WHERE	SupplierNo IS NULL
			AND Discontinued=0
                        AND SM.MBflag='B'  ";

$URL["Parts without use and UnDisc'd"] = 'Stocks';
$SQL["Parts without use and UnDisc'd"]
        = "     SELECT SMC.*
                FROM StockMaster SMC
                LEFT JOIN BOM ON SMC.StockID=BOM.Component
                WHERE	SMC.CategoryID = 1
                        AND Discontinued=0
			AND BOM.Component IS NULL   ";

$URL["Manufactured parts without a CM"]= 'PurchData';
$SQL["Manufactured parts without a CM"]
        = "     SELECT SM.*
                FROM StockMaster SM
                LEFT JOIN PurchData PD ON PD.StockID=SM.StockID
                WHERE	PD.SupplierNo IS NULL
                        AND SM.Discontinued=0
			AND SM.MBflag='M'  ";

$URL["Compliant bought parts without a reference"]= 'Stocks';
$SQL["Compliant bought parts without a reference"]
        = "     SELECT SM.*
                FROM StockMaster SM
                LEFT JOIN PurchData PD ON PD.StockID=SM.StockID
                WHERE   SM.Discontinued=0
                        AND SM.MBflag='B'
			AND PD.RoHS = 'Compliant'
                        AND PD.RoHSReference IS NULL";

$URL["Compliant BOM with uncompliant "]= 'Stocks';
$SQL["Compliant BOM with uncompliant "]
        = "     SELECT DISTINCT SMP.*
                FROM StockMaster SMC
                LEFT JOIN BOM ON SMC.StockID=BOM.Component
                LEFT JOIN StockMaster SMP ON SMP.StockID=BOM.Parent
                LEFT JOIN PurchData PD ON PD.StockID=SMC.StockID
                WHERE       SMP.CategoryID = 2
                        AND SMC.CategoryID = 1
                        AND BOM.Component IS NOT NULL   
                	AND SMP.Discontinued=0
                        AND SMC.MBflag='B' AND PD.RoHS = 'Compliant'
                        AND PD.RoHSReference IS NULL";

foreach ($SQL as $searchKey => $sql) {
	echo "<TD VALIGN=TOP><TABLE>";
	echo "<TR BGCOLOR=BEIGE><TD COLSPAN=5>$searchKey</TD></TR>";
	$result = DB_query( $sql, $db );
	while ($myrow = DB_fetch_array($result)){
		$T_line ="<TD><A target='_blank' HREF='".$URL[$searchKey].".php?StockID=" . $myrow['StockID'] . "'>" .  $myrow['StockID'] . "</A></TD>";
		echo "<TR>".$T_line . "</TR>";
	}
	echo "</TD></TABLE>";
}

echo "</TR>";
echo "</TABLE>";

include('includes/footer.inc');

?>
