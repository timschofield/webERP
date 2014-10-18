<?php
/* $Id: DefineStockAdjustment.php 6033 2013-06-24 07:36:26Z daintree $*/
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
	var $tag;
	var $SerialItems; /*array to hold controlled items*/
	
	//Constructor
	function StockAdjustment(){
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
		$this->tag=0;
	}
}
?>