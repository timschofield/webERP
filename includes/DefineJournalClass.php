<?php
/* $Revision: 1.3 $ */
/* definition of the Journal class */

Class Journal {

	var $GLEntries; /*array of objects of JournalGLAnalysis class - id is the pointer */
	var $JnlDate; /*Date the journal to be processed */
	var $JournalType; /*Normal or reversing journal */
	var $GLItemCounter; /*Counter for the number of GL entires being posted to by the journal */
	var $GLItemID;
	var $JournalTotal; /*Running total for the journal */
	var $BankAccounts; /*Array of bank account GLCodes that must be posted to by a bank payment or receipt 
				to ensure integrity for matching off vs bank stmts */

	function Journal(){
	/*Constructor function initialises a new journal */
		$this->GLEntries = array();
		$this->GLItemCounter=0;
		$this->JournalTotal=0;
		$this->GLItemID=0;
		$this->BankAccounts = array();
	}

	function Add_To_GLAnalysis($Amount, $Narrative, $GLCode, $GLActName){
		if (isset($GLCode) AND $Amount!=0){
			$this->GLEntries[$this->GLItemID] = new JournalGLAnalysis($Amount, $Narrative, $this->GLItemID, $GLCode, $GLActName);
			$this->GLItemCounter++;
			$this->GLItemID++;
			$this->JournalTotal += $Amount;
			Return 1;
		}
		Return 0;
	}

	function remove_GLEntry($GL_ID){
		$this->JournalTotal -= $this->GLEntries[$GL_ID]->Amount;
		unset($this->GLEntries[$GL_ID]);
		$this->GLItemCounter--;
	}

} /* end of class defintion */

Class JournalGLAnalysis {

	Var $Amount;
	Var $Narrative;
	Var $GLCode;
	var $GLActName;
	Var $ID;

	function JournalGLAnalysis ($Amt, $Narr, $id, $GLCode, $GLActName){

/* Constructor function to add a new JournalGLAnalysis object with passed params */
		$this->Amount =$Amt;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->ID = $id;
	}
}

?>
