<?php
/* $Revision: 1.3 $ */
/* Definition of the SpecialOrder class to hold all the information for a special quote/order and delivery
*/

Class SpecialOrder {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $Initiator;
	var $QuotationRef;
	var $Comments;
	var $StkLocation;
	var $SupplierID;
	var $SupplierName;
	var $SuppCurrCode;
	var $SuppCurrExRate;
	var $CustomerID;
	var $BranchCode;
	var $CustomerName;
	var $CustCurrCode;
	var $CustRef;
	var $BranchName;
	var $LinesOnOrder;
	var $total;
	var $PurchOrderNo;

	function SpecialOrder(){
	/*Constructor function initialises a new special order object */
		$this->LineItems = array();
		$this->total=0;
		$this->LinesOnOrder=0;
	}

	function add_to_order($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate){
		if ($Qty!=0 && isset($Qty)){
			$this->LineItems[$LineNo] = new LineDetails($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate);
			$this->LinesOnOrder++;
			Return 1;
		}
		Return 0;
	}


	function remove_from_order(&$LineNo){
		 unset($this->LineItems[$LineNo]);
	}


} /* end of class defintion */

Class LineDetails {

	Var $LineNo;
	Var $ItemDescription;
	Var $Quantity;
	Var $Price;
	Var $Cost;
	Var $StkCat;
	Var $ReqDelDate;
	Var $PartCode;

	function LineDetails ($LineNo, $Qty, $ItemDescr, $Price, $Cost, $StkCat, $ReqDelDate){

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->ReqDelDate = $ReqDelDate;
		$this->Price = $Price;
		$this->Cost = $Cost;
		$this->StkCat = $StkCat;
	}
}

?>
