<?php
/* $Revision: 1.13 $ */
/* Definition of the PurchOrder class to hold all the information for a purchase order and delivery
*/

Class PurchOrder {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $CurrCode;
	var $ExRate;
	var $Initiator;
	var $RequisitionNo;
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $DelAdd5;
	var $DelAdd6;
	var $Comments;
	var $Location;
	Var $Managed;
	Var $SupplierID;
	var $SupplierName;
	var $Orig_OrderDate;
	var $OrderNo; /*Only used for modification of existing orders otherwise only established when order committed */
	var $LinesOnOrder;
	var $PrintedPurchaseOrder;
	var $DatePurchaseOrderPrinted;
	var $total;
	var $GLLink; /*Is the GL link to stock activated only checked when order initiated or reading in for modification */

	function PurchOrder(){
	/*Constructor function initialises a new purchase order object */
		$this->LineItems = array();
		$this->total=0;
		$this->LinesOnOrder=0;
	}

	function add_to_order($LineNo,
				$StockID,
				$Serialised,
				$Controlled,
				$Qty,
				$ItemDescr,
				$Price,
				$UOM,
				$GLCode,
				$ReqDelDate,
				$ShiptRef,
				$JobRef,
				$QtyInv=0,
				$QtyRecd=0,
				$GLActName='',
				$DecimalPlaces=2){

		if ($Qty!=0 && isset($Qty)){

			$this->LineItems[$LineNo] = new LineDetails($LineNo,
								$StockID,
								$Serialised,
								$Controlled,
								$Qty,
								$ItemDescr,
								$Price,
								$UOM,
								$GLCode,
								$ReqDelDate,
								$ShiptRef,
								$JobRef,
								$QtyInv,
								$QtyRecd,
								$GLActName,
								$DecimalPlaces);
			$this->LinesOnOrder++;
			Return 1;
		}
		Return 0;
	}

	function update_order_item($LineNo,
				$Qty,
				$Price,
				$ItemDescription,
				$GLCode,
				$GLAccountName,
				$ReqDelDate,
				$ShiptRef,
				$JobRef ){

			$this->LineItems[$LineNo]->ItemDescription = $ItemDescription;
			$this->LineItems[$LineNo]->Quantity = $Qty;
			$this->LineItems[$LineNo]->Price = $Price;
			$this->LineItems[$LineNo]->GLCode = $GLCode;
			$this->LineItems[$LineNo]->GLAccountName = $GLAccountName;
			$this->LineItems[$LineNo]->ReqDelDate = $ReqDelDate;
			$this->LineItems[$LineNo]->ShiptRef = $ShiptRef;
			$this->LineItems[$LineNo]->JobRef = $JobRef;
			$this->LineItems[$LineNo]->Price = $Price;
	}

	function remove_from_order(&$LineNo){
		 $this->LineItems[$LineNo]->Deleted = True;
	}


	function Any_Already_Received(){
		/* Checks if there have been deliveries or invoiced entered against any of the line items */
		if (count($this->LineItems)>0){
		   foreach ($this->LineItems as $OrderedItems) {
			if ($OrderedItems->QtyReceived !=0 || $OrderedItems->QtyInvoiced !=0){
				return 1;
			}
		   }
		}
		return 0;
	}

	function Some_Already_Received($LineNo){
		/* Checks if there have been deliveries or amounts invoiced against a specific line item */
		if (count($this->LineItems)>0){
		   if ($this->LineItems[$LineNo]->QtyReceived !=0 || $this->LineItems[$LineNo]->QtyInvoiced !=0){
			return 1;
		   }
		}
		return 0;
	}
} /* end of class defintion */

Class LineDetails {
/* PurchOrderDetails */
	Var $LineNo;
	Var $PODetailRec;
	Var $StockID;
	Var $ItemDescription;
	Var $DecimalPlaces;
	Var $GLCode;
	Var $GLActName;
	Var $Quantity;
	Var $Price;
	Var $Units;
	Var $ReqDelDate;
	Var $QtyInv;
	Var $QtyReceived;
	Var $StandardCost;
	var $ShiptRef;
	Var $JobRef;
	Var $ReceiveQty;
	Var $Deleted;

	Var $Controlled;
	Var $Serialised;

	Var $SerialItems;  /*An array holding the batch/serial numbers and quantities in each batch*/

	function LineDetails ($LineNo, $StockItem, $Controlled, $Serialised, $Qty, $ItemDescr,  $Prc, $UOM, $GLCode, $ReqDelDate, $ShiptRef =0, $JobRef, $QtyInv, $QtyRecd, $GLActName, $DecimalPlaces){

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->StockID =$StockItem;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->StkModClass = $StkModClass;
		$this->DecimalPlaces=$DecimalPlaces;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->ReqDelDate = $ReqDelDate;
		$this->Price = $Prc;
		$this->Units = $UOM;
		$this->QtyReceived = $QtyRecd;
		$this->QtyInv = $QtyInv;
		$this->GLCode = $GLCode;
		$this->JobRef = $JobRef;
		if (is_numeric($ShiptRef)){
			$this->ShiptRef = $ShiptRef;
		} else {
			$this->ShiptRef = 0;
		}
		$this->Completed = $Completed;
		$this->GLActName = $GLActName;
		$this->ReceiveQty =0;	/*initialise these last two only */
		$this->StandardCost =0;
		$this->Deleted=False;
		$this->SerialItems = array(); /*if Controlled then need to populate this later */
		$this->SerialItemsValid=false;
	}
}

?>
