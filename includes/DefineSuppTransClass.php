<?php

/**
 * Class to hold all the information for an accounts payable invoice or credit note
 */
class SuppTrans {

	var $GRNs; /*array of objects of class GRNs using the GRN No as the pointer */
	var $GLCodes; /*array of objects of class GLCode using a counter as the pointer */
	var $Shipts;  /*array of objects of class Shipment using a counter as the pointer */
	var $Contracts; /*array of objects of class Contract using a counter as the pointer */
	var $Assets; /*array of objects of class Asset using a counter as the pointer */
	var $SupplierID;
	var $SupplierName;
	var $CurrCode;
	var $TermsDescription;
	var $Terms;
	var $GLLink_Creditors;
	var $GRNAct;
	var $CreditorsAct;
	var $InvoiceOrCredit;
	var $ExRate;
	var $CurrDecimalPlaces;
	var $Comments;
	var $TranDate;
	var $DueDate;
	var $SuppReference;
	var $OvAmount;
	var $OvGST;
	var $GLCodesCounter=0;
	var $ShiptCounter=0;
	var $ContractsCounter=0;
	var $AssetCounter=0;
	var $TaxGroup;
	var $LocalTaxProvince;
	var $TaxGroupDescription;
	var $Taxes;
	var $Hold;
	var $SupplierRef='';

	function __construct() {
		$this->GRNs = array();
		$this->GLCodes = array();
		$this->Shipts = array();
		$this->Contracts = array();
		$this->Assets = array();
		$this->Taxes = array();
	}

