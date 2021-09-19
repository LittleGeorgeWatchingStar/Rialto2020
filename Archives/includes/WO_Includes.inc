<?php

/* $Revision: 0.01 $ */

//--------------------------------------------------------------------------------------------------

function alternateTableRowColor($k) {
	global $config_oddRowColor, $config_evenRowColor;
	if ($k==1){
		echo "<tr bgcolor=$config_oddRowColor>";
		$k=0;
	} else {
		echo "<tr bgcolor=$config_evenRowColor>";
		$k++;
	}
	return $k;
}

//------------------------------------------------------------------------------------

$All_Items = "ALLITEMSCODE";

function base_stockItemsList($sql, $db, $listControlName, $selectedID,	$allOption=false, $allOptionName="", $submitOnChange=false)
{
global 	$All_Items;

	if ($submitOnChange==true)
		echo "<SELECT NAME=$listControlName onchange='this.form.submit();'>";
	else
		echo "<SELECT NAME=$listControlName>";

	$result = DB_query($sql, $db);

	if ($allOption == true){
	    	if ($All_Items == $selectedID){
    		     echo "<OPTION SELECTED Value='" . $All_Items . "'>" . $allOptionName;
	    	} else {
	    	     echo "<OPTION Value='" . $All_Items . "'>" . $allOptionName;
    		}
		if ($selectedID == "") {
			$selectedID =  $All_Items;
		}
	}
	while ($row = DB_fetch_row($result)) {
		if ($selectedID==$row[0]) {
			echo "<OPTION selected value=" . $row[0] . ">";
		} else {
			echo "<OPTION value=" . $row[0] . ">";
		}
		echo $row[1];
		if ($selectedID == "") {
			$selectedID = $row[0];
		}
	}
	echo "</SELECT>";

//	DB_free_result($result);
}
//------------------------------------------------------------------------------------

function stockManufacturedItemsList($listControlName, $db, $selectedID,$allOption=false, $submitOnChange=false)
{
	$sql = "SELECT StockID, Description, CategoryDescription
		FROM StockMaster,StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID
		AND (StockMaster.MBflag='M') AND StockMaster.Discontinued = 0 ORDER BY StockID";
	base_stockItemsList($sql, $db, $listControlName, $selectedID, $allOption, _("All Items"), $submitOnChange);
}


//------------------------------------------------------------------------------------------------

function LocationsList($listControlName, $db, $selectedID, $allOption=false, $submitOnChange=false, $allOptionName="")
{
global  $All_Items;

	if ($submitOnChange==true)
		echo "<SELECT NAME=$listControlName onchange='this.form.submit();'>";
	else
		echo "<SELECT NAME=$listControlName>";

//	if ($selectedID =="" AND isset($_SESSION['UserStockLocation']) AND $_SESSION['UserStockLocation'] !="") {
//	  	$selectedID = $_SESSION['UserStockLocation'];
//	}

	$sql = "SELECT LocCode, LocationName FROM Locations";
	$result = DB_query($sql, $db);

	if ($allOption == true){
		echo "<OPTION ";
    	if ($selectedID == $All_Items)
    	     echo " SELECTED ";
		echo " Value='" . $All_Items. "'>" . _("All Locations");

		if ($selectedID == "") {
			$selectedID = $All_Items;
		}
	}

	while ($row = DB_fetch_row($result)) {
			if ($selectedID==$row[0]) {
				echo "<OPTION selected value=" . $row[0] . ">";
			} else {
				echo "<OPTION value=" . $row[0] . ">";
			}
			echo $row[1];

			if ($selectedID == "") {
				$selectedID = $row[0];
			}
	}

	echo "</SELECT>";

//	DB_free_result($result);
}

//------------------------------------------------------------------------------------

