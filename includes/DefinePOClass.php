<?php
/* $Id$ */
/* Definition of the PurchOrder class to hold all the information for a purchase order and delivery
*/


Class PurchOrder {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $CurrCode;
	var $ExRate;
	var $Initiator;
	var $DeliveryDate;
	var $RequisitionNo;
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $DelAdd5;
	var $DelAdd6;
	var $Tel;
	var $SuppDelAdd1;
	var $SuppDelAdd2;
	var $SuppDelAdd3;
	var $SuppDelAdd4;
	var $SuppDelAdd5;
	var $SuppDelAdd6;
	var $SupplierContact;
	var $SuppTel;
	var $Comments;
	var $Location;
	var $Managed;
	var $SupplierID;
	var $SupplierName;
	var $Orig_OrderDate;
	var $OrderNo; /*Only used for modification of existing orders otherwise only established when order committed */
	var $LinesOnOrder;
	var $PrintedPurchaseOrder;
	var $DatePurchaseOrderPrinted;
	var $Total;
	var $GLLink; /*Is the GL link to stock activated only checked when order initiated or reading in for modification */
	var $Version;
	var $Status;
	var $StatusComments;
	var $AllowPrintPO;
	var $Revised;
	var $DeliveryBy;
	var $PaymentTerms;
	var $Contact;
	var $Port;

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
											$Completed,
											$JobRef,
											$QtyInv=0,
											$QtyRecd=0,
											$GLActName='',
											$DecimalPlaces=2,
											$ItemNo,
											$SuppUOM,
											$ConversionFactor=1,
											$LeadTime=1,
											$Suppliers_PartNo='',
											$SubTotal_Amount=0,
											$Package=0,
											$PcUnit=0,
											$nw=0,
											$gw=0,
											$CuFt=0,
											$Total_Quantity=0,
											$Total_Amount=0,
											$AssetID=0){
							
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
																								$ItemNo,
																								$SuppUOM,
																								$ConversionFactor,
																								$LeadTime,
																								$Suppliers_PartNo,
																								$SubTotal_Amount,
																								$Package,
																								$PcUnit,
																								$nw,
																								$gw,
																								$CuFt,
																								$Total_Quantity,
																								$Total_Amount,
																								$AssetID);
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
														$ItemNo,
														$uom,
														$ConversionFactor,
														$Suppliers_PartNo,
														$SubTotal_Amount,
														$Package,
														$PcUnit,
														$nw,
														$gw,
														$CuFt,
														$Total_Quantity,
														$Total_Amount){

			$this->LineItems[$LineNo]->ItemDescription = $ItemDescription;
			$this->LineItems[$LineNo]->Quantity = $Qty;
			$this->LineItems[$LineNo]->Price = $Price;
			$this->LineItems[$LineNo]->GLCode = $GLCode;
			$this->LineItems[$LineNo]->GLAccountName = $GLAccountName;
			$this->LineItems[$LineNo]->ReqDelDate = $ReqDelDate;
			$this->LineItems[$LineNo]->ShiptRef = $ShiptRef;
			$this->LineItems[$LineNo]->JobRef = $JobRef;
			$this->LineItems[$LineNo]->ItemNo = $ItemNo;
			$this->LineItems[$LineNo]->uom = $uom;
			$this->LineItems[$LineNo]->ConversionFactor = $ConversionFactor;
			$this->LineItems[$LineNo]->Suppliers_PartNo = $Suppliers_PartNo;
			$this->LineItems[$LineNo]->Subtotal_Amount = $SubTotal_Amount;
			$this->LineItems[$LineNo]->Package = $Package;
			$this->LineItems[$LineNo]->PcUnit = $PcUnit;
			$this->LineItems[$LineNo]->nw = $nw;
			$this->LineItems[$LineNo]->gw = $gw;
			$this->LineItems[$LineNo]->CuFt = $CuFt;
			$this->LineItems[$LineNo]->Total_Quantity = $Total_Quantity;
			$this->LineItems[$LineNo]->Total_Amount = $Total_Amount;
	}

	function remove_from_order(&$LineNo){
		 $this->LineItems[$LineNo]->Deleted = True;
	}


	function Any_Already_Received(){
		/* Checks if there have been deliveries or invoiced entered against any of the line items */
		if (count($this->LineItems)>0){
		   foreach ($this->LineItems as $OrderedItems) {
			if ($OrderedItems->QtyReceived !=0 OR $OrderedItems->QtyInvoiced !=0){
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
	Var $Units; //errrrr what is uom for then
	Var $ReqDelDate;
	Var $QtyInv;
	Var $QtyReceived;
	Var $StandardCost;
	var $ShiptRef;
	var $Completed;
	Var $JobRef;
	Var $ItemNo;
	Var $SuppUOM;
	var $ConversionFactor;
	Var $Suppliers_PartNo;
	Var $SubTotal_Amount;
	Var $LeadTime;
	Var $Package;
	Var $PcUnit;
	Var $nw;
	Var $gw;
	Var $CuFt;
	Var $Total_Quantity;
	Var $Total_Amount;
	Var $ReceiveQty;
	Var $Deleted;
	Var $Controlled;
	Var $Serialised;
	Var $SerialItems;  /*An array holding the batch/serial numbers and quantities in each batch*/
	Var $AssetID;

	function LineDetails (	$LineNo,
											$StockItem,
											$Serialised,
											$Controlled,
											$Qty,
											$ItemDescr,
											$Price,
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
											$ItemNo,
											$SuppUOM,
											$ConversionFactor,
											$Suppliers_PartNo,
											$SubTotal_Amount,
											$LeadTime,
											$Package,
											$PcUnit,
											$nw,
											$gw,
											$CuFt,
											$Total_Quantity,
											$Total_Amount,
											$AssetID)	{

	/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNo = $LineNo;
		$this->StockID =$StockItem;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->DecimalPlaces=$DecimalPlaces;
		$this->ItemDescription = $ItemDescr;
		$this->Quantity = $Qty;
		$this->ReqDelDate = $ReqDelDate;
		$this->Price = $Price;
		$this->Units = $UOM;
		$this->QtyReceived = $QtyRecd;
		$this->QtyInv = $QtyInv;
		$this->GLCode = $GLCode;
		$this->JobRef = $JobRef;
		$this->ItemNo = $ItemNo;
		$this->SuppUOM = $SuppUOM;
		$this->ConversionFactor = $ConversionFactor;
		$this->Suppliers_PartNo = $Suppliers_PartNo;
		$this->Subtotal_Amount = $SubTotal_Amount;
		$this->LeadTime = $LeadTime;
		$this->PcUnit = $PcUnit;
		$this->nw = $nw;
		$this->gw = $gw;
		$this->CuFt = $CuFt;
		$this->Total_Quantity = $Total_Quantity;
		$this->Total_Amount = $Total_Amount;
		if (is_numeric($ShiptRef)){
			$this->ShiptRef = $ShiptRef;
		} else {
			$this->ShiptRef = 0;
		}
		$this->Completed = $Completed;
		$this->GLActName = $GLActName;
		$this->ReceiveQty = 0;	/*initialise these last two only */
		$this->StandardCost = 0;
		$this->Deleted = false;
		$this->SerialItems = array(); /*if Controlled then need to populate this later */
		$this->SerialItemsValid=false;
		$this->AssetID= $AssetID;
	}
}
?>