<?php

//	include_once($rootpath . "/includes/manufacturing/db/manufacturing_db.inc");
include_once("includes/BOMs.inc");
include_once("includes/WO_ui_input.inc");

function isManufactured($MBFlag)
{
	return ($MBFlag == 'M');
}

function isPurchased($MBFlag)
{
	return ($MBFlag == 'B');
}

function isService($MBFlag)
{
	return ($MBFlag == 'D');
}

function hasStockHolding($MBFlag)
{
	return isPurchased($MBFlag) OR isManufactured($MBFlag);
}

//--------------------------------------------------------------------------------------

?>
