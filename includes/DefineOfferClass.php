<?php
/* $Id$ */
/* Definition of the Offer class to hold all the information for a supplier offer
*/


Class Offer {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $CurrCode;
	var $ExRate;
	var $Initiator;
	var $deliverydate;
	var $RequisitionNo;
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $DelAdd5;
	var $DelAdd6;
	var $tel;
	var $suppDelAdd1;
	var $suppDelAdd2;
	var $suppDelAdd3;
	var $suppDelAdd4;
	var $suppDelAdd5;
	var $suppDelAdd6;
	var $SupplierContact;
	var $supptel;
	var $Comments;
	var $Location;
	var $Managed;
	var $SupplierID;
	var $SupplierName;
	var $Orig_OrderDate;
	var $OrderNo; /*Only used for modification of existing orders otherwise only established when order committed */
	var $LinesOnOffer;
	var $PrintedPurchaseOrder;
	var $DatePurchaseOrderPrinted;
	var $total;
	var $GLLink; /*Is the GL link to stock activated only checked when order initiated or reading in for modification */
	var $version;
	var $Stat;
	var $StatComments;
	var $AllowPrintPO;
	var $revised;
	var $deliveryby;
	var $paymentterms;
	var $contact;
	var $port;

	function Offer(){
	/*Constructor function initialises a new purchase offer object */
		$this->LineItems = array();
		$this->total=0;
		$this->LinesOnOffer=0;
	}

	function add_to_offer(
				$LineNo,
				$StockID,
				$Qty,
				$ItemDescr,
				$Price,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate){

		if (isset($Qty) and $Qty!=0){

			$this->LineItems[$LineNo] = new LineDetails($LineNo,
				$StockID,
				$Qty,
				$ItemDescr,
				$Price,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate);
			$this->LinesOnOffer++;
			Return 1;
		}
		Return 0;
	}

	function update_offer_item($LineNo,
				$Qty,
				$Price,
				$ExpiryDate){

			$this->LineItems[$LineNo]->Quantity = $Qty;
			$this->LineItems[$LineNo]->Price = $Price;
			$this->LineItems[$LineNo]->ExpiryDate = $ExpiryDate;
	}

	function remove_from_offer(&$LineNo){
		 $this->LineItems[$LineNo]->Deleted = True;
	}


	function Offer_Value() {
		$TotalValue=0;
		foreach ($this->LineItems as $OrderedItems) {
			$TotalValue += ($OrderedItems->Price)*($OrderedItems->Quantity);
		}
		return $TotalValue;
	}
} /* end of class defintion */

Class LineDetails {
/* PurchOrderDetails */
	var $LineNo;
	var $StockID;
	var $ItemDescription;
	var $Quantity;
	var $Price;
	var $Units;
	var $DecimalPlaces;
	var $Deleted;
	var $ExpiryDate;

	function LineDetails (
				$LineNo,
				$StockItem,
				$Qty,
				$ItemDescr,
				$Prc,
				$UOM,
				$DecimalPlaces,
				$ExpiryDate)
	{

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->StockID =$StockItem;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->Price = $Prc;
		$this->Units = $UOM;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->ExpiryDate = $ExpiryDate;
		$this->Deleted = False;
	}
}

?>