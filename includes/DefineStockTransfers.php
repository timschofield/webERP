<?php
/* $Id: DefineStockTransfers.php 5766 2012-12-19 06:45:03Z daintree $*/

/*Class to hold stock transfer records */

class StockTransfer {

	Var $TrfID;
	Var $StockLocationFrom;
	Var $StockLocationFromName;
	Var $StockLocationTo;
	Var $StockLocationToName;
	Var $TranDate;
	Var $TransferItem; /*Array of LineItems */

	function StockTransfer($TrfID,
				$StockLocationFrom,
				$StockLocationFromName,
				$StockLocationTo,
				$StockLocationToName,
				$TranDate )	{

		$this->TrfID = $TrfID;
		$this->StockLocationFrom = $StockLocationFrom;
		$this->StockLocationFromName = $StockLocationFromName;
		$this->StockLocationTo =$StockLocationTo;
		$this->StockLocationToName =$StockLocationToName;
		$this->TranDate = $TranDate;
		$this->TransferItem=array(); /*Array of LineItem s */
	}
}

class LineItem {
	var $StockID;
	var $ItemDescription;
	var $ShipQty;
	var $PrevRecvQty;
	var $Quantity;
	var $PartUnit;
	var $Controlled;
	var $Serialised;
	var $DecimalPlaces;
	var $Perishable;
	var $SerialItems; /*array to hold controlled items*/
//Constructor
	function LineItem($StockID,
			$ItemDescription,
			$Quantity,
			$PartUnit,
			$Controlled,
			$Serialised,
			$Perishable,
			$DecimalPlaces){

		$this->StockID = $StockID;
		$this->ItemDescription = $ItemDescription;
		$this->PartUnit = $PartUnit;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->Perishable = $Perishable;
		$this->ShipQty = $Quantity;
		if ($this->Controlled==1){
			$this->Quantity = 0;
		} else {
			$this->Quantity = $Quantity;
		}
		$this->SerialItems = array();
	}
}
?>
