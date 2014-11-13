<?php
/* $Id: Contract_Readin.php 3692 2010-08-15 09:22:08Z daintree $ */
/*Contract_Readin.php is used by the modify existing Contract in Contracts.php and also by ContractCosting.php */

$ContractHeaderSQL = "SELECT contractdescription,
							contracts.debtorno,
							contracts.branchcode,
							contracts.loccode,
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

$ErrMsg =  _('The contract cannot be retrieved because');
$DbgMsg =  _('The SQL statement that was used and failed was');
$ContractHdrResult = DB_query($ContractHeaderSQL,$ErrMsg,$DbgMsg);

if (DB_num_rows($ContractHdrResult)==1 and !isset($_SESSION['Contract'.$identifier]->ContractRef )) {

	$myrow = DB_fetch_array($ContractHdrResult);
	$_SESSION['Contract'.$identifier]->ContractRef = $ContractRef;
	$_SESSION['Contract'.$identifier]->ContractDescription = $myrow['contractdescription'];
	$_SESSION['Contract'.$identifier]->DebtorNo = $myrow['debtorno'];
	$_SESSION['Contract'.$identifier]->BranchCode = $myrow['branchcode'];
	$_SESSION['Contract'.$identifier]->LocCode = $myrow['loccode'];
	$_SESSION['Contract'.$identifier]->Status = $myrow['status'];
	$_SESSION['Contract'.$identifier]->CategoryID = $myrow['categoryid'];
	$_SESSION['Contract'.$identifier]->OrderNo = $myrow['orderno'];
	$_SESSION['Contract'.$identifier]->Margin = $myrow['margin'];
	$_SESSION['Contract'.$identifier]->WO = $myrow['wo'];
	$_SESSION['Contract'.$identifier]->RequiredDate = ConvertSQLDate($myrow['requireddate']);
	$_SESSION['Contract'.$identifier]->Drawing = $myrow['drawing'];
	$_SESSION['Contract'.$identifier]->ExRate = $myrow['exrate'];
	$_SESSION['Contract'.$identifier]->BranchName = $myrow['brname'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Contract'.$identifier]->CustomerName = $myrow['name'];
	$_SESSION['Contract'.$identifier]->CurrCode = $myrow['currcode'];


/*now populate the contract BOM array with the items required for the contract */

	$ContractBOMsql = "SELECT contractbom.stockid,
							stockmaster.description,
							contractbom.workcentreadded,
							contractbom.quantity,
							stockmaster.units,
							stockmaster.decimalplaces,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost
						FROM contractbom INNER JOIN stockmaster
						ON contractbom.stockid=stockmaster.stockid
						WHERE contractref ='" . $ContractRef . "'";

	$ErrMsg =  _('The bill of material cannot be retrieved because');
	$DbgMsg =  _('The SQL statement that was used to retrieve the contract bill of material was');
	$ContractBOMResult = DB_query($ContractBOMsql,$ErrMsg,$DbgMsg);

	if (DB_num_rows($ContractBOMResult) > 0) {
		while ($myrow=DB_fetch_array($ContractBOMResult)) {
			$_SESSION['Contract'.$identifier]->Add_To_ContractBOM($myrow['stockid'],
																	$myrow['description'],
																	$myrow['workcentreadded'],
																	$myrow['quantity'],
																	$myrow['cost'],
																	$myrow['units'],
																	$myrow['decimalplaces']);
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

	$ErrMsg =  _('The other contract requirementscannot be retrieved because');
	$DbgMsg =  _('The SQL statement that was used to retrieve the other contract requirments was');
	$ContractReqtsResult = DB_query($ContractReqtsSQL,$ErrMsg,$DbgMsg);

	if (DB_num_rows($ContractReqtsResult) > 0) {
		while ($myrow=DB_fetch_array($ContractReqtsResult)) {
			$_SESSION['Contract'.$identifier]->Add_To_ContractRequirements($myrow['requirement'],
																		   $myrow['quantity'],
																		   $myrow['costperunit'],
																		   $myrow['contractreqid']);
		} /* add other contract requirments lines*/
	} //end is there are contract other contract requirments to add
} // end if there was a header for the contract
?>