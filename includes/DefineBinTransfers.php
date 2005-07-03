<?php
/* $Revision: 1.1 $ */

/*Class to hold stock transfer records */

class StockTransfer {

	Var $TrfID;
	Var $StockLocationFrom;
	Var $StockMainLocFrom;//for use with bin transfers, not used with location transfers
	Var $StockLocationFromName;
	Var $StockLocationTo;
	Var $StockMainLocTo; //for use with bin transfers, not used with location transfers
	Var $StockLocationToName;
	Var $TranDate;
	Var $TransferItem; /*Array of LineItems */

	function StockTransfer($TrfID,
				$StockMainLocFrom,
				$StockLocationFrom,
				$StockLocationFromName,
				$StockMainLocTo,
				$StockLocationTo,
				$StockLocationToName,
				$TranDate )	{

		$this->TrfID = $TrfID;
		$this->StockMainLocFrom = $StockMainLocFrom;
		$this->StockLocationFrom = $StockLocationFrom;
		$this->StockLocationFromName = $StockLocationFromName;
		$this->StockMainLocTo =$StockMainLocTo; //for use with bin transfers, not used with location transfers
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
