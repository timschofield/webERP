<?php
/* $Revision: 1.3 $ */

function ValidBundleRef ($StockID, $LocCode, $BundleRef){
	global $db;

	$Result = DB_query("SELECT Quantity FROM StockSerialItems WHERE StockID='" . $StockID . "' AND LocCode ='" . $LocCode . "' AND SerialNo='" . $BundleRef . "'" ,$db);
	if (DB_num_rows($Result)==0){
		echo "<BR>The batch reference/serial number " . $BundleRef . " does not refer to a currently existing item of " . $StockID . " at " . $LocCode;
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

	/*Do any validation here before adding the item */
		if (strlen($BundleRef)<20 AND strlen($BundleRef)>1 AND is_numeric($BundleQty)){
			$this->BundleRef = $BundleRef;
			$this->BundleQty = $BundleQty;
			return true;
		} else {
			return false;
		}
	}


}//class SerialItem
?>
