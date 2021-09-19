<?php
/* $Revision: w.3 $ */
$PageSecurity = 10;
$All_Items = "ALLITEMSCODE";

set_include_path('.' . PATH_SEPARATOR . '../web' . PATH_SEPARATOR . '../lib' );

#include("includes/session.inc");
#include("includes/header.inc");
include_once('config.php');
include_once('includes/ConnectDB.inc');
include( 'includes/inventory_db.inc');
include_once("includes/DateFunctions.inc");
include_once("includes/WO_ui_input.inc");
include("includes/WO_Includes.inc");

$outputstrings['File']['delimiter'] = "\n";
$outputstrings['WWW']['delimiter']  = "<BR>";
$eol = "\n";

$outputstrings['File']['string'] =
	"BEGIN:VCALENDAR" . $eol .
	"METHOD:PUBLISH" . $eol .
	"X-WR-TIMEZONE:America/Los_Angeles" . $eol .
	"PRODID:-//Apple Inc.//iCal 3.0//EN" . $eol .
	"CALSCALE:GREGORIAN" . $eol .
	"X-WR-CALNAME:WorkOrders" . $eol .
	"X-WR-CALDESC:WorkOrders@weberp.gumstix.com/WorkCalendar.php" . $eol .
	"VERSION:2.0" . $eol .
	"X-WR-RELCALID:A6D44CB7-EFC1-4A89-80C7-AB7EFE1A6AAE" . $eol .
	"X-APPLE-CALENDAR-COLOR:#B1365F" . $eol ;
/*	"BEGIN:VTIMEZONE" . $eol .
	"TZID:America/Los_Angeles" . $eol .
	"BEGIN:DAYLIGHT" . $eol .
	"TZOFFSETFROM:-0800" . $eol .
	"TZOFFSETTO:-0700" . $eol .
	"DTSTART:20070311T020000" . $eol .
	"RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU" . $eol .
	"TZNAME:PDT" . $eol .
	"END:DAYLIGHT" . $eol .
	"BEGIN:STANDARD" . $eol .
	"TZOFFSETFROM:-0700" . $eol .
	"TZOFFSETTO:-0800" . $eol .
	"DTSTART:20071104T020000" . $eol .
	"RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU" . $eol .
	"TZNAME:PST" . $eol .
	"END:STANDARD" . $eol .
	"END:VTIMEZONE" . $eol;
*/
$eol = '<BR>';

$outputstrings['WWW']['string'] =
        "BEGIN:VCALENDAR" . $eol .
        "METHOD:PUBLISH" . $eol .

        "X-WR-TIMEZONE:America/Los_Angeles" . $eol .
        "PRODID:-//Gumstix Inc.//iCal 3.0//EN" . $eol .
        "CALSCALE:GREGORIAN" . $eol .
        "X-WR-CALNAME:WorkOrders" . $eol .
        "X-WR-CALDESC:WorkOrders@weberp.gumstix.com/WorkCalendar.php" . $eol .
        "VERSION:2.0" . $eol .
        "X-WR-RELCALID:A6D44CB7-EFC1-4A89-80C7-AB7EFE1A6AAE" . $eol .
        "X-APPLE-CALENDAR-COLOR:#B1365F" . $eol ;

/*        "BEGIN:VTIMEZONE" . $eol .
        "TZID:America/Los_Angeles" . $eol .
        "BEGIN:DAYLIGHT" . $eol .
        "TZOFFSETFROM:-0800" . $eol .
        "TZOFFSETTO:-0700" . $eol .
        "DTSTART:20070311T020000" . $eol .
        "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU" . $eol .
        "TZNAME:PDT" . $eol .
        "END:DAYLIGHT" . $eol .
        "BEGIN:STANDARD" . $eol .
        "TZOFFSETFROM:-0700" . $eol .
        "TZOFFSETTO:-0800" . $eol .
        "DTSTART:20071104T020000" . $eol .
        "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU" . $eol .
        "TZNAME:PST" . $eol .
        "END:STANDARD" . $eol .
        "END:VTIMEZONE" . $eol;
*/

$SQL = "SELECT PurchOrders.*, SuppName, MAX(PurchOrderDetails.DeliveryDate) RequiredBy
        FROM PurchOrders
        LEFT JOIN PurchOrderDetails ON PurchOrderDetails.OrderNo = PurchOrders.OrderNo
	LEFT JOIN Suppliers ON SupplierID=SupplierNo
        WHERE PurchOrderDetails.Completed=0 AND Initiator!= 'WOSystem'
        GROUP BY PurchOrders.OrderNo ";

$PurchOrdersResult = DB_query($SQL,$db,"No orders were returned");
while ($myrow=DB_fetch_array($PurchOrdersResult)) {
        $requiredBy = str_replace("-", "", $myrow["RequiredBy"] );
        $dtdue     = $requiredBy    . "T140000" ;
	$dtdue_end = $requiredBy    . "T143000" ;
        $dtstamp=   Date("Ymd") . "T" . Date("his");
        $dtZStamp = /* "TZID=America/Los_Angeles:" . */ Date("Ymd") . "T" . Date("his");
        $i++;
	if ( ($myrow['DatePrinted'] != "")) {
	        foreach ( array ("WWW", "File") as $key )  {
                        $outputstrings[$key]['string'] .=
                                "BEGIN:VEVENT" . $outputstrings[$key]['delimiter'] .
                                "SEQUENCE:3" . $outputstrings[$key]['delimiter'] .
                                "TRANSP:OPAQUE" . $outputstrings[$key]['delimiter'] .
                                "UID:" . $i . ".PO.bob@gumstix.com" . $outputstrings[$key]['delimiter'] .
                                "DTSTART:" .  $dtdue . $outputstrings[$key]['delimiter'] .
//                                "STATUS:CONFIRMED" . $outputstrings[$key]['delimiter'] .
                                "DSTAMP:" . $dtstamp . $outputstrings[$key]['delimiter'] .
                                "SUMMARY:PO " . $myrow['OrderNo'] . " " . $myrow["SuppName"] . $outputstrings[$key]['delimiter'] .
//                                "LOCATION:" . $myrow["LocationName"] . " " . $outputstrings[$key]['delimiter'] .
				"CREATED:20090927T173817Z" . $outputstrings[$key]['delimiter'] .
				"DTEND:" . $dtdue_end .$outputstrings[$key]['delimiter'] .
                                "END:VEVENT" . $outputstrings[$key]['delimiter']
                                ;
	        }
	}
}

$outputstrings['File']['string'] .=	"END:VCALENDAR" ;
$outputstrings['WWW']['string'] .=	"END:VCALENDAR" ;

echo $outputstrings['WWW']['string'];

$fp = fopen( SITE_FS_SHARE_BASE . '/calendar/gumstix.ics', 'wb');
fwrite ($fp, $outputstrings['File']['string']);
fclose ($fp);



?>