	function GetTaxes() {
		/*Gets the Taxes and rates applicable to the tax group of the supplier
		and SESSION['DefaultTaxCategory'] and the taxprovince of the location that the user is setup to use*/

		$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.purchtaxglaccount,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid=" . $this->TaxGroup . "
			AND taxauthrates.dispatchtaxprovince=" . $this->LocalTaxProvince . "
			AND taxauthrates.taxcatid = " . $_SESSION['DefaultTaxCategory'] . "
			ORDER BY taxgrouptaxes.calculationorder";

		$ErrMsg = __('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($SQL, $ErrMsg);

		while ($MyRow = DB_fetch_array($GetTaxRatesResult)){

			$this->Taxes[$MyRow['calculationorder']] = new Tax($MyRow['calculationorder'],
																$MyRow['taxauthid'],
																$MyRow['description'],
																$MyRow['taxrate'],
																$MyRow['taxontax'],
																$MyRow['purchtaxglaccount']);
		}
	}

	function Add_GRN_To_Trans($GRNNo,
								$PODetailItem,
								$ItemCode,
								$ItemDescription,
								$QtyRecd,
								$Prev_QuantityInv,
								$This_QuantityInv,
								$OrderPrice,
								$ChgPrice,
								$Complete,
								$StdCostUnit,
								$ShiptRef,
								$JobRef,
								$GLCode,
								$PONo,
								$AssetID,
								$Hold,
								$DecimalPlaces,
								$GRNBatchNo,
								$SupplierRef) {

		if ($This_QuantityInv!=0 AND isset($This_QuantityInv)){
			$this->GRNs[$GRNNo] = new GRNs($GRNNo,
											$PODetailItem,
											$ItemCode,
											$ItemDescription,
											$QtyRecd,
											$Prev_QuantityInv,
											$This_QuantityInv,
											$OrderPrice,
											$ChgPrice,
											$Complete,
											$StdCostUnit,
											$ShiptRef,
											$JobRef,
											$GLCode,
											$PONo,
											$AssetID,
											$Hold,
											$DecimalPlaces,
											$GRNBatchNo,
											$SupplierRef);
			Return 1;
		}
		Return 0;
	}

	function Modify_GRN_To_Trans($GRNNo,
									$PODetailItem,
									$ItemCode,
									$ItemDescription,
									$QtyRecd,
									$Prev_QuantityInv,
									$This_QuantityInv,
									$OrderPrice,
									$ChgPrice,
									$Complete,
									$StdCostUnit,
									$ShiptRef,
									$JobRef,
									$GLCode,
									$Hold,
									$SupplierRef){

		if ($This_QuantityInv!=0 AND isset($This_QuantityInv)){
			$this->GRNs[$GRNNo]->Modify($PODetailItem,
										$ItemCode,
										$ItemDescription,
										$QtyRecd,
										$Prev_QuantityInv,
										$This_QuantityInv,
										$OrderPrice,
										$ChgPrice,
										$Complete,
										$StdCostUnit,
										$ShiptRef,
										$JobRef,
										$GLCode,
										$Hold,
								       		$SupplierRef);
			Return 1;
		}
		Return 0;
	}

	function Copy_GRN_To_Trans($GRNSrc) {
		if ($GRNSrc->This_QuantityInv!=0 && isset($GRNSrc->This_QuantityInv)){

			$this->GRNs[$GRNSrc->GRNNo] = new GRNs($GRNSrc->GRNNo,
													$GRNSrc->PODetailItem,
													$GRNSrc->ItemCode,
													$GRNSrc->ItemDescription,
													$GRNSrc->QtyRecd,
													$GRNSrc->Prev_QuantityInv,
													$GRNSrc->This_QuantityInv,
													$GRNSrc->OrderPrice,
													$GRNSrc->ChgPrice,
													$GRNSrc->Complete,
													$GRNSrc->StdCostUnit,
													$GRNSrc->ShiptRef,
													$GRNSrc->JobRef,
													$GRNSrc->GLCode,
													$GRNSrc->PONo,
													$GRNSrc->AssetID,
													$GRNSrc->Hold,
													$GRNSrc->DecimalPlaces,
													$GRNSrc->GRNBatchNo,
													$GRNSrc->SupplierRef);
			Return 1;
		}
		Return 0;
	}

	function Add_GLCodes_To_Trans($GLCode,
									$GLActName,
									$Amount,
									$Narrative,
									$Tag) {

		if ($Amount!=0 AND isset($Amount)){
			$this->GLCodes[$this->GLCodesCounter] = new GLCodes($this->GLCodesCounter,
																$GLCode,
																$GLActName,
																$Amount,
																$Narrative,
																$Tag);
			$this->GLCodesCounter++;
			Return 1;
		}
		Return 0;
	}

	function Add_Shipt_To_Trans($ShiptRef, $Amount) {
		if ($Amount!=0){
			$this->Shipts[$this->ShiptCounter] = new Shipment($this->ShiptCounter,
																$ShiptRef,
																$Amount);
			$this->ShiptCounter++;
			Return 1;
		}
		Return 0;
	}

	function Add_Asset_To_Trans($AssetID, $Amount) {
		if ($Amount!=0){
			$this->Assets[$this->AssetCounter] = new Asset($this->AssetCounter,
															$AssetID,
															$Amount);
			$this->AssetCounter++;
			Return 1;
		}
		Return 0;
	}

	function Add_Contract_To_Trans($ContractRef, $Amount,$Narrative, $AnticipatedCost) {
		if ($Amount!=0){
			$this->Contracts[$this->ContractsCounter] = new Contract($this->ContractsCounter,
																	$ContractRef,
																	$Amount,
																	$Narrative,
																	$AnticipatedCost);
			$this->ContractsCounter++;
			Return 1;
		}
		Return 0;
	}
	function Remove_Asset_From_Trans($AssetCounter) {
	     unset($this->Assets[$AssetCounter]);
	}

	function Remove_GRN_From_Trans($GRNNo) {
	     unset($this->GRNs[$GRNNo]);
	}

	function Remove_GLCodes_From_Trans($GLCodeCounter) {
	     unset($this->GLCodes[$GLCodeCounter]);
	}

	function Remove_Shipt_From_Trans($ShiptCounter) {
	     unset($this->Shipts[$ShiptCounter]);
	}

	function Remove_Contract_From_Trans($ContractID) {
	     unset($this->Contracts[$ContractID]);
	}

	function Total_GRN_Value() {
		$TotalGRNs =0;
		foreach ($this->GRNs as $GRN) {
			$TotalGRNs += ($GRN->This_QuantityInv*$GRN->ChgPrice);
		}
		return $TotalGRNs;
	}

	function Total_Shipts_Value() {
		$TotalShiptValue =0;
		foreach ($this->Shipts as $Shipt) {
			$TotalShiptValue += $Shipt->Amount;
		}
		return $TotalShiptValue;
	}

	function Total_GL_Value() {
		$TotalGLValue =0;
		foreach ($this->GLCodes as $GL) {
			$TotalGLValue += $GL->Amount;
		}
		return $TotalGLValue;
	}

	function Total_Assets_Value() {
		$TotalAssetValue =0;
		foreach ($this->Assets as $Asset) {
			$TotalAssetValue += $Asset->Amount;
		}
		return $TotalAssetValue;
	}

	function Total_Contracts_Value() {
		$TotalContractsValue =0;
		foreach ($this->Contracts as $Contract) {
			$TotalContractsValue += $Contract->Amount;
		}
		return $TotalContractsValue;
	}
}

/**
 * Contains relevant information from the PurchOrderDetails as well to provide in cached form,
 * all the info to do the necessary entries without looking up ie additional queries of the database again
 */
class GRNs {

	var $GRNNo;
	var $PODetailItem;
	var $ItemCode;
	var $ItemDescription;
	var $QtyRecd;
	var $Prev_QuantityInv;
	var $This_QuantityInv;
	var $OrderPrice;
	var $ChgPrice;
	var $Complete;
	var $StdCostUnit;
	var $ShiptRef;
	var $JobRef;
	var $GLCode;
	var $PONo;
	var $Hold;
	var $AssetID;
	var $DecimalPlaces;
	var $GRNBatchNo;
	var $SupplierRef;

