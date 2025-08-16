<?php
/*Contract_Readin.php is used by the modify existing Contract in Contracts.php and also by ContractCosting.php */

$ContractHeaderSQL = "SELECT contractdescription,
							contracts.debtorno,
							contracts.branchcode,
							contracts.loccode,
							contracts.customerref,
							status,
							categoryid,
							orderno,
							margin,
							wo,
							requireddate,
							drawing,
							exrate,
							debtorsmaster.name,
							custbranch.brname,
							debtorsmaster.currcode
						FROM contracts INNER JOIN debtorsmaster
						ON contracts.debtorno=debtorsmaster.debtorno
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						INNER JOIN custbranch
						ON debtorsmaster.debtorno=custbranch.debtorno
						AND contracts.branchcode=custbranch.branchcode
						INNER JOIN locationusers ON locationusers.loccode=contracts.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
						WHERE contractref= '" . $ContractRef . "'";

$ErrMsg =  __('The contract cannot be retrieved because');
$ContractHdrResult = DB_query($ContractHeaderSQL, $ErrMsg);

if (DB_num_rows($ContractHdrResult)==1 and !isset($_SESSION['Contract'.$identifier]->ContractRef )) {

	$MyRow = DB_fetch_array($ContractHdrResult);
	$_SESSION['Contract'.$identifier]->ContractRef = $ContractRef;
	$_SESSION['Contract'.$identifier]->ContractDescription = $MyRow['contractdescription'];
	$_SESSION['Contract'.$identifier]->DebtorNo = $MyRow['debtorno'];
	$_SESSION['Contract'.$identifier]->BranchCode = $MyRow['branchcode'];
	$_SESSION['Contract'.$identifier]->LocCode = $MyRow['loccode'];
	$_SESSION['Contract'.$identifier]->CustomerRef = $MyRow['customerref'];
	$_SESSION['Contract'.$identifier]->Status = $MyRow['status'];
	$_SESSION['Contract'.$identifier]->CategoryID = $MyRow['categoryid'];
	$_SESSION['Contract'.$identifier]->OrderNo = $MyRow['orderno'];
	$_SESSION['Contract'.$identifier]->Margin = $MyRow['margin'];
	$_SESSION['Contract'.$identifier]->WO = $MyRow['wo'];
	$_SESSION['Contract'.$identifier]->RequiredDate = ConvertSQLDate($MyRow['requireddate']);
	$_SESSION['Contract'.$identifier]->Drawing = $MyRow['drawing'];
	$_SESSION['Contract'.$identifier]->ExRate = $MyRow['exrate'];
	$_SESSION['Contract'.$identifier]->BranchName = $MyRow['brname'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Contract'.$identifier]->CustomerName = $MyRow['name'];
	$_SESSION['Contract'.$identifier]->CurrCode = $MyRow['currcode'];


/*now populate the contract BOM array with the items required for the contract */

	$ContractBOMsql = "SELECT contractbom.stockid,
							stockmaster.description,
							contractbom.workcentreadded,
							contractbom.quantity,
							stockmaster.units,
							stockmaster.decimalplaces,
							stockmaster.actualcost AS cost
						FROM contractbom INNER JOIN stockmaster
						ON contractbom.stockid=stockmaster.stockid
						WHERE contractref ='" . $ContractRef . "'";

	$ErrMsg =  __('The bill of material cannot be retrieved because');
	$ContractBOMResult = DB_query($ContractBOMsql, $ErrMsg);

	if (DB_num_rows($ContractBOMResult) > 0) {
		while ($MyRow=DB_fetch_array($ContractBOMResult)) {
			$_SESSION['Contract'.$identifier]->Add_To_ContractBOM($MyRow['stockid'],
																	$MyRow['description'],
																	$MyRow['workcentreadded'],
																	$MyRow['quantity'],
																	$MyRow['cost'],
																	$MyRow['units'],
																	$MyRow['decimalplaces']);
		} /* add contract bill of materials BOM lines*/
	} //end is there was a contract BOM to add
	//Now add the contract requirments
	$ContractReqtsSQL = "SELECT requirement,
								quantity,
								costperunit,
								contractreqid
						FROM contractreqts
						WHERE contractref ='" . $ContractRef . "'
						ORDER BY contractreqid";

	$ErrMsg =  __('The other contract requirementscannot be retrieved because');
	$ContractReqtsResult = DB_query($ContractReqtsSQL, $ErrMsg);

	if (DB_num_rows($ContractReqtsResult) > 0) {
		while ($MyRow=DB_fetch_array($ContractReqtsResult)) {
			$_SESSION['Contract'.$identifier]->Add_To_ContractRequirements($MyRow['requirement'],
																		   $MyRow['quantity'],
																		   $MyRow['costperunit'],
																		   $MyRow['contractreqid']);
		} /* add other contract requirments lines*/
	} //end is there are contract other contract requirments to add
} // end if there was a header for the contract
