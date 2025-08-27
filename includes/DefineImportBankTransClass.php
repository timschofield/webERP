<?php

class BankStatement {
	var $ReportCreated;
	var $AccountNumber;
	var $AccountName;
	var $StatementNumber;
	var $AccountOwner;
	var $CurrCode;
	var $OpeningDate;
	var $OpeningBalance;
	var $ClosingDate;
	var $ClosingBalance;
	var $BankGLAccount;
	var $BankAccountName;
	var $CurrDecimalPlaces;
	var $ExchangeRate;
	var $Trans;

	function __construct() {
		$this->ReportCreated = '';
		$this->AccountNumber = '';
		$this->AccountName = '';
		$this->StatementNumber = '';
		$this->AccountOwner = '';
		$this->CurrCode = '';
		$this->ClosingBalance = 0;
		$this->OpeningBalance = 0;
		$this->BankGLAccount = '';
		$this->BankAccountName = '';
		$this->CurrDecimalPlaces = 2;
		$this->ExchangeRate = 1;
	}
	function BankStatement() {
		self::__construct();
	}
}

class BankTrans {
	var $ValueDate;
	var $Amount;
	var $Code;
	var $Description;
	var $BankTransID;
	var $GLEntries;
	var $DebtorNo;
	var $SupplierID;
	var $GLItemID;
	var $GLTotal;

	function __construct($ValueDate, $Amount) {
		$this->ValueDate = $ValueDate;
		$this->Amount = $Amount;
		$this->GLEntries = array();
		$this->DebtorNo = '';
		$this->SupplierID = '';
		$this->GLItemID = 0;
		$this->GLTotal = 0;
		$this->BankTransID = 0;
	}
	function BankTrans($ValueDate, $Amount) {
		self::__construct($ValueDate, $Amount);
	}

	function Add_To_GLAnalysis($Amount, $Narrative, $GLCode, $GLAccountName, $Tag){
		if (isset($GLCode) AND $Amount!=0){
			$this->GLEntries[$this->GLItemID] = new GLAnalysis($Amount, $Narrative, $this->GLItemID, $GLCode, $GLAccountName, $Tag);
			$this->GLItemID++;
			$this->GLTotal += $Amount;

			Return 1;
		}
		Return 0;
	}

	function Remove_GLEntry($GL_ID){
		$this->GLTotal -= $this->GLEntries[$GL_ID]->Amount;
		unset($this->GLEntries[$GL_ID]);
		$this->GLItemCounter--;
	}
}


class GLAnalysis {

	var $Amount;
	var $Narrative;
	var $GLCode;
	var $GLAccountName;
	var $ID;
	var $Tag;

	function __construct($Amount, $Narrative, $ID, $GLCode, $GLAccountName, $Tag) {
		$this->Amount =$Amount;
		$this->Narrative = $Narrative;
		$this->GLCode = $GLCode;
		$this->GLAccountName = $GLAccountName;
		$this->ID = $ID;
		$this->Tag = $Tag;
	}
}
