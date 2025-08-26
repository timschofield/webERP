<?php

class StockAdjustment {

	var $StockID;
	var $StockLocation;
	var $Controlled;
	var $Serialised;
	var $ItemDescription;
	var $PartUnit;
	var $StandardCost;
	var $DecimalPlaces;
	var $Quantity;
	var $Tag;
	var $Narrative;
	var $SerialItems; /*array to hold controlled items*/

	/// @todo move this in the definitions above
	function __construct() {
		$this->StockID = '';
		$this->StockLocation = '';
		$this->Controlled = '';
		$this->Serialised = '';
		$this->ItemDescription = '';
		$this->PartUnit = '';
		$this->StandardCost = 0;
		$this->DecimalPlaces = 0;
		$this->SerialItems = array();
		$this->Quantity = 0;
		$this->Tag=0;
	}
}