	function __construct($GRNNo,
					$PODetailItem,
					$ItemCode,
					$ItemDescription,
					$QtyRecd,
					$Prev_QuantityInv,
					$This_QuantityInv,
					$OrderPrice,
					$ChgPrice,
					$Complete,
					$StdCostUnit,
					$ShiptRef,
					$JobRef,
					$GLCode,
					$PONo,
					$AssetID,
					$Hold,
					$DecimalPlaces,
					$GRNBatchNo,
					$SupplierRef=''){

	/* Constructor function to add a new GRNs object with passed params */
		$this->GRNNo = $GRNNo;
		$this->PODetailItem = $PODetailItem;
		$this->ItemCode = $ItemCode;
		$this->ItemDescription = $ItemDescription;
		$this->QtyRecd = $QtyRecd;
		$this->Prev_QuantityInv = $Prev_QuantityInv;
		$this->This_QuantityInv = $This_QuantityInv;
		$this->OrderPrice =$OrderPrice;
		$this->ChgPrice = $ChgPrice;
		$this->Complete = $Complete;
		$this->StdCostUnit = $StdCostUnit;
		$this->ShiptRef = $ShiptRef;
		$this->JobRef = $JobRef;
		$this->GLCode = $GLCode;
		$this->PONo = $PONo;
		$this->AssetID = $AssetID;
		$this->Hold = $Hold;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->GRNBatchNo = $GRNBatchNo;
		$this->SupplierRef = $SupplierRef;
	}

	function Modify ($PODetailItem,
					$ItemCode,
					$ItemDescription,
					$QtyRecd,
					$Prev_QuantityInv,
					$This_QuantityInv,
					$OrderPrice,
					$ChgPrice,
					$Complete,
					$StdCostUnit,
					$ShiptRef,
					$JobRef,
					$GLCode,
					$Hold,
					$SupplierRef){

	/* Modify function to edit a GRNs object with passed params */
		$this->PODetailItem = $PODetailItem;
		$this->ItemCode = $ItemCode;
		$this->ItemDescription = $ItemDescription;
		$this->QtyRecd = $QtyRecd;
		$this->Prev_QuantityInv = $Prev_QuantityInv;
		$this->This_QuantityInv = $This_QuantityInv;
		$this->OrderPrice =$OrderPrice;
		$this->ChgPrice = $ChgPrice;
		$this->Complete = $Complete;
		$this->StdCostUnit = $StdCostUnit;
		$this->ShiptRef = $ShiptRef;
		$this->JobRef = $JobRef;
		$this->Hold = $Hold;
		$this->GLCode = $GLCode;
		$this->SupplierRef = $SupplierRef;
	}
}

class GLCodes {

	var $Counter;
	var $GLCode;
	var $GLActName;
	var $Amount;
	var $Narrative;
	var $Tag;
	var $TagName;

	function __construct($Counter, $GLCode, $GLActName, $Amount, $Narrative, $Tag=0, $TagName=''){
	/* Constructor function to add a new GLCodes object with passed params */
		$this->Counter = $Counter;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->Amount = $Amount;
		$this->Narrative = $Narrative;
		$this->Tag = $Tag;

	}
}

class Shipment {

	var $Counter;
	var $ShiptRef;
	var $Amount;

	function __construct($Counter, $ShiptRef, $Amount){
		$this->Counter = $Counter;
		$this->ShiptRef = $ShiptRef;
		$this->Amount = $Amount;
	}
}

class Asset {

	var $Counter;
	var $AssetID;
	var $Description;
	var $CostAct;
	var $Amount;

	function __construct($Counter, $AssetID, $Amount){
		$this->Counter = $Counter;
		$this->AssetID = $AssetID;
		$this->Amount = $Amount;

		$Result = DB_query("SELECT fixedassets.description,
									fixedassetcategories.costact
							FROM fixedassets INNER JOIN fixedassetcategories
							ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
							WHERE assetid='" . $AssetID . "'");
		$AssetRow = DB_fetch_array($Result);
		$this->Description = $AssetRow['description'];
		$this->CostAct = $AssetRow['costact'];
	}
}

class Contract {

	var $Counter;
	var $ContractRef;
	var $Amount;
	var $Narrative;
	var $AniticipatedCost;

	function __construct($Counter, $ContractRef, $Amount,$Narrative,$AnticipatedCost){
		$this->Counter = $Counter;
		$this->ContractRef = $ContractRef;
		$this->Amount = $Amount;
		$this->Narrative = $Narrative;
		$this->AnticipatedCost = $AnticipatedCost;
	}
}


class Tax {

	var $TaxCalculationOrder;  /*the index for the array */
	var $TaxAuthID;
	var $TaxAuthDescription;
	var $TaxRate;
	var $TaxOnTax;
	var $TaxGLCode;
	var $TaxOvAmount;


	function __construct($TaxCalculationOrder,
					$TaxAuthID,
					$TaxAuthDescription,
					$TaxRate,
					$TaxOnTax,
					$TaxGLCode){

		$this->TaxCalculationOrder = $TaxCalculationOrder;
		$this->TaxAuthID = $TaxAuthID;
		$this->TaxAuthDescription = $TaxAuthDescription;
		$this->TaxRate =  $TaxRate;
		$this->TaxOnTax = $TaxOnTax;
		$this->TaxGLCode = $TaxGLCode;
	}
}
