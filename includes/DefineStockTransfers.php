<?php
/* $Revision: 1.6 $ */

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
	Var $ShipQty;
	Var $PrevRecvQty;
	Var $Quantity;
	Var $PartUnit;
	var $Controlled;
	var $Serialised;
	Var $DecimalPlaces;
	var $SerialItems; /*array to hold controlled items*/
//Constructor
	function LineItem($StockID,
			$ItemDescription,
			$Quantity,
			$PartUnit,
			$Controlled,
			$Serialised,
			$DecimalPlaces){

		$this->StockID = $StockID;
		$this->ItemDescription = $ItemDescription;
		$this->PartUnit = $PartUnit;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
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
