<?php

/**
 * Class to hold all the information for a shipment
 */
class Shipment {

	var $ShiptRef; /*unqique identifier for the shipment */
	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $SupplierID;
	var $SupplierName;
	var $CurrCode;
	var $VoyageRef;
	var $Vessel;
	var $ETA;
	var $StockLocation;
	var $Closed;
	var $CurrDecimalPlaces;
	var $AccumValue;

	function __construct() {
		$this->LineItems = array();
		$this->AccumValue =0;
		$this->Closed =0;
	}

	function Add_To_Shipment($PODetailItem,
							$OrderNo,
							$StockID,
							$ItemDescr,
							$QtyInvoiced,
							$UnitPrice,
							$UOM,
							$DelDate,
							$QuantityOrd,
							$QuantityRecd,
							$StdCostUnit,
							$DecimalPlaces){

		$this->LineItems[$PODetailItem]= new LineDetails($PODetailItem,
														$OrderNo,
														$StockID,
														$ItemDescr,
														$QtyInvoiced,
														$UnitPrice,
														$UOM,
														$DelDate,
														$QuantityOrd,
														$QuantityRecd,
														$StdCostUnit,
														$DecimalPlaces);

		$SQL = "UPDATE purchorderdetails SET shiptref = '" . $this->ShiptRef . "'
			WHERE podetailitem = '" . $PODetailItem . "'";
		$ErrMsg = __('There was an error updating the purchase order detail record to make it part of shipment') . ' ' . $this->ShiptRef . ' ' . __('the error reported was');
		$Result = DB_query($SQL, $ErrMsg);

		Return 1;
	}

	function Remove_From_Shipment($PODetailItem) {

		if ($this->LineItems[$PODetailItem]->QtyInvoiced==0){

			unset($this->LineItems[$PODetailItem]);
			$SQL = "UPDATE purchorderdetails SET shiptref = 0 WHERE podetailitem='" . $PODetailItem . "'";
			$Result = DB_query($SQL);
		} else {
			prnMsg(__('This shipment line has a quantity invoiced and already charged to the shipment - it cannot now be removed'),'warn');
		}
	}
}

class LineDetails {

	var $PODetailItem;
	var $OrderNo;
	var $StockID;
	var $ItemDescription;
	var $QtyInvoiced;
	var $UnitPrice;
	var $UOM;
	var $DelDate;
	var $QuantityOrd;
	var $QuantityRecd;
	var $StdCostUnit;
	var $DecimalPlaces;

	function __construct($PODetailItem,
							$OrderNo,
							$StockID,
							$ItemDescr,
							$QtyInvoiced,
							$UnitPrice,
							$UOM,
							$DelDate,
							$QuantityOrd,
							$QuantityRecd,
							$StdCostUnit,
							$DecimalPlaces=2) {

		$this->PODetailItem = $PODetailItem;
		$this->OrderNo = $OrderNo;
		$this->StockID =$StockID;
		$this->ItemDescription = $ItemDescr;
		$this->QtyInvoiced = $QtyInvoiced;
		$this->DelDate = $DelDate;
		$this->UnitPrice = $UnitPrice;
		$this->UOM = $UOM;
		$this->QuantityRecd = $QuantityRecd;
		$this->QuantityOrd = $QuantityOrd;
		$this->StdCostUnit = $StdCostUnit;
		$this->DecimalPlaces = $DecimalPlaces;
	}

	function LineDetails($PODetailItem,
							$OrderNo,
							$StockID,
							$ItemDescr,
							$QtyInvoiced,
							$UnitPrice,
							$UOM,
							$DelDate,
							$QuantityOrd,
							$QuantityRecd,
							$StdCostUnit,
							$DecimalPlaces=2) {
			self::__construct($PODetailItem,
							$OrderNo,
							$StockID,
							$ItemDescr,
							$QtyInvoiced,
							$UnitPrice,
							$UOM,
							$DelDate,
							$QuantityOrd,
							$QuantityRecd,
							$StdCostUnit,
							$DecimalPlaces=2);
	}
}
