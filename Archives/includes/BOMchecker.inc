<?php
/* $Revision: 1.4 $ */

/**
 * @deprecated Use BomCsvValidator class instead.
 *
 * Checks the bill of materials (BOM) for the given board and version.
 * @param string $BoardName
 * @param resource $db
 * @param string|int $BoardVersion
 * @return string|int
 *         An HTML string of differences, if differences exist;
 *         0 if the BOM is okay;
 *         -1 if there is an error.
 */
function check_BOM($BoardName, $db, $BoardVersion ) //	, $locCode)
{
	if ( !isset($BoardName) ||  !isset($BoardVersion)) {
		return -1;
	}

	$handle = curl_init("http://ops.gumstix.com/svn/gumstix-hardware/Production/BRD/$BoardName-R$BoardVersion/$BoardName.bom.csv");
	curl_setopt($handle, CURLOPT_USERPWD, 'weberp:saywhat');
	curl_setopt($handle, CURLOPT_HEADER, false);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_USERAGENT, 'WebERP');
	curl_setopt($handle, CURLOPT_FAILONERROR, true);
	$BOMCSV = curl_exec($handle);

	if(curl_errno($handle))	{
		return -2;
	}

	curl_close($handle);
	$BOMCSV = csv_parse($BOMCSV);
	$diffsExist = false;
	for ($row=1; $row < count($BOMCSV); $row++) {
		$BOMList = $BOMCSV[$row];

		$sql = "SELECT Quantity FROM BOM WHERE Parent='".$BoardName."' AND Component = '".$BOMList[1]."'";
//		$sql .=	" AND LocCode='" .  $locCode . "'";
		$componentinfo	= DB_query($sql, $db, "Query failed");
		$componentqty	= DB_fetch_array($componentinfo);
		$componentqty   = $componentqty['Quantity'];
		if ( $componentqty != $BOMList[0]  ) {
			if (!$diffsExist) {
				$differences .= "<TR><th colspan='2' class='title'>Differences</TH>";
				$differences .= "<TH>SVN</TH><TH>webERP</TH></TR><TR>";
				$differences .= "<th>" .  "StockID" . "</th>";
				$differences .= "<th>" .  "Description". "</th>";
				$differences .= "<th>" .  "Qty" . "</th>";
				$differences .= "<th>" .  "Qty" . "</th>";
				$differences .= "</TR>";
			}
			$diffsExist = true;
			$differences .= "<TR><TD>" .  $BOMList[1] . "</TD>";
			$differences .= "<TD>" .  $BOMList[2] . ' ' . $BOMList[3]. "</TD>";
			$differences .= "<TD>" .  $BOMList[0]. "</TD>";
			$differences .= "<TD>" .  number_format($componentqty, 0) . "</TD>";
			$differences .= "</TR>";
	    }
	}

	if (!$diffsExist) {
		$differences .= "<TR><Th>" .  "E Qty" . "</Th>";
		$differences .= "<Th>" .  "E ID" . "</Th>";
		$differences .= "<Th>" .  "E Name". "</Th>";
		$differences .= "</TR>";
	}

	$sql = "SELECT * FROM BOM WHERE Parent='".$BoardName."'";
//	$sql.=" AND LocCode='" .  $locCode . "'";
	$componentinfo	= DB_query($sql, $db, "Query failed");
	while ( $thiscomponentinfo = DB_fetch_array($componentinfo)) {
		$match=-1;
		for ($row=1; $row < count($BOMCSV); $row++) {
			$BOMList = $BOMCSV[$row];
			if ($BOMList[1] == $thiscomponentinfo['Component']) {
				$match = $row;
				$row = count($BOMCSV);
			}
		}
		if ($match==-1) {
			$diffsExist = true;
            $differences .= "<TR>";
            $differences .= "<TD>" .  $thiscomponentinfo['Component']. "</TD>";
			$differences .= "<TD>" .  ''  . "</TD>";
            $differences .= "<TD>" .  '0' . "</TD>";
			$differences .= "<TD>" .  number_format($thiscomponentinfo['Quantity'],0) . "</TD>";
            $differences .= "</TR>";
		}
		if ( $thiscomponentinfo['Quantity'] != $BOMList[0]  ) {
			if ( $componentqty != $BOMList[0]  ) {
				$diffsExist = true;
				$differences .= "<TR><TD>" .  $thiscomponentinfo['Quantity']. "</TD>";
				$differences .= "<TD>" .  $thiscomponentinfo['Component']. "</TD>";
				$differences .= "<TD>" .  $componentqty . "</TD>";
				$differences .= "</TR>";
			}
		}
	}

	if ($diffsExist) {
		return $differences;
	} else {
		return 0;
	}
}

?>
