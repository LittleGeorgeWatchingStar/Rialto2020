<?php
/* $Revision: w.3 $ */
$PageSecurity = 10;
$All_Items = "ALLITEMSCODE";

#include("includes/session.inc");
#include("includes/header.inc");
include_once('config.php');
include_once('includes/ConnectDB.inc');

include("includes/DateFunctions.inc");
include("includes/WO_ui_input.inc");
include("includes/WO_Includes.inc");

$outputstrings['File']['delimiter'] = "\n";
$outputstrings['WWW']['delimiter']  = "\n";

$outputstrings['File']['string'] = 
	"BEGIN:VCALENDAR" . $outputstrings['File']['delimiter'] .
        "VERSION:2.0" . $outputstrings['File']['delimiter'] .
        "PRODID:-//Gumstix, Inc. Work Calendar//NONSGML v1.0//EN" . $outputstrings['File']['delimiter'];

$outputstrings['WWW']['string'] =
	"BEGIN:VCALENDAR" . $outputstrings['WWW']['delimiter'] .
	"VERSION:2.0" . $outputstrings['WWW']['delimiter'] .
	"PRODID:-//Gumstix, Inc./Work Calendar//NONSGML v1.0//EN" . $outputstrings['WWW']['delimiter'];


$SQLBeforeDate	= FormatDateForSQL(Date($DefaultDateFormat));
$SQLAfterDate	= FormatDateForSQL(Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-5,Date("d"),Date("y"))));

$SQL = "SELECT ST.ID, ST.SupplierNo, ST.SuppReference, ST.TranDate, ST.DueDate, ST.OvAmount, ST.Hold, S.SuppName
	FROM SuppTrans ST
	INNER JOIN Suppliers S ON ST.SupplierNo = S.SupplierID
	WHERE ST.Type= 20
	AND ST.TranDate >= '". $SQLAfterDate . "'
	AND ST.TranDate <= '" . $SQLBeforeDate . "'
	AND ST.Settled = 0
	ORDER BY ST.SupplierNo, ST.DueDate";

$PayablesResult = DB_query($SQL,$db,"No orders were returned");
$i=0;
while ($myrow=DB_fetch_array($PayablesResult)) {
	$requiredBy = str_replace("-", "", $myrow["DueDate"] );
	$dtdue   = $requiredBy    ; //  . "T140000" ;
	$dtstamp=Date("Ymd") . "T" . Date("his");
	$dateOnlyStamp = Date("Ymd") ;
        $dtkit   = $requiredBy - ($myrow["LocationName"]=="BesTek"?7:14) ; //  . "T153000";
	$i++;
	$SuppName = substr($myrow["SuppName"],0,strpos($myrow["SuppName"] . " ", ' ', 6));
	foreach ( array ("WWW", "File") as $key )  {
		$outputstrings[$key]['string'] .= 
			"BEGIN:VEVENT" . $outputstrings[$key]['delimiter'] .
			"UID:" . $i . ".due.roy@gumstix.com" . $outputstrings[$key]['delimiter'] .
			"DSTAMP:" . $dtstamp . $outputstrings[$key]['delimiter'] .
			"DTSTART:" .  $dtdue. $outputstrings[$key]['delimiter'] .
			"SUMMARY: $" . number_format($myrow["OvAmount"],2, '.', '') . " " . $SuppName . $outputstrings[$key]['delimiter'] .
			"END:VEVENT" . $outputstrings[$key]['delimiter'] 
			 ;
	}
}

$outputstrings['File']['string'] .=	"END:VCALENDAR" ;
$outputstrings['WWW']['string'] .=	"END:VCALENDAR" ;

echo $outputstrings['WWW']['string'];

$fp = fopen( '/www/share.gumstix.com/calendar/gumstix.ics','wb');
fwrite ($fp, $outputstrings['File']['string']);
fclose ($fp);



?>
