<?php
/* $Revision: 1.4 $ */
/* definition of the Payment class */

Class Payment {

	var $GLItems; /*array of objects of Payment class - id is the pointer */
	var $Account; /*Bank account GL Code Paid from */
	var $BankAccountName; /*Bank account name */
	var $DatePaid; /*Date the batch of Payments was Paid */
	var $ExRate; /*Exchange rate applicable to all Payments in the batch seperate batch required for different currencies*/
	var $Currency; /*Currency being Paid - defaulted to company functional */
	var $SupplierID; /* supplier code */
	var $SuppName;
	var $Address1;
	var $Address2;
	var $Address3;
	var $Address4;
	var $Address5;
	var $Address6;
	var $Discount;
	var $Amount;
	var $Narrative;
	var $GLItemCounter; /*Counter for the number of GL accounts being posted to by the Payment */

	function Payment(){
	/*Constructor function initialises a new Payment batch */
		$this->GLItems = array();
		$this->GLItemCounter=0;
		$this->SupplierID ="";
		$this->SuppName ="";
		$this->Address1 ="";
		$this->Address2 ="";
		$this->Address3 ="";
		$this->Address4 ="";
		$this->Address5 ="";
		$this->Address6 ="";

	}

	function Add_To_GLAnalysis($Amount, $Narrative, $GLCode, $GLActName){
		if (isset($GLCode) AND $Amount!=0){
			$this->GLItems[$this->GLItemCounter] = new PaymentGLAnalysis($Amount, $Narrative, $this->GLItemCounter, $GLCode, $GLActName);
			$this->GLItemCounter++;
			Return 1;
		}
		Return 0;
	}

	function remove_GLItem($GL_ID){
		unset($this->GLItems[$GL_ID]);
	}

} /* end of class defintion */

Class PaymentGLAnalysis {

	Var $Amount;	/* in currency of the payment*/
	Var $Narrative;
	Var $GLCode;
	var $GLActName;
	Var $ID;

	function PaymentGLAnalysis ($Amt, $Narr, $id, $GLCode, $GLActName){

/* Constructor function to add a new PaymentGLAnalysis object with passed params */
		$this->Amount =$Amt;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->ID = $id;
	}
}

?>