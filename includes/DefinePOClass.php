<?php
/* $Id$ */
/* Definition of the PurchOrder class to hold all the information for a purchase order and delivery
*/

/* Dummy for gettext */

$sDummy  = _('New Order') .
           _('Pending') .
           _('Authorised') .
           _('Rejected') .
           _('Cancelled') .
           _('Printed') .
           _('Completed');

unset($sDummy);


Class PurchOrder {

    const STATUS_NEW_ORDER  = 'New Order';
    const STATUS_PENDING    = 'Pending';
    const STATUS_AUTHORISED = 'Authorised';
    const STATUS_REJECTED   = 'Rejected';
    const STATUS_CANCELLED  = 'Cancelled';
    const STATUS_PRINTED    = 'Printed';
    const STATUS_COMPLITED  = 'Completed';

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
	var $version;
	var $Stat;
	var $StatComments;
	var $AllowPrintPO;
	var $revised;
	var $deliveryby;
	var $paymentterms;
	var $contact;
	var $port;

	function PurchOrder(){
	/*Constructor function initialises a new purchase order object */
		$this->LineItems = array();
		$this->total=0;
		$this->LinesOnOrder=0;
	}

	function add_to_order(
				$LineNo,
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
				$completed,
				$JobRef,
				$QtyInv=0,
				$QtyRecd=0,
				$GLActName='',
				$DecimalPlaces=2,
				$itemno,
				$uom,
				$ConversionFactor,
				$leadtime,
				$suppliers_partno,
				$subtotal_amount,
				$package,
				$pcunit,
				$nw,
				$gw,
				$cuft,
				$total_quantity,
				$total_amount){

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
				0,
				$JobRef,
				$QtyInv,
				$QtyRecd,
				$GLActName,
				$DecimalPlaces,
				$itemno,
				$uom,
				$ConversionFactor,
				$leadtime,
				$suppliers_partno,
				$subtotal_amount,
				$package,
				$pcunit,
				$nw,
				$gw,
				$cuft,
				$total_quantity,
				$total_amount);
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
				$JobRef ,
				$itemno,
				$uom,
				$ConversionFactor,
				$suppliers_partno,
				$subtotal_amount,
				$package,
				$pcunit,
				$nw,
				$gw,
				$cuft,
				$total_quantity,
				$total_amount){

			$this->LineItems[$LineNo]->ItemDescription = $ItemDescription;
			$this->LineItems[$LineNo]->Quantity = $Qty;
			$this->LineItems[$LineNo]->Price = $Price;
			$this->LineItems[$LineNo]->GLCode = $GLCode;
			$this->LineItems[$LineNo]->GLAccountName = $GLAccountName;
			$this->LineItems[$LineNo]->ReqDelDate = $ReqDelDate;
			$this->LineItems[$LineNo]->ShiptRef = $ShiptRef;
			$this->LineItems[$LineNo]->JobRef = $JobRef;
			$this->LineItems[$LineNo]->itemno = $itemno;
			$this->LineItems[$LineNo]->uom = $uom;
			$this->LineItems[$LineNo]->ConversionFactor = $ConversionFactor;
			$this->LineItems[$LineNo]->suppliers_partno = $suppliers_partno;
			$this->LineItems[$LineNo]->subtotal_amount = $subtotal_amount;
			$this->LineItems[$LineNo]->package = $package;
			$this->LineItems[$LineNo]->pcunit = $pcunit;
			$this->LineItems[$LineNo]->nw = $nw;
			$this->LineItems[$LineNo]->gw = $gw;
			$this->LineItems[$LineNo]->cuft = $cuft;
			$this->LineItems[$LineNo]->total_quantity = $total_quantity;
			$this->LineItems[$LineNo]->total_amount = $total_amount;
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
		if (count($this->LineItems)>0 and isset($this->LineItems[$LineNo])){
		   if ($this->LineItems[$LineNo]->QtyReceived !=0 or $this->LineItems[$LineNo]->QtyInv !=0){
			return 1;
		   }
		}
		return 0;
	}

	function Order_Value() {
		$TotalValue=0;
		foreach ($this->LineItems as $OrderedItems) {
			$TotalValue += ($OrderedItems->Price)*($OrderedItems->Quantity);
		}
		return $TotalValue;
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
	var $completed;
	Var $JobRef;
	Var $itemno;
	Var $uom;
	var $ConversionFactor;
	Var $suppliers_partno;
	Var $subtotal_amount;
	Var $leadtime;
	Var $package;
	Var $pcunit;
	Var $nw;
	Var $gw;
	Var $cuft;
	Var $total_quantity;
	Var $total_amount;
	Var $ReceiveQty;
	Var $Deleted;
	Var $Controlled;
	Var $Serialised;
	Var $SerialItems;  /*An array holding the batch/serial numbers and quantities in each batch*/

	function LineDetails (
				$LineNo,
				$StockItem,
				$Serialised,
				$Controlled,
				$Qty,
				$ItemDescr,
				$Prc,
				$UOM,
				$GLCode,
				$ReqDelDate,
				$ShiptRef =0,
				$Completed,
				$JobRef,
				$QtyInv,
				$QtyRecd,
				$GLActName,
				$DecimalPlaces,
				$itemno,
				$uom,
				$ConversionFactor,
				$suppliers_partno,
				$subtotal_amount,
				$leadtime,
				$package,
				$pcunit,
				$nw,
				$gw,
				$cuft,
				$total_quantity,
				$total_amount)
	{

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->StockID =$StockItem;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
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
		$this->itemno = $itemno;
		$this->uom = $uom;
		$this->ConversionFactor = $ConversionFactor;
		$this->suppliers_partno = $suppliers_partno;
		$this->subtotal_amount = $subtotal_amount;
		$this->leadtime = $leadtime;
		$this->pcunit = $pcunit;
		$this->nw = $nw;
		$this->gw = $gw;
		$this->cuft = $cuft;
		$this->total_quantity = $total_quantity;
		$this->total_amount = $total_amount;
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
