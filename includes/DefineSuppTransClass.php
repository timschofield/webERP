<?php
/* $Id: DefineSuppTransClass.php 6438 2013-11-26 09:01:14Z daintree $*/
/* Definition of the Supplier Transactions class to hold all the information for an accounts payable invoice or credit note
*/

Class SuppTrans {

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
	var $ShiptsCounter=0;
	var $ContractsCounter=0;
	var $AssetCounter=0;
	var $TaxGroup;
	var $LocalTaxProvince;
	var $TaxGroupDescription;
	var $Taxes;
	var $Hold;

	function SuppTrans(){
	/*Constructor function initialises a new Supplier Transaction object */
		$this->GRNs = array();
		$this->GLCodes = array();
		$this->Shipts = array();
		$this->Contracts = array();
		$this->Assets = array();
		$this->Taxes = array();
	}

	function GetTaxes () {

		global $db;

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

		$ErrMsg = _('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg);

		while ($myrow = DB_fetch_array($GetTaxRatesResult)){

			$this->Taxes[$myrow['calculationorder']] = new Tax($myrow['calculationorder'],
																$myrow['taxauthid'],
																$myrow['description'],
																$myrow['taxrate'],
																$myrow['taxontax'],
																$myrow['purchtaxglaccount']);
		}
	} //end method GetTaxes()


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
								$StdCostUnit=0,
								$ShiptRef,
								$JobRef,
								$GLCode,
								$PONo,
								$AssetID=0,
								$Hold=0,
								$DecimalPlaces=2,
								$GRNBatchNo){

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
											$GRNBatchNo);
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
									$Hold){

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
										$Hold );
			Return 1;
		}
		Return 0;
	}

	function Copy_GRN_To_Trans($GRNSrc){
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
													$GRNSrc->GRNBatchNo);
			Return 1;
		}
		Return 0;
	}

	function Add_GLCodes_To_Trans($GLCode,
									$GLActName,
									$Amount,
									$Narrative,
									$Tag){

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

	function Add_Shipt_To_Trans($ShiptRef, $Amount){
		if ($Amount!=0){
			$this->Shipts[$this->ShiptCounter] = new Shipment($this->ShiptCounter,
																$ShiptRef,
																$Amount);
			$this->ShiptCounter++;
			Return 1;
		}
		Return 0;
	}

	function Add_Asset_To_Trans($AssetID, $Amount){
		if ($Amount!=0){
			$this->Assets[$this->AssetCounter] = new Asset($this->AssetCounter,
															$AssetID,
															$Amount);
			$this->AssetCounter++;
			Return 1;
		}
		Return 0;
	}

	function Add_Contract_To_Trans($ContractRef, $Amount,$Narrative, $AnticipatedCost){
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
	function Remove_Asset_From_Trans($AssetCounter){
	     unset($this->Assets[$AssetCounter]);
	}
	function Remove_GRN_From_Trans($GRNNo){
	     unset($this->GRNs[$GRNNo]);
	}

	function Remove_GLCodes_From_Trans($GLCodeCounter){
	     unset($this->GLCodes[$GLCodeCounter]);
	}

	function Remove_Shipt_From_Trans($ShiptCounter){
	     unset($this->Shipts[$ShiptCounter]);
	}

	function Remove_Contract_From_Trans($ContractID){
	     unset($this->Contracts[$ContractID]);
	}

	function Total_GRN_Value(){
		$TotalGRNs =0;
		foreach ($this->GRNs as $GRN) {
			$TotalGRNs += ($GRN->This_QuantityInv*$GRN->ChgPrice);
		}
		return $TotalGRNs;
	}
	function Total_Shipts_Value(){
		$TotalShiptValue =0;
		foreach ($this->Shipts as $Shipt) {
			$TotalShiptValue += $Shipt->Amount;
		}
		return $TotalShiptValue;
	}
	function Total_GL_Value(){
		$TotalGLValue =0;
		foreach ($this->GLCodes as $GL) {
			$TotalGLValue += $GL->Amount;
		}
		return $TotalGLValue;
	}
	function Total_Assets_Value(){
		$TotalAssetValue =0;
		foreach ($this->Assets as $Asset) {
			$TotalAssetValue += $Asset->Amount;
		}
		return $TotalAssetValue;
	}
	function Total_Contracts_Value(){
		$TotalContractsValue =0;
		foreach ($this->Contracts as $Contract) {
			$TotalContractsValue += $Contract->Amount;
		}
		return $TotalContractsValue;
	}
} /* end of class defintion */

Class GRNs {

/* Contains relavent information from the PurchOrderDetails as well to provide in cached form,
all the info to do the necessary entries without looking up ie additional queries of the database again */

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

	function GRNs ($GRNNo,
					$PODetailItem,
					$ItemCode,
					$ItemDescription,
					$QtyRecd,
					$Prev_QuantityInv,
					$This_QuantityInv,
					$OrderPrice,
					$ChgPrice,
					$Complete,
					$StdCostUnit=0,
					$ShiptRef,
					$JobRef,
					$GLCode,
					$PONo,
					$AssetID,
					$Hold=0,
					$DecimalPlaces=2,
					$GRNBatchNo){



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
					$Hold){

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
	}
}

Class GLCodes {

	Var $Counter;
	Var $GLCode;
	Var $GLActName;
	Var $Amount;
	Var $Narrative;
	Var $Tag;
	Var $TagName;

	function GLCodes ($Counter, $GLCode, $GLActName, $Amount, $Narrative, $Tag=0, $TagName=''){

		global $db;
	/* Constructor function to add a new GLCodes object with passed params */
		$this->Counter = $Counter;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->Amount = $Amount;
		$this->Narrative = $Narrative;
		$this->Tag = $Tag;

		$TagResult=DB_query("SELECT tagdescription from tags where tagref='" . $Tag . "'", $db);
		$TagMyrow=DB_fetch_array($TagResult);
		if ($Tag==0) {
			$this->TagName=_('None');
		} else {
			$this->TagName=$TagMyrow['tagdescription'];
		}
	}
}

Class Shipment {

	Var $Counter;
	Var $ShiptRef;
	Var $Amount;

	function Shipment ($Counter, $ShiptRef, $Amount){
		$this->Counter = $Counter;
		$this->ShiptRef = $ShiptRef;
		$this->Amount = $Amount;
	}
}

Class Asset {

	Var $Counter;
	Var $AssetID;
	Var $Description;
	Var $CostAct;
	Var $Amount;

	function Asset ($Counter, $AssetID, $Amount){
		global $db;
		$this->Counter = $Counter;
		$this->AssetID = $AssetID;
		$this->Amount = $Amount;

		$result = DB_query("SELECT fixedassets.description,
									fixedassetcategories.costact
							FROM fixedassets INNER JOIN fixedassetcategories
							ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
							WHERE assetid='" . $AssetID . "'",$db);
		$AssetRow = DB_fetch_array($result);
		$this->Description = $AssetRow['description'];
		$this->CostAct = $AssetRow['costact'];
	}
}

Class Contract {

	Var $Counter;
	Var $ContractRef;
	Var $Amount;
	Var $Narrative;
	Var $AniticipatedCost;

	function Contract ($Counter, $ContractRef, $Amount,$Narrative,$AnticipatedCost){
		$this->Counter = $Counter;
		$this->ContractRef = $ContractRef;
		$this->Amount = $Amount;
		$this->Narrative = $Narrative;
		$this->AnticipatedCost = $AnticipatedCost;
	}
}


Class Tax {
	Var $TaxCalculationOrder;  /*the index for the array */
	Var $TaxAuthID;
	Var $TaxAuthDescription;
	Var $TaxRate;
	Var $TaxOnTax;
	Var $TaxGLCode;
	Var $TaxOvAmount;

	function Tax ($TaxCalculationOrder,
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

?>
