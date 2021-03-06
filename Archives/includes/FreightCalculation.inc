<?php
/* $Revision: 1.3 $ */
/*Function to calculate the freight cost. 
Freight cost is determined by looking for a match of destination city from the Address2 and Address3 fields then looking through the freight company rates for the total KGs and Cubic meters  to figure out the least cost shipping company. */


Function CalcFreightCost ($TotalValue, $BrAddr2, $City, $State, $Zip, $Country, $TotalVolume, $TotalWeight, $FromLocation,$db){

	/*These variables are set up in the .ini file as configuration directives */
	global $FreightChargeAppliesIfLessThan;
	global $CountryOfOperation;

	$sql = "Select ShipperID,
			KGRate *" . $TotalWeight . " AS KGCost,
			CubRate * " . $TotalVolume . " AS CubCost,
			FixedPrice, MinimumChg
		FROM FreightCosts
		WHERE LocationFrom = '$FromLocation'
		AND MAXKGs > " . $TotalWeight . "
		AND MAXCub >" . $TotalVolume . "
		AND Destination LIKE '" . ucwords($City). "'";

	$CalcFreightCostResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo _("The freight calculation for the destination city cannot be performed because") . " - " . DB_error_msg($db);
	} elseif (DB_num_rows($CalcFreightCostResult)>0) {

		$CalcFreightCost =9999999;

		while ($myrow = DB_fetch_array($CalcFreightCostResult)) {

use Gumstix\GeographyBundle\Model\Country;
	/**********      FREIGHT CALCULATION
	IF FIXED PRICE TAKE IT IF BEST PRICE SO FAR OTHERWISE
	TAKE HIGHER OF CUBE, KG OR MINIMUM CHARGE COST 	**********/

			if ($myrow["FixedPrice"]!=0) {
				if ($myrow["FixedPrice"] < $CalcFreightCost) {
					$CalcFreightCost=$myrow["FixedPrice"];
					$CalcBestShipper =$myrow["ShipperID"];
				}
			} elseif ($myrow["CubCost"] > $myrow["KGCost"] && $myrow["CubCost"] > $myrow["MinimumChg"] && $myrow["CubCost"] < $CalcFreightCost) {

				$CalcFreightCost=$myrow["CubCost"];
				$CalcBestShipper =$myrow["ShipperID"];

			} elseif ($myrow["KGCost"]>$myrow["CubCost"] && $myrow["KGCost"] > $myrow["MinimumChg"] && $myrow["KGCost"] < $CalcFreightCost) {

				$CalcFreightCost=$myrow["KGCost"];
				$CalcBestShipper =$myrow["ShipperID"];

			} elseif ($myrow["MinimumChg"]< $CalcFreightCost){

				$CalcFreightCost=$myrow["MinimumChg"];
				$CalcBestShipper =$myrow["ShipperID"];

			}
		}
	} else {
		$CalcFreightCost = _("Unrecognised destination, delivery only to cities in") . " " . $CountryOfOperation;
	}
	if ($TotalValue >= $FreightChargeAppliesIfLessThan){

		/*Even though the order is over the freight free threshold - still need to calculate the best shipper to ensure get best deal*/

		$CalcFreightCost =0;
	}
	return array ($CalcFreightCost, $CalcBestShipper);
}

?>
