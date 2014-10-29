<?php
/* $Id: api_shippers.php 6941 2014-10-26 23:18:08Z daintree $*/

/* This function returns a list of the stock shipper id's
 * currently setup on webERP
 */

	function GetShipperList($User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT shipper_id FROM shippers';
		$result = DB_query($sql);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$ShipperList[$i]=$myrow[0];
			$i++;
		}
		return $ShipperList;
	}

/* This function takes as a parameter a shipper id
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
		$sql = "SELECT * FROM shippers WHERE shipper_id='" . $Shipper."'";
		$result = DB_query($sql);
		return DB_fetch_array($result);
	}
?>