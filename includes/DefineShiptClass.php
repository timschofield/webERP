<?php
/* $Revision: 1.5 $ */
/* Definition of the Shipment class to hold all the information for a shipment*/

Class Shipment {

	Var $ShiptRef; /*unqique identifier for the shipment */

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	Var $SupplierID;
	var $SupplierName;
	var $CurrCode;
	var $VoyageRef;
	Var $Vessel;
	Var $ETA;
	Var $StockLocation;
	Var $Closed;

	function Shipment(){
	/*Constructor function initialises a new Shipment object */
		$this->LineItems = array();
		$this->AccumValue =0;
		$this->Closed =0;
	}

	function add_to_shipment($PODetailItem,
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
					&$db){

		$this->LineItems[$PODetailItem]= new LineDetails($PODetailItem,$OrderNo,$StockID,$ItemDescr, $QtyInvoiced, $UnitPrice, $UOM, $DelDate, $QuantityOrd, $QuantityRecd, $StdCostUnit);

		$sql = "UPDATE purchorderdetails SET shiptref = " . $this->ShiptRef . " 
			WHERE podetailitem = " . $PODetailItem;
		$ErrMsg = _('There was an error updating the purchase order detail record to make it part of shipment') . ' ' . $ShiptRef . ' ' . _('the error reported was');
		$result = DB_query($sql, $db, $ErrMsg);

		Return 1;
	}


	function remove_from_shipment($PODetailItem,&$db){

		if ($this->LineItems[$PODetailItem]->QtyInvoiced==0){

			unset($this->LineItems[$PODetailItem]);
			$sql = "UPDATE purchorderdetails SET shiptref = 0 WHERE podetailitem=" . $PODetailItem;
			$Result = DB_query($sql,$db);
		} else {
			prnMsg(_('This shipment line has a quantity invoiced and already charged to the shipment - it cannot now be removed'),'warn');
		}
	}

} /* end of class defintion */

Class LineDetails {

	Var $PODetailItem;
	Var $OrderNo;
	Var $StockID;
	Var $ItemDescription;
	Var $QtyInvoiced;
	Var $UnitPrice;
	Var $UOM;
	Var $DelDate;
	Var $QuantityOrd;
	Var $QuantityRecd;
	Var $StdCostUnit;


	function LineDetails ($PODetailItem, $OrderNo, $StockID, $ItemDescr, $QtyInvoiced, $UnitPrice, $UOM, $DelDate, $QuantityOrd, $QuantityRecd, $StdCostUnit){

	/* Constructor function to add a new LineDetail object with passed params */
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
	}
}

?>