function stockBOMItemsList($listControlName, $db, $selectedID, $allOption=false, $submitOnChange=false)
{
	global $All_Items;

	if ($submitOnChange==true)
		echo "<SELECT NAME=$listControlName onchange='this.form.submit();'>";
	else
		echo "<SELECT NAME=$listControlName>";

	$sql = "SELECT StockID, Description, CategoryDescription
				FROM StockMaster,StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID
				AND (StockMaster.MBflag='M' OR StockMaster.MBflag='K')
				AND StockMaster.Discontinued=0
				ORDER BY Description";
	$result = DB_query($sql, $db);

	if (($allOption == true)){
    	if ($All_Items == $selectedID){
    	     echo "<OPTION SELECTED Value='".$All_Items."'>" . _("All Items");
    	} else {
    	     echo "<OPTION Value='". $All_Items ."'>" . _("All Items");
    	}
		if ($selectedID == "") {
			$selectedID = $All_Items;
		}
	}

	while ($row = DB_fetch_row($result)) {
			if ($selectedID==$row[0]) {
				echo "<OPTION selected value=" . $row[0] . ">";
			} else {
				echo "<OPTION value=" . $row[0] . ">";
			}
			echo $row[1];

			if ($selectedID == "") {
				$selectedID = $row[0];
			}
	}

	echo "</SELECT>";
//	DB_free_result($result);
}


function stockHistoryItemsList($listControlName, $db, $selectedID, $allOption=false, $submitOnChange=false)
{
	global $All_Items;

	if ($submitOnChange==true)
		echo "<SELECT NAME=$listControlName onchange='this.form.submit();'>";
	else
		echo "<SELECT NAME=$listControlName>";

	$sql = "SELECT StockID, Description, CategoryDescription
				FROM StockMaster,StockCategory WHERE StockMaster.CategoryID=StockCategory.CategoryID
				AND (StockMaster.MBflag='M' OR StockMaster.CategoryID=3)
				AND StockMaster.Discontinued=0
				ORDER BY Description";
	$result = DB_query($sql, $db);

	if (($allOption == true)){
    	if ($All_Items == $selectedID){
    	     echo "<OPTION SELECTED Value='".$All_Items."'>" . _("All Items");
    	} else {
    	     echo "<OPTION Value='". $All_Items ."'>" . _("All Items");
    	}
		if ($selectedID == "") {
			$selectedID = $All_Items;
		}
	}

	while ($row = DB_fetch_row($result)) {
			if ($selectedID==$row[0]) {
				echo "<OPTION selected value=" . $row[0] . ">";
			} else {
				echo "<OPTION value=" . $row[0] . ">";
			}
			echo $row[1];

			if ($selectedID == "") {
				$selectedID = $row[0];
			}
	}

	echo "</SELECT>";
//	DB_free_result($result);
}



function workCenterList($listControlName, $db, $selectedID, $allOption=false)
{
	global $All_Items;
	echo "<SELECT NAME=$listControlName>";

	$sql = "SELECT Location, Description FROM WorkCentres";
	$result = DB_query($sql, $db);

	if ($allOption == true){
    	if ($All_Items == $selectedID){
    	     echo "<OPTION SELECTED Value='" . $All_Items ."'>" . _("All Work Centres");
    	} else {
    	     echo "<OPTION Value='".$All_Items."'>" . _("All Work Centres");
    	}
	}

	while ($row = DB_fetch_row($result)) {
			if ($selectedID==$row[0]) {
				echo "<OPTION selected value=" . $row[0] . ">";
			} else {
				echo "<OPTION value=" . $row[0] . ">";
			}
			echo $row[1];
	}

	echo "</SELECT>";
//	DB_free_result($result);
}

function workCenterList_TableRow($label, $db, $listControlName, $selectedID, $allOption=false)
{
	echo "<TR><TD>" . $label . "</TD>";
	echo "<TD>";
	workCenterList($listControlName, $db, $selectedID, $allOption);
	echo "</TD></TR>";
}

function stockBOMItemsList_TableRow($label, $db, $listControlName, $selectedID, $allOption=false, $submitOnChange=false)
{
	echo "<TR><TD>" . $label . "</TD>";
	echo "<TD>";
	stockBOMItemsList($listControlName, $db, $selectedID, $allOption, $submitOnChange);
	echo "</TD></TR>";
}


?>

