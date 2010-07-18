<?php
/* $Id:  $*/
/* definition of the Contract class */

Class Contract {

	var $ContractNo; /*auto generated contract no - but there for existing contracts */
    var $ContractRef; /*the contract short description used for stockid when contract submitted for quotation */
    var $ContractDescription; /*the description of the contract */
    var $DebtorNo; /*the customer that the contract is for */
    var $CustomerName;
    var $BranchCode;
    var $BranchName;
    var $Status; /* 0 = initiated - 1=quoted - 2=completed */
    var $CategoryID;   /* the category where the contract will be when converted to an item  for quotation*/
    var $OrderNo; /* the order number created when the contract is quoted */
    var $CustomerRef;
    var $Margin; /*the margin used in quoting for the contract */
    var $WO; /*the wo created when the quotation is converted to an order */
    var $RequiredDate;
    var $QuantityReqd;
	var $Units; /*the unit of measure of the contract item*/
	var $Drawing; /*a link to the contract drawing*/
    var $CurrCode; /*the currency of the customer to quote in */
    var $ExRate; /*the rate of exchange between customer currency and company functional currency used when quoting */
    var $BOMComponentCounter;
    var $RequirementsCounter;

	var $ContractBOM; /*array of stockid components  required for the contract */
	var $ContractReqts; /*array of other items required for the contract */
	
	function Contract(){
	/*Constructor function initialises a new Payment batch */
		$this->ContractBOM = array();
		$this->ContractReqts = array();
		$this->BOMComponentCounter=0;
		$this->RequirementsCounter=0;
		$this->QuantityReqd=1;
		$this->Status =0;
	}

	function Add_To_ContractBOM($StockID, $ItemDescription, $WorkCentre, $Quantity, $ItemCost, $UOM){
		if (isset($StockID) AND $Quantity!=0){
			$this->ContractBOM[$this->BOMComponentCounter] = new ContractComponent($this->BOMComponentCounter,
																					$StockID, 
																					$ItemDescription, 
																					$WorkCentre, 
																					$Quantity,
																					$ItemCost, 
																					$UOM);
			$this->BOMComponentCounter++;
			Return 1;
		}
		Return 0;
	}

	function remove_ContractComponent($ContractComponent_ID){
		unset($this->ContractBOM[$ContractComponent_ID]);
	}

	
/*Requirments Methods */

function Add_To_ContractRequirements($Requirment, $Quantity, $CostPerUnit,$ContractReqID){
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
	var $ComponentID;
	var $StockID;
	var $ItemDescription;
	var $WorkCentre;
	var $Quantity;
	var $ItemCost;
	var $UOM;
	
	function ContractComponent ($ComponentID, $StockID, $ItemDescription, $WorkCentre, $Quantity, $ItemCost, $UOM){

/* Constructor function to add a new Contract Component object with passed params */
		$this->ComponentID = $ComponentID;
		$this->StockID = $StockID;
		$this->ItemDescription = $ItemDescription;
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