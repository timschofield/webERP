<?php
/* $Revision: 1.1 $ */
class SerialItem {

	var $BundleRef;
	var $BundleQty;

	//Constructor
	function SerialItem($BundleRef, $BundleQty){

	/*Do any validation here before adding the item */
		if (strlen($BundleRef)<20 AND is_numeric($BundleQty)){
			$this->BundleRef = $BundleRef;
			$this->BundleQty = $BundleQty;
			return true;
		} else {
			return false;
		}
	}


}//class SerialItem
?>
