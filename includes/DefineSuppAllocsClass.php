<?php
/* $Revision: 1.3 $ */

/* definition of the Supplier Payment/Credit Note allocation class */

Class Allocation {

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

	function Allocation(){
	/*Constructor function initialises a new supplier allocation*/
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

} /* end of class defintion */

Class Allocn {
	Var $ID;
	Var $TransType;
	Var $TypeNo;
	Var $TransDate;
	Var $SuppRef;
	Var $AllocAmt;
	Var $TransAmount;
	Var $ExRate;
	Var $DiffOnExch; /*Difference on exchange calculated on this allocation */
	Var $PrevDiffOnExch; /*Difference on exchange before this allocation */
	Var $PrevAlloc; /*Total of allocations vs this trans from other payments/credits*/
	Var $OrigAlloc; /*Allocation vs this trans from the same payment/credit before modifications */
	Var $PrevAllocRecordID; /*The SuppAllocn trans type for the previously allocated amount
				   this must be deleted if a new modified record is inserted
				   THERE CAN BE ONLY ONE ... allocation record for each
				   payment/inovice combination  */

	function Allocn ($ID, $TransType, $TypeNo, $TransDate, $SuppRef, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID){

/* Constructor function to add a new Allocn object with passed params */
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

?>
