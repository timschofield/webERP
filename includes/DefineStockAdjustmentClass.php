<?php
/* $Revision: 1.1 $ */

class StockAdjustment {

	var $StockID;
	Var $StockLocation;
	var $Controlled;
	var $Serialised;
	var $PartDescription;
	Var $PartUnit;
	Var $StandardCost;
	Var $DecimalPlaces;
	Var $Quantity;
	var $SerialItems; /*array to hold controlled items*/

	//Constructor
	function StockAdjustment(){
		$this->SerialItems = array();
	}
}
?>
