<?php
/* $Revision: 1.7 $ */

function ValidBundleRef ($StockID, $LocCode, $BundleRef){
	global $db;

	$SQL = "SELECT quantity 
				FROM stockserialitems 
				WHERE stockid='" . $StockID . "' 
				AND loccode ='" . $LocCode . "' 
				AND serialno='" . $BundleRef . "'";
	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result)==0){
		return 0;
	} else {
		$myrow = DB_fetch_row($Result);
		return $myrow[0]; /*The quantity in the bundle */
	}
}

class SerialItem {

	var $BundleRef;
	var $BundleQty;

	//Constructor
	function SerialItem($BundleRef, $BundleQty){

		$this->BundleRef = $BundleRef;
		$this->BundleQty = $BundleQty;
	}
}//class SerialItem
?>
