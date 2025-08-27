<?php

function ValidBundleRef ($StockID, $LocCode, $BundleRef) {
	$SQL = "SELECT quantity
				FROM stockserialitems
				WHERE stockid='" . $StockID . "'
				AND loccode ='" . $LocCode . "'
				AND serialno='" . $BundleRef . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0){
		return 0;
	} else {
		$MyRow = DB_fetch_row($Result);
		return $MyRow[0]; /*The quantity in the bundle */
	}
}

function GetExpiryDate ($StockID, $LocCode, $BundleRef) {
	$SQL = "SELECT expirationdate
				FROM stockserialitems
				WHERE stockid = '" . $StockID . "'
				AND loccode = '" . $LocCode . "'
				AND serialno = '" . $BundleRef . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0){
		return '1000-01-01';
	} else {
		$MyRow = DB_fetch_row($Result);
		return ConvertSQLDate($MyRow[0]);
	}
}

class SerialItem {

	var $BundleRef;
	var $BundleQty;
	var $ExpiryDate;

  function __construct($BundleRef, $BundleQty, $ExpiryDate='1000-01-01') {
		$this->BundleRef = $BundleRef;
		$this->BundleQty = $BundleQty;
		$this->ExpiryDate = $ExpiryDate;
	}

	function SerialItem($BundleRef, $BundleQty, $ExpiryDate='1000-01-01'){
		self::__construct($BundleRef, $BundleQty, $ExpiryDate='1000-01-01');
	}
}
