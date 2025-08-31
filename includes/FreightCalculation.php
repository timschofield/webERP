<?php

/* Function to calculate the freight cost.
Freight cost is determined by looking for a match of destination city from the Address2 and Address3 fields then looking through the freight company rates for the total KGs and Cubic meters  to figure out the least cost shipping company. */

function CalcFreightCost ($TotalValue,
							$BrAdd2,
							$BrAdd3,
							$BrAdd4,
							$BrAdd5,
							$BrAddCountry,
							$TotalVolume,
							$TotalWeight,
							$FromLocation,
							$Currency){

	$CalcFreightCost =9999999999;
	$CalcBestShipper ='';
	global $CountriesArray;

	$ParameterError = false;
	if ((!isset($BrAdd2)) AND (!isset($BrAdd3)) AND (!isset($BrAdd4)) AND (!isset($BrAdd5)) AND (!isset($BrAddCountry))){
		// No address field to detect destination ==> ERROR
		$ParameterError = true;
	}
	if ((!isset($TotalVolume)) AND (!isset($TotalWeight))){
		// No weight AND no volume ==> ERROR
		$ParameterError = true;
	}
	if (!isset($FromLocation)){
		// No location FROM ==> ERROR
		$ParameterError = true;
	}
	if (!isset($Currency)){
		// No Currency ==> ERROR
		$ParameterError = true;
	}
	if($ParameterError){
		return array ("NOT AVAILABLE", "NOT AVAILABLE");
	}
	// All parameters are OK, so we move ahead...

	// make an array of all the words that could be the name of the destination zone (city, state or ZIP)
	$FindCity = array($BrAdd2, $BrAdd3, $BrAdd4, $BrAdd5);

	$SQL = "SELECT shipperid,
				kgrate * " . $TotalWeight . " AS kgcost,
				cubrate * " . $TotalVolume . " AS cubcost,
				fixedprice,
				minimumchg
			FROM freightcosts
			WHERE locationfrom = '" . $FromLocation . "'
			AND destinationcountry = '" . strtoupper($BrAddCountry) . "'
			AND maxkgs > " . $TotalWeight . "
			AND maxcub >" . $TotalVolume . "  AND (";

	//ALL suburbs and cities are compared in upper case - so data in freight tables must be in upper case too
	foreach ($FindCity as $City) {
		if ($City != ''){
			$SQL .= " destination LIKE '" .  strtoupper($City) . "%' OR";
		}
	}
	if ($BrAddCountry != $CountriesArray[$_SESSION['CountryOfOperation']]){
		/* For international shipments empty destination (ANY) is allowed */
		$SQL .= " destination = '' OR";
	}
	$SQL = mb_substr($SQL, 0, mb_strrpos($SQL,' OR')) . ')';

	$CalcFreightCostResult = DB_query($SQL);
	if (DB_error_no() !=0) {
		echo __('The freight calculation for the destination city cannot be performed because') . ' - ' . DB_error_msg() . ' - ' . $SQL;
	} elseif (DB_num_rows($CalcFreightCostResult)>0) {

		while ($MyRow = DB_fetch_array($CalcFreightCostResult)) {

			/**********      FREIGHT CALCULATION
			IF FIXED PRICE TAKE IT IF BEST PRICE SO FAR OTHERWISE
			TAKE HIGHER OF CUBE, KG OR MINIMUM CHARGE COST 	**********/

			if ($MyRow['fixedprice']!=0) {
				if ($MyRow['fixedprice'] < $CalcFreightCost) {
					$CalcFreightCost=$MyRow['fixedprice'];
					$CalcBestShipper =$MyRow['shipperid'];
				}
			} elseif ($MyRow['cubcost'] > $MyRow['kgcost'] && $MyRow['cubcost'] > $MyRow['minimumchg'] && $MyRow['cubcost'] <= $CalcFreightCost) {

				$CalcFreightCost=$MyRow['cubcost'];
				$CalcBestShipper =$MyRow['shipperid'];

			} elseif ($MyRow['kgcost']>$MyRow['cubcost'] && $MyRow['kgcost'] > $MyRow['minimumchg'] && $MyRow['kgcost'] <= $CalcFreightCost) {

				$CalcFreightCost=$MyRow['kgcost'];
				$CalcBestShipper =$MyRow['shipperid'];

			} elseif ($MyRow['minimumchg'] < $CalcFreightCost){

				$CalcFreightCost=$MyRow['minimumchg'];
				$CalcBestShipper =$MyRow['shipperid'];

			}
		}
	} else {
		$CalcFreightCost = "NOT AVAILABLE";
	}
	if ($TotalValue >= $_SESSION['FreightChargeAppliesIfLessThan'] AND $_SESSION['FreightChargeAppliesIfLessThan']!=0){
		/*Even though the order is over the freight free threshold - still need to calculate the best shipper to ensure get best deal*/
		$CalcFreightCost =0;
	}

	if ($Currency != $_SESSION['CompanyRecord']['currencydefault']){
		$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $Currency . "'");
		if (DB_num_rows($ExRateResult)>0){
			$ExRateRow = DB_fetch_row($ExRateResult);
			$ExRate = $ExRateRow[0];
		} else {
			$ExRate =1;
		}
		if ($CalcFreightCost != "NOT AVAILABLE"){
			$CalcFreightCost *= $ExRate;
		}
	}

	return array ($CalcFreightCost, $CalcBestShipper);
}
