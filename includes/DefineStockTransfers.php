<?php
/* $Id: DefineStockTransfers.php 7296 2015-05-10 04:54:12Z rchacon $*/

/*Class to hold stock transfer records */

class StockTransfer {

	Var $TrfID;
	Var $StockLocationFrom;
	Var $StockLocationFromName;
	Var $StockLocationFromAccount;
	Var $StockLocationTo;
	Var $StockLocationToName;
	Var $StockLocationToAccount;
	Var $TranDate;
	Var $TransferItem; /*Array of LineItems */

	function StockTransfer($TrfID,
				$StockLocationFrom,
				$StockLocationFromName,
				$StockLocationFromAccount,
				$StockLocationTo,
				$StockLocationToName,
				$StockLocationToAccount,
				$TranDate )	{

		$this->TrfID = $TrfID;
		$this->StockLocationFrom = $StockLocationFrom;
		$this->StockLocationFromName = $StockLocationFromName;
		$this->StockLocationFromAccount = $StockLocationFromAccount;
		$this->StockLocationTo =$StockLocationTo;
		$this->StockLocationToName =$StockLocationToName;
		$this->StockLocationToAccount =$StockLocationToAccount;
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
