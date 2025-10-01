<?php

/* definition of the ReceiptBatch class */

class Receipt_Batch {

	var $Items; /*array of objects of Receipt class - id is the pointer */
	var $BatchNo; /*Batch Number*/
	var $Account; /*Bank account GL Code banked into */
	var $AccountCurrency; /*Bank Account Currency */
	var $BankAccountName; /*Bank account name */
	var $DateBanked; /*Date the batch of receipts was banked */
	var $ExRate; /*Exchange rate conversion between currency received and bank account currency */
	var $FunctionalExRate; /* Exchange Rate between Bank Account Currency and Functional(business reporting) currency */
	var $Currency; /*Currency being banked - defaulted to company functional */
	var $CurrDecimalPlaces;
	var $BankTransRef;
	var $Narrative;
	var $ReceiptType;  /*Type of receipt ie credit card/cash/cheque etc - array of types defined in config.php*/
	var $Total;	  /*Total of the batch of receipts in the currency of the company*/
	var $ItemCounter; /*Counter for the number of customer receipts in the batch */

	function __construct(){
	/*Constructor function initialises a new receipt batch */
		$this->Items = array();
		$this->ItemCounter=0;
		$this->Total=0;
	}

	function Receipt_Batch() {
		self::__construct();
	}

	function add_to_batch($Amount, $Customer, $Discount, $Narrative, $GLCode, $PayeeBankDetail, $CustomerName, $Tag){
		if ((isset($Customer) OR isset($GLCode)) AND ($Amount + $Discount) !=0){
			$this->Items[$this->ItemCounter] = new Receipt($Amount, $Customer, $Discount, $Narrative, $this->ItemCounter, $GLCode, $PayeeBankDetail, $CustomerName, $Tag);
			$this->ItemCounter++;
			$this->Total = $this->Total + ($Amount + $Discount) / $this->ExRate;
			Return 1;
		}
		Return 0;
	}

	function remove_receipt_item($RcptID){

		$this->Total = $this->Total - ($this->Items[$RcptID]->Amount + $this->Items[$RcptID]->Discount) / $this->ExRate;
		unset($this->Items[$RcptID]);

	}

} /* end of class defintion */

class Receipt {
	var $Amount;	/*in currency of the customer*/
	var $Customer; /*customer code */
	var $CustomerName;
	var $Discount;
	var $Narrative;
	var $GLCode;
	var $PayeeBankDetail;
	var $ID;
	var $Tag;
	var $TagName;

	function __construct($Amt, $Cust, $Disc, $Narr, $id, $GLCode, $PayeeBankDetail, $CustomerName, $Tag){
/* Constructor function to add a new Receipt object with passed params */
		$this->Amount =$Amt;
		$this->Customer = $Cust;
		$this->CustomerName = $CustomerName;
		$this->Discount = $Disc;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->PayeeBankDetail=$PayeeBankDetail;
		$this->ID = $id;
		$this->Tag = $Tag;
		$Result = DB_query("SELECT tagdescription FROM tags WHERE tagref='" . $Tag . "'");
		if (DB_num_rows($Result)==1){
			$TagRow = DB_fetch_array($Result);
			$this->TagName = $TagRow['tagdescription'];
		}
	}
	function Receipt($Amt, $Cust, $Disc, $Narr, $id, $GLCode, $PayeeBankDetail, $CustomerName, $Tag){
		self::__construct($Amt, $Cust, $Disc, $Narr, $id, $GLCode, $PayeeBankDetail, $CustomerName, $Tag);
	}
}
