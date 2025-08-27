<?php
/* Definition of the SpecialOrder class to hold all the information for a special quote/order and delivery
*/

class SpecialOrder {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $Initiator;
	var $QuotationRef;
	var $Comments;
	var $StkLocation;
	var $SupplierID;
	var $SupplierName;
	var $SuppCurrCode;
	var $SuppCurrExRate;
	var $SuppCurrDecimalPlaces;
	var $CustomerID;
	var $BranchCode;
	var $CustomerName;
	var $CustCurrCode;
	var $CustCurrDecimalPlaces;
	var $CustRef;
	var $BranchName;
	var $LinesOnOrder;
	var $Total;
	var $PurchOrderNo;
	var $Status;
	var $AllowPrintPO;
	var $CustCurrExRate;

	function __construct(){
	/*Constructor function initialises a new special order object */
		$this->LineItems = array();
		$this->total=0;
		$this->LinesOnOrder=0;
		$this->AllowPrintPO=0;
	}

	function SpecialOrder() {
		self::__construct();
	}

	function add_to_order($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate){
		if ($Qty!=0 AND isset($Qty)){
			$this->LineItems[$LineNo] = new LineDetails($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate);
			$this->LinesOnOrder++;
			Return 1;
		}
		Return 0;
	}


	function remove_from_order($LineNo){
		 unset($this->LineItems[$LineNo]);
	}

	function Order_Value() {
		$TotalValue=0;
		foreach ($this->LineItems as $OrderedItems) {
			$TotalValue += ($OrderedItems->Price)*($OrderedItems->Quantity);
		}
		return $TotalValue;
	}

} /* end of class defintion */

class LineDetails {

	var $LineNo;
	var $ItemDescription;
	var $Quantity;
	var $Price;
	var $Cost;
	var $StkCat;
	var $ReqDelDate;
	var $PartCode;

	function __construct($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate){

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->ReqDelDate = $ReqDelDate;
		$this->Price = $Price;
		$this->Cost = $Cost;
		$this->StkCat = $StkCat;
	}

	function LineDetails ($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate){
		self::__construct($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate);
	}

}
