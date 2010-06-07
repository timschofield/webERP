<?php
/* $Id:  $*/
/* definition of the Contract class */

Class Contract {

	var contractno; /*auto generated contract no - but there for existing contracts */
    var contractref; /*the contract short description used for stockid when contract submitted for quotation */
    var contractdescription; /*the description of the contract */
    var debtorno; /*the customer that the contract is for */
    var branchcode;
    var status; /* 0 = initiated - 1=quoted - 2=completed */
    var categoryid;   /* the category where the contract will be when converted to an item  for quotation*/
    var orderno; /* the order number created when the contract is quoted */
    var margin; /*the margin used in quoting for the contract */
    var wo; /*the wo created when the quotation is converted to an order */
    var requireddate;
    var quantityreqd;
	var units; /*the unit of measure of the contract item*/
	var drawing; /*a link to the contract drawing*/
    var rate; /*the rate of exchange between customer currency and company functional currency used when quoting */
    var BOMComponentCounter;
    var RequirementsCounter;

	var $ContractBOM; /*array of stockid components  required for the contract */
	var $ContractReqts; /*array of other items required for the contract */
	
	function Contract(){
	/*Constructor function initialises a new Payment batch */
		$this->ContractBOM = array();
		$this->ContractReqts = array();
		$this->BOMComponentCounter=0;
		$this->RequirementsCounter=0;
	}

	function Add_To_ContractBOM($StockID, $WorkCentre, $Quantity, $ItemCost, $UOM){
		if (isset($StockID) AND $Quantity!=0){
			$this->ContractBOM[$this->BOMComponentCounter] = new ContractComponent($StockID, $WorkCentre, $Quantity,$ItemCost, $UOM);
			$this->BOMComponentCounter++;
			Return 1;
		}
		Return 0;
	}

	function remove_ContractComponent($ContractComponent_ID){
		unset($this->ContractBOM[$ContractComponent_ID]);
	}

	
/*Requirments Methods */

function Add_To_ContractRequirements($Requirment, $Quantity, $CostPerUnit,$ContractReqID){){
		if (isset($Requirment) AND $Quantity!=0 AND $CostPerUnit!=0){
			$this->ContractReqts[$this->RequirementsCounter] = new ContractRequirement($Requirment, $Quantity, $CostPerUnit,$ContractReqID);
			$this->RequirementsCounter++;
			Return 1;
		}
		Return 0;
	}

	function remove_ContractRequirement($ContractRequirmentID){
		unset($this->ContractReqts[$ContractRequirmentID]);
	}

} /* end of class defintion */

Class ContractComponent {

	var $StockID;
	var $WorkCentre;
	var $Quantity;
	var $ItemCost;
	var $UOM;
	
	function ContractComponent ($StockID, $WorkCentre, $Quantity, $ItemCost, $UOM){

/* Constructor function to add a new Contract Component object with passed params */
		$this->StockID =$StockID;
		$this->WorkCentre = $WorkCentre;
		$this->Quantity = $Quantity;
		$this->ItemCost= $ItemCost;
		$this->UOM = $UOM;
	}
}

Class ContractRequirement {

	var $ContractReqID; /*Used to hold the database ID of the contractreqtID  - if an existing contract*/
	var $Requirement; /*The description of the requirement for the contract */
	var $Quantity;
	var $CostPerUnit;
	
	function ContractRequirement ($Requirment, $Quantity, $CostPerUnit,$ContractReqID=0){

/* Constructor function to add a new Contract Component object with passed params */
		$this->Requirement =$Requirement;
		$this->Quantity = $Quantity;
		$this->CostPerUnit = $CostPerUnit;
		$this->ItemCost= $ItemCost;
		$this->ContractReqID = $ContractReqID;
	}
}




?>