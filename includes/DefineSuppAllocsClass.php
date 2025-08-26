<?php

/* definition of the Supplier Payment/Credit Note allocation class */

class Allocation {

	var $Allocs; /*array of transactions allocated to */
	var $AllocTrans; /*The ID of the transaction being allocated */
	var $SupplierID;
	var $SuppName;
	var $TransType;
	var $TransTypeName;
	var $TransNo;
	var $TransDate;
	var $TransExRate; /*Exchange rate of the transaction being allocated */
	var $TransAmt; /*Total amount of the transaction in FX */
	var $PrevDiffOnExch; /*The difference on exchange before this allocation */
	var $CurrDecimalPlaces; /*The number of decimal places to display for the currency being allocated */

	function __construct(){
		$this->Allocs = array();
	}

	function add_to_AllocsAllocn ($ID, $TransType, $TypeNo, $TransDate, $SuppRef, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID){

		if ($TransAmount >0){
			$this->Allocs[$ID] = new Allocn($ID, $TransType, $TypeNo, $TransDate, $SuppRef, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID);
			Return 1;
		} else {
			Return 0;
		}
	}

	function remove_alloc_item($ID){
		unset($this->Allocs[$ID]);
	}

}

class Allocn {

	var $ID;
	var $TransType;
	var $TypeNo;
	var $TransDate;
	var $SuppRef;
	var $AllocAmt;
	var $TransAmount;
	var $ExRate;
	var $DiffOnExch; /*Difference on exchange calculated on this allocation */
	var $PrevDiffOnExch; /*Difference on exchange before this allocation */
	var $PrevAlloc; /*Total of allocations vs this trans from other payments/credits*/
	var $OrigAlloc; /*Allocation vs this trans from the same payment/credit before modifications */
	var $PrevAllocRecordID; /*The SuppAllocn trans type for the previously allocated amount
				   this must be deleted if a new modified record is inserted
				   THERE CAN BE ONLY ONE ... allocation record for each
				   payment/inovice combination  */

	function __construct($ID, $TransType, $TypeNo, $TransDate, $SuppRef, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID){

		$this->ID = $ID;
		$this->TransType = $TransType;
		$this->TypeNo = $TypeNo;
		$this->TransDate = $TransDate;
		$this->SuppRef = $SuppRef;
		$this->AllocAmt = $AllocAmt;
		$this->OrigAlloc = $AllocAmt;
		$this->TransAmount = $TransAmount;
		$this->ExRate = $ExRate;
		$this->DiffOnExch=$DiffOnExch;
		$this->PrevDiffOnExch = $PrevDiffOnExch;
		$this->PrevAlloc = $PrevAlloc;
		$this->PrevAllocRecordID= $PrevAllocRecordID;
	}
}
