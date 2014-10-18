<?php
/* $Id: FreightCalculation.inc 6444 2013-11-28 01:00:08Z tehonu $*/
/*Function to calculate the freight cost.
Freight cost is determined by looking for a match of destination city from the Address2 and Address3 fields then looking through the freight company rates for the total KGs and Cubic meters  to figure out the least cost shipping company. */


Function CalcFreightCost ($TotalValue,
				$BrAdd2,
				$BrAdd3,
				$BrAdd4,
				$BrAdd5,
				$BrAddCountry,
				$TotalVolume,
				$TotalWeight,
				$FromLocation,
				$Currency,
				$db){
	
	$ParameterError = FALSE;
	if ((!isset($BrAdd2)) AND (!isset($BrAdd3)) AND (!isset($BrAdd4)) AND (!isset($BrAdd5)) AND (!isset($BrAddCountry))){
		// No address field to detect destination ==> ERROR
		$ParameterError = TRUE;
	}
	if ((!isset($TotalVolume)) AND (!isset($TotalWeight))){
		// No weight AND no volume ==> ERROR
		$ParameterError = TRUE;
	}
	if (!isset($FromLocation)){
		// No location FROM ==> ERROR
		$ParameterError = TRUE;
	}
	if (!isset($Currency)){
		// No Currency ==> ERROR
		$ParameterError = TRUE;
	}
	if($ParameterError){
		return array ("NOT AVAILABLE", "NOT AVAILABLE");	
	}
	// All parameters are OK, so we move ahead...
	
	// make an array of all the words that could be the name of the destination zone (city, state or ZIP)
	$FindCity = explode(' ', $BrAdd2 . ' ' . $BrAdd3 . ' ' . $BrAdd4 . ' ' . $BrAdd5);

	$sql = "SELECT shipperid,
				kgrate * " . $TotalWeight . " AS kgcost,
				cubrate * " . $TotalVolume . " AS cubcost,
				fixedprice, 
				minimumchg
			FROM freightcosts
			WHERE locationfrom = '" . $FromLocation . "'
			AND destinationcountry = '" . $BrAddCountry . "'
			AND maxkgs > " . $TotalWeight . "
			AND maxcub >" . $TotalVolume . " ";
			if (($BrAdd2 != '') OR ($BrAdd3 != '') OR ($BrAdd4 != '') OR ($BrAdd5 != '')){
				// if there is some details of the address besides the country
				$sql .= " AND (";
				foreach ($FindCity as $City) {
					if (ucwords($City) != ''){
						$sql = $sql . " destination LIKE '" . ucwords($City) . "%' OR";
					}
				}
				if ($BrAddCountry != $CountriesArray[$_SESSION['CountryOfOperation']]){
					/* For international shipments empty destination (ANY) is allowed */
					$sql = $sql . " destination = '' OR";
				}
				$sql = mb_substr($sql, 0, mb_strrpos($sql,' OR')) . ')';
			}
// echo ($sql);
	$CalcFreightCostResult = DB_query($sql,$db);
	if (DB_error_no($db) !=0) {
		echo _('The freight calculation for the destination city cannot be performed because') . ' - ' . DB_error_msg($db) . ' - ' . $sql;
	} elseif (DB_num_rows($CalcFreightCostResult)>0) {

		$CalcFreightCost =9999999999;

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
		$CalcFreightCost = "NOT AVAILABLE";
	}
	if ($TotalValue >= $_SESSION['FreightChargeAppliesIfLessThan']){
		/*Even though the order is over the freight free threshold - still need to calculate the best shipper to ensure get best deal*/
		$CalcFreightCost =0;
	}

	if ($Currency != $_SESSION['CompanyRecord']['currencydefault']){
		$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $Currency . "'",$db);
		if (DB_num_rows($ExRateResult)>0){
			$ExRateRow = DB_fetch_row($ExRateResult);
			$ExRate = $ExRateRow[0];
		} else {
			$ExRate =1;
		}
		if ($CalcFreightCost != "NOT AVAILABLE"){
			$CalcFreightCost = $CalcFreightCost * $ExRate;
		}
	}
	
	return array ($CalcFreightCost, $CalcBestShipper);
}

?>
