<?php
/* $Revision: 1.3 $ */
/* definition of the Debtor Receipt/Credit note allocation class */

Class Allocation {

	var $Allocs; /*array of transactions allocated to */
	var $AllocTrans; /*The ID of the transaction being allocated */
	var $DebtorNo;
	var $CustomerName;
	var $TransType;
	var $TransTypeName;
	var $TransNo;
	var $TransDate;
	var $TransExRate; /*Exchange rate of the transaction being allocated */
	var $TransAmt; /*Total amount of the transaction in FX */
	var $PrevDiffOnExch; /*The difference on exchange before this allocation */


	function Allocation(){
	/*Constructor function initialises a new debtor allocation*/
		$this->Allocs = array();
	}

	function add_to_AllocsAllocn ($ID, $TransType, $TypeNo, $TransDate, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID){
		// if ($AllocAmt <= ($TransAmount - $PrevAlloc)){

			$this->Allocs[$ID] = new Allocn($ID, $TransType, $TypeNo, $TransDate, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID);
			Return 1;
		
	}

	function remove_alloc_item($AllocnID){

		unset($this->Allocs[$AllocnID]);

	}

} /* end of class defintion */

Class Allocn {

	Var $ID;  /* DebtorTrans ID of the transaction alloc to */
	Var $TransType;
	Var $TypeNo;
	Var $TransDate;
	Var $AllocAmt;
	Var $TransAmount;
	Var $ExRate;
	Var $DiffOnExch; /*Difference on exchange calculated on this allocation */
	Var $PrevDiffOnExch; /*Difference on exchange before this allocation */
	Var $PrevAlloc; /*Total of allocations vs this trans from other receipts/credits*/
	Var $OrigAlloc; /*Allocation vs this trans from the same receipt/credit before modifications */
	Var $PrevAllocRecordID; /*The CustAllocn record ID for the previously allocated amount
				   this must be deleted if a new modified record is inserted
				   THERE CAN BE ONLY ONE ... allocation record for each
				   receipt/inovice combination  */

	function Allocn ($ID, $TransType, $TypeNo, $TransDate, $AllocAmt, $TransAmount, $ExRate, $DiffOnExch, $PrevDiffOnExch, $PrevAlloc, $PrevAllocRecordID){

/* Constructor function to add a new Allocn object with passed params */
		$this->ID =$ID;
		$this->TransType = $TransType;
		$this->TypeNo = $TypeNo;
		$this->TransDate = $TransDate;
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
