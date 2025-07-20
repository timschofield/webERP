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
	var $AdjustmentReason;

	//Constructor
	function __construct(){
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
		$this->AdjustmentReason=0;
		$this->Narrative='';
	}

	function StockAdjustment() {
		self::__construct();
	}
}
