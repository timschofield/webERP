<?php
/* $Revision: 1.4 $ */
/*Function to calculate the freight cost. 
Freight cost is determined by looking for a match of destination city from the Address2 and Address3 fields then looking through the freight company rates for the total KGs and Cubic meters  to figure out the least cost shipping company. */


Function CalcFreightCost ($TotalValue, 
				$BrAdd2, 
				$BrAdd3, 
				$TotalVolume, 
				$TotalWeight, 
				$FromLocation,
				$db){

	# make an array of all the words that could be the name of the destination city
	$FindCity = explode(' ', $BrAdd2 . ' ' . $BrAdd3);

	$sql = 'Select shipperid,
			kgrate *' . $TotalWeight . ' AS kgcost,
			cubrate * ' . $TotalVolume . " AS cubcost,
			fixedprice, minimumchg
		FROM freightcosts
		WHERE locationfrom = '$FromLocation'
		AND maxkgs > " . $TotalWeight . '
		AND maxcub >' . $TotalVolume . '
		AND (';

	foreach ($FindCity as $City) {

		$sql = $sql . ' destination ' . LIKE . " '" . ucwords($City) . "%' OR";

	}
	$sql = substr($sql, 0, strrpos($sql,' OR')) . ')';

	$CalcFreightCostResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo _('The freight calculation for the destination city cannot be performed because') . ' - ' . DB_error_msg($db);
	} elseif (DB_num_rows($CalcFreightCostResult)>0) {

		$CalcFreightCost =9999999;

		while ($myrow = DB_fetch_array($CalcFreightCostResult)) {

	/**********      FREIGHT CALCULATION
	IF FIXED PRICE TAKE IT IF BEST PRICE SO FAR OTHERWISE
	TAKE HIGHER OF CUBE, KG OR MINIMUM CHARGE COST 	**********/

			if ($myrow['fixedprice']!=0) {
				if ($myrow['fixedprice'] < $CalcFreightCost) {
					$CalcFreightCost=$myrow['fixedprice'];
					$CalcBestShipper =$myrow['shipperid'];
				}
			} elseif ($myrow['cubcost'] > $myrow['kgcost'] && $myrow['cubcost'] > $myrow['minimumchg'] && $myrow['cubcost'] < $CalcFreightCost) {

				$CalcFreightCost=$myrow['cubcost'];
				$CalcBestShipper =$myrow['shipperid'];

			} elseif ($myrow['kgcost']>$myrow['cubcost'] && $myrow['kgcost'] > $myrow['minimumchg'] && $myrow['kgcost'] < $CalcFreightCost) {

				$CalcFreightCost=$myrow['kgcost'];
				$CalcBestShipper =$myrow['shipperid'];

			} elseif ($myrow['minimumchg']< $CalcFreightCost){

				$CalcFreightCost=$myrow['minimumchg'];
				$CalcBestShipper =$myrow['shipperid'];

			}
		}
	} else {
		$CalcFreightCost = _('Unrecognised destination, delivery only to cities in') . ' ' . $_SESSION['CountryOfOperation'];
	}
	if ($TotalValue >= $_SESSION['FreightChargeAppliesIfLessThan']){

		/*Even though the order is over the freight free threshold - still need to calculate the best shipper to ensure get best deal*/

		$CalcFreightCost =0;
	}
	return array ($CalcFreightCost, $CalcBestShipper);
}

?>
