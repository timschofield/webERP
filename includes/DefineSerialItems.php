<?php
/* $Revision: 1.6 $ */

function ValidBundleRef ($StockID, $LocCode, $BundleRef){
	global $db;

	$Result = DB_query("SELECT quantity 
				FROM stockserialitems 
				WHERE stockid='" . $StockID . "' 
				AND loccode ='" . $LocCode . "' 
				AND serialno='" . $BundleRef . "'" ,$db);
	if (DB_num_rows($Result)==0){
		echo '<BR>';
		prnMsg(_('The batch reference/serial number '). ' ' .  $BundleRef . ' ' . _('does not refer to a currently existing item of'). ' '. $StockID . ' '. _('at').' ' . $LocCode, 'error');
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
