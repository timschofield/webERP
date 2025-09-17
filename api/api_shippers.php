<?php

if (!isset($PathPrefix)) {
	header('Location: ../');
	exit();
}

/** This function returns a list of the stock shipper id's
 * currently setup on webERP
 */
function GetShipperList($User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT shipper_id FROM shippers';
	$Result = DB_query($SQL);
	$i=0;
	$ShipperList = array();
	while ($MyRow=DB_fetch_array($Result)) {
		$ShipperList[$i]=$MyRow[0];
		$i++;
	}
	return $ShipperList;
}

/** This function takes as a parameter a shipper id
 * and returns an array containing the details of the selected
 * shipper.
 */
function GetShipperDetails($Shipper, $User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db)=='integer') {
		$Errors[0]=NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM shippers WHERE shipper_id='" . $Shipper."'";
	$Result = DB_query($SQL);
	return DB_fetch_array($Result);
}
