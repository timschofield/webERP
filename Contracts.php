<?php

/* $Id: Contract_Header.php 3325 2010-01-25 16:50:32Z tim_schofield $ */

$PageSecurity = 4;
include('includes/DefineContractClass.php');
include('includes/session.inc');

if (isset($_GET['ModifyContractNo'])) {
	$title = _('Modify Contract') . ' ' . $_GET['ModifyContractNo'];
} else {
	$title = _('Contract Entry');
}

if (isset($_GET['CustomerID'])) {
	$_POST['SelectedCustomer']=$_GET['CustomerID'];
}

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*If the page is called is called without an identifier being set then
 * it must be either a new contract, or the start of a modification of an
 * existing contract, and so we must create a new identifier.
 *
 * The identifier only needs to be unique for this php session, so a
 * unix timestamp will be sufficient.
 */

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}


if (isset($_GET['NewContract']) AND isset($_SESSION['Contract'.$identifier])){
	unset($_SESSION['Contract'.$identifier]);
	$_SESSION['ExistingContract'] = 0;
}

if (isset($_GET['NewContract']) AND isset($_GET['SelectedCustomer'])) {
		/*
		* initialize a new contract
		*/
		$_SESSION['ExistingContract']=0;
		unset($_SESSION['Contract'.$identifier]->ContractBOM);
		unset($_SESSION['Contract'.$identifier]->ContractReqts);
		unset($_SESSION['Contract'.$identifier]);
		/* initialize new class object */
		$_SESSION['Contract'.$identifier] = new Contract;
		
		$_POST['SelectedCustomer'] = $_GET['SelectedCustomer'];
		
		$_SESSION['Contract'.$identifier]->Status =0;
		/*The customer is checked for credit and the Contract Object populated
		 * using the usual logic of when a customer is selected 
		 * */
}

if(isset($_SESSION['Contract'.$identifier]) AND 
			(isset($_POST['EnterContractBOM']) 
				OR isset($_POST['EnterContractRequirements']))){
	/**  Ensure session variables updated */	
	
	$_SESSION['Contract'.$identifier]->ContractRef=$_POST['ContractRef'];
	$_SESSION['Contract'.$identifier]->ContractDescription=$_POST['ContractDescription'];
	$_SESSION['Contract'.$identifier]->CategoryID = $_POST['CategoryID'];
	$_SESSION['Contract'.$identifier]->RequiredDate = $_POST['RequiredDate'];
	$_SESSION['Contract'.$identifier]->Margin = $_POST['Margin'];
	$_SESSION['Contract'.$identifier]->CustomerRef = $_POST['CustomerRef'];
	$_SESSION['Contract'.$identifier]->QuantityReqd = $_POST['QuantityReqd'];
	$_SESSION['Contract'.$identifier]->Units = $_POST['Units'];
	$_SESSION['Contract'.$identifier]->ExRate = $_POST['ExRate'];


/*User hit the button to enter line items -
  then meta refresh to Contract_Items.php*/
	$InputError = false;
	if(strlen($_SESSION['Contract'.$identifier]->ContractRef)<2){
		prnMsg(_('The contract reference must be entered (and be longer than 2 characters) before the requirements of the contract can be setup'),'warn');
		$InputError = true;
	}
	
	if (isset($_POST['EnterContractBOM']) AND !$InputError){
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/ContractBOM.php?' . SID . 'identifier='.$identifier. "'>";
		echo '<p>';
		prnMsg(_('You should automatically be forwarded to the entry of the Contract line items page') . '. ' .
		_('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
		"<a href='$rootpath/ContractBOM.php?" . SID. 'identifier='.$identifier . "'>" . _('click here') . '</a> ' . _('to continue'),'info');
		include('includes/footer.inc');
		exit;
	}
	if (isset($_POST['EnterContractRequirements']) AND !$InputError){
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/ContractRequirements.php?' . SID . 'identifier='.$identifier. "'>";
		echo '<p>';
		prnMsg(_('You should automatically be forwarded to the entry of the Contract requirements page') . '. ' .
		_('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
		"<a href='$rootpath/ContractRequirements.php?" . SID. 'identifier='.$identifier . "'>" . _('click here') . '</a> ' . _('to continue'),'info');
		include('includes/footer.inc');
		exit;
	}
} /* end of if going to contract BOM or contract requriements */

echo '<a href="'. $rootpath . '/ContractSelect.php?' . SID . 'identifier='.$identifier.'">'. _('Back to Contracts'). '</a><br>';

//attempting to upload the drawing image file
if (isset($_FILES['Drawing']) AND $_FILES['Drawing']['name'] !='' AND $_SESSION['Contract'.$identifier]->ContractRef!='') {

	$result = $_FILES['Drawing']['error'];
 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$filename = $_SESSION['part_pics_dir'] . '/' . $_SESSION['Contract'.$identifier]->ContractRef . '.jpg';

	 //But check for the worst
	if (strtoupper(substr(trim($_FILES['Drawing']['name']),strlen($_FILES['Drawing']['name'])-3))!='JPG'){
		prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'),'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['Drawing']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
		$UploadTheFile ='No';
	} elseif ( $_FILES['Drawing']['type'] == 'text/plain' ) {  //File Type Check
		prnMsg( _('Only graphics files can be uploaded'),'warn');
         	$UploadTheFile ='No';
	} elseif (file_exists($filename)){
		prnMsg(_('Attempting to overwrite an existing item image'),'warn');
		$result = unlink($filename);
		if (!$result){
			prnMsg(_('The existing image could not be removed'),'error');
			$UploadTheFile ='No';
		}
	}

	if ($UploadTheFile=='Yes'){
		$result  =  move_uploaded_file($_FILES['Drawing']['tmp_name'], $filename);
		$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Something is wrong with uploading the file');
	}
 /* EOR Add Image upload for New Item  - by Ori */
}


/*The page can be called with ModifyContractRef=x where x is a contract 
 * reference. The page then looks up the details of contract x and allows
 * these details to be modified */

if (isset($_GET['ModifyContractRef'])){

	if (isset($_SESSION['Contract'.$identifier])){
			unset ($_SESSION['Contract'.$identifier]->ContractBOM);
			unset ($_SESSION['Contract'.$identifier]->ContractReqts);
			unset ($_SESSION['Contract'.$identifier]);
	}
	
	$_SESSION['ExistingContract']=$_GET['ModifyContractRef'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Contract'.$identifier] = new Contract;

	/*read in all the guff from the selected contract into the contract Class variable  */

	$ContractHeaderSQL = "SELECT contractdescription,
					  				debtorno,
					  				branchcode,
					  				status,
					  				categoryid,
					  				orderno,
					  				margin,
					  				wo,
					  				requireddate,
					  				quantityreqd,
					  				units,
					  				drawing,
					  				exrate
					  		FROM contracts
					  		WHERE contractref= '" . $_GET['ModifyContractRef'] . "'";

	$ErrMsg =  _('The contract cannot be retrieved because');
	$DbgMsg =  _('The SQL statement that was used and failed was');
	$ContractHdrResult = DB_query($ContractHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($ContractHdrResult)==1 and !isset($_SESSION['Contract'.$identifier]->ContractRef )) {

		$myrow = DB_fetch_array($ContractHdrResult);
		$_SESSION['Contract'.$identifier]->ContractRef = $_GET['ModifyOrderRef'];
		$_SESSION['Contract'.$identifier]->ContractDescription = $myrow['contractdescription'];
		$_SESSION['Contract'.$identifier]->DebtorNo = $myrow['debtorno'];
		$_SESSION['Contract'.$identifier]->BranchCode = $myrow['branchcode'];
		$_SESSION['Contract'.$identifier]->Status = $myrow['status'];
		$_SESSION['Contract'.$identifier]->CategoryID = $myrow['categoryid'];
		$_SESSION['Contract'.$identifier]->OrderNo = $myrow['orderno'];
		$_SESSION['Contract'.$identifier]->Margin = $myrow['Margin'];
		$_SESSION['Contract'.$identifier]->WO = $myrow['wo'];
		$_SESSION['Contract'.$identifier]->RequiredDate = $myrow['RequiredDate'];
		$_SESSION['Contract'.$identifier]->QuantityRequired = $myrow['QuantityRequired'];
		$_SESSION['Contract'.$identifier]->Units = $myrow['units'];
		$_SESSION['Contract'.$identifier]->Drawing = $myrow['drawing'];
		$_SESSION['Contract'.$identifier]->ExRate = $myrow['exrate'];
		
/*now populate the contract BOM array with the items required for the contract */

		$ContractBOMsql = "SELECT contractbom.stockid,
									contractbom.workcentreadded,
									contractbom.quantity,
									stockmaster.units,
									stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost
							FROM contractbom INNER JOIN stockmaster
							ON contractbom.stockid=stockmaster.stockid
							WHERE contractref ='" . $_GET['ModifyContractRef'] . "'";

	  	$ErrMsg =  _('The bill of material cannot be retrieved because');
		$DbgMsg =  _('The SQL statement that was used to retrieve the contract bill of material was');
		$ContractBOMResult = db_query($ContractBOMSQL,$db,$ErrMsg,$DbgMsg);

		if (db_num_rows($ContractBOMResult) > 0) {
			while ($myrow=db_fetch_array($ContractBOMResult)) {
				$_SESSION['Contract'.$identifier]->Add_To_ContractBOM($myrow['stockid'], 
																		$myrow['workcentreadded'],
																		$myrow['quantity'],
																		$myrow['cost'],
																		$myrow['units']);
			} /* add contract bill of materials BOM lines*/
		} //end is there was a contract BOM to add
		//Now add the contract requirments
		$ContractReqtsSQL = "SELECT component,
									quantity,
									costperunit,
									contractreqid
							FROM contractreqts 
							WHERE contractref ='" . $_GET['ModifyContractRef'] . "' 
							ORDER BY contractreqid";

	  	$ErrMsg =  _('The other contract requirementscannot be retrieved because');
		$DbgMsg =  _('The SQL statement that was used to retrieve the other contract requirments was');
		$ContractReqtsResult = db_query($ContractReqtsSQL,$db,$ErrMsg,$DbgMsg);

		if (db_num_rows($ContractReqtsResult) > 0) {
			while ($myrow=db_fetch_array($ContractReqtsResult)) {
				$_SESSION['Contract'.$identifier]->Add_To_ContractRequirements($myrow['component'],
																			   $myrow['quantity'],
																			   $myrow['costperunit'],
																			   $myrow['contractreqid']);
			} /* add other contract requirments lines*/
		} //end is there are contract other contract requirments to add
   } // end if there was a header for the contract

}// its an existing contract to readin

if (isset($_POST['CancelContract'])) {
/*The cancel button on the header screen - to delete the contract */
	$OK_to_delete = 1;	 //assume this in the first instance
	if(!isset($_SESSION['ExistingContract']) OR $_SESSION['ExistingContract']!=0) {
		/* need to check that not already ordered by the customer - status = 0  */
		if($_SESSION['Contract'.$identifier]->Status==1){
			$result = DB_query('SELECT orderno FROM salesorders WHERE orderno=' . $_SESSION['Contract'.$identifier]->OrderNo,$db);
			if (DB_num_rows($result)==1){
				$OK_to_delete =0;
				prnMsg( _('The contract has already been ordered by the customer the order must also be deleted first before the contract can be deleted'),'warn');
			}
		}
	}
	if ($OK_to_delete==1){
		if($_SESSION['ExistingContract']!=0){

			$sql = "DELETE FROM contractbom WHERE contractref='" . $_SESSION['Contract'.$identifier]->ContractRef . "'";
			$ErrMsg = _('The contract bill of materials could not be deleted because');
			$DelResult=DB_query($sql,$db,$ErrMsg);
			$sql = "DELETE FROM contractreqts WHERE contractref='" . $_SESSION['Contract'.$identifier]->ContractRef . "'";
			$ErrMsg = _('The contract requirements could not be deleted because');
			$DelResult=DB_query($sql,$db,$ErrMsg);
			$sql= "DELETE FROM contracts WHERE contractref='" . $_SESSION['Contract'.$identifier]->ContractRef . "'";
			$ErrMsg = _('The contract could not be deleted because');
			$DelResult=DB_query($sql,$db,$ErrMsg);
			
			prnMsg( _('Contract').' '.$_SESSION['Contract'.$identifier]->ContractRef.' '._('has been cancelled'), 'success');
			unset($_SESSION['ExistingContract']);
		}
		unset($_SESSION['Contract'.$identifier]->ContractBOM);
		unset($_SESSION['Contract'.$identifier]->ContractReqts);
		unset($_SESSION['Contract'.$identifier]);
	}
}

if (!isset($_SESSION['Contract'.$identifier])){
	/* It must be a new contract being created
	 * $_SESSION['Contract'.$identifier] would be set up from the order modification
	 * code above if a modification to an existing contract. Also
	 * $ExistingContract would be set to the ContractRef
	 * */

		$_SESSION['ExistingContract']= 0;
		$_SESSION['Contract'.$identifier] = new Contract;
		
		$_SESSION['Contract'.$identifier]->Status = 0; //new contracts are just quotes ...
		
		if ($_SESSION['Contract'.$identifier]->DebtorNo=='' OR !isset($_SESSION['Contract'.$identifier]->DebtorNo)){

/* a session variable will have to maintain if a supplier
 * has been selected for the order or not the session
 * variable CustomerID holds the supplier code already
 * as determined from user id /password entry  */
			$_SESSION['RequireCustomerSelection'] = 1;
		} else {
			$_SESSION['RequireCustomerSelection'] = 0;
		}
}

if (isset($_POST['CommitContract'])){ 
	/*This is the bit where the contract object is commited to the database after a bit of error checking */
	
	//First update the session['Contract'.$identifier] variable with all inputs from the form
	
	$InputError = False; //assume no errors on input then test for errors
	if (strlen($_POST['ContractRef'])<2){
		prnMsg(_('The contract reference is expected to be more than 2 characters long. Please alter the contract reference before proceeding.'),'error');
		$InputError = true;
	}
	//The contractRef cannot be the same as an existing stockid or contractref
	$result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['ContractRef'] . "'",$db); 
	if (DB_num_rows($result)==1){
		prnMsg(_('The contract reference cannot be the same as a previously created stock item. Please modify the contract reference before continuing'),'error');
		$InputError=true;
	}
	if (strlen($_POST['ContractDescription'])<10){
		prnMsg(_('The contract description is expected to be more than 10 characters long. Please alter the contract description in full before proceeding.'),'error');
		$InputError = true;
	}
	if (! Is_Date($_POST['RequiredDate'])){
		prnMsg (_('The date the contract is required to be completed by must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$InputError =true;
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['RequiredDate']) AND $_POST['RequiredDate']!=''){
		prnMsg(_('The date that the contract is to be completed by is expected to be a date in the future. Make the required date a date after today before proceeding.'),'error');
		$InputError =true;
	}
	if (!is_numeric($_POST['QuantityReqd'])){
		prnMsg(_('The quantity required is expected to be numeric. Please enter a number in the quantity required field before proceeding.'),'error');
		$InputError=true;
	}
	if ($_POST['QuantityReqd']<=0){
		prnMsg(_('The quantity required is expected to be a positive number. Please enter a postive number in the quantity required field before proceeding.'),'error');
		$InputError=true;
	}
	if (!$InputError) { 
		$_SESSION['Contract'.$identifier]->ContractRef=$_POST['ContractRef'];
		$_SESSION['Contract'.$identifier]->ContractDescription=$_POST['ContractDescription'];
		$_SESSION['Contract'.$identifier]->CategoryID = $_POST['CategoryID'];
		$_SESSION['Contract'.$identifier]->RequiredDate = $_POST['RequiredDate'];
		$_SESSION['Contract'.$identifier]->Margin = $_POST['Margin'];
		$_SESSION['Contract'.$identifier]->Status = $_POST['Status'];
		$_SESSION['Contract'.$identifier]->CustomerRef = $_POST['CustomerRef'];
		$_SESSION['Contract'.$identifier]->QuantityReqd = $_POST['QuantityReqd'];
		$_SESSION['Contract'.$identifier]->Units = $_POST['Units'];
		$_SESSION['Contract'.$identifier]->ExRate = $_POST['ExRate'];
	}
		
	$sql = "SELECT contractref,
					debtorno,
					branchcode,
					categoryid,
					requireddate,
					margin,
					customerref,
					quantityreqd,
					units,
					exrate,
					status 
			FROM contracts 
			WHERE contractref='" . $_POST['ContractRef'] . "'"; 
			
	$result = DB_query($sql,$db); 
	if (DB_num_rows($result)==1){ // then we have an existing contract with this contractref
		$ExistingContract = DB_fetch_array($result);
		if ($ExistingContract['debtorno'] != $_SESSION['Contract'.$identifier]->DebtorNo){
			prnMsg(_('The contract reference cannot be the same as a previously created contract for another customer. Please modify the contract reference before continuing'),'error');
			$InputError=true;
		}
		if ($ExistingContract['status'] == 0 AND $_POST['Status']==2){ 
			prnMsg(_('The contract must first be made into a customer quotation - only then can it be completed.'),'error');
			$InputError=true;	
		}
		if ($ExistingContract['status'] == 1 AND $_POST['Status']==0){
			prnMsg(_('Having made the contract into a quotation it cannot now be changed back into just a costing.'),'error');
			$InputError=true;	
		}
		if ($ExistingContract['status'] == 1 AND $_POST['Status']==2){
			/* then we are completing this contract - need to :
			 * close the work order
			 * do the variances postings 
			 */
			 
			 
			 
			 
			 
			 	
		} 
		if($ExistingContract['status']<=1 AND ! $InputError){
			//then we can accept any changes at all do an update on the whole lot
			$sql = "UPDATE contracts SET categoryid = '" . $_POST['CategoryID'] ."',
										requireddate = '" . FormatDateForSQL($_POST['RequiredDate']) . "', 
										margin = " . $_POST['Margin'] . ", 
										customerref = '" . $_POST['CustomerRef'] . "', 
										quantityreqd = " . $_POST['QuantityReqd'] . ", 
										units = '" . $_POST['Units'] . "', 
										exrate = " . $_POST['ExRate'] . ",
										status = " . $_POST['Status'] . " 
							WHERE contractref ='" . $_POST['ContractRef'] . "'";
			$ErrMsg = _('Cannot update the contract because');
			$result = DB_query($sql,$db,$ErrMsg);
			/* also need to update the items on the contract BOM  - delete the existing contract BOM then add these items*/
			$result = DB_query("DELETE FROM contractbom WHERE contractref='" .$_POST['ContractRef'] . "'",$db);
			$ErrMsg = _('Could not add a component to the contract bill of material');
			foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component){
				$sql = "INSERT INTO contractbom (contractref,
												stockid,
												workcentreadded,
												quantity)
								VALUES ( '" . $_POST['ContractRef'] . "',
										'" . $Component->StockID . "',
										'" . $Component->WorkCentre . "',
										" . $Component->Quantity . ")";
				$result = DB_query($sql,$db,$ErrMsg);
			}												
			
			/*also need to update the items on the contract requirements  - delete the existing database entries then add these */
			$result = DB_query("DELETE FROM contractreqts WHERE contractref='" .$_POST['ContractRef'] . "'",$db);
			$ErrMsg = _('Could not add a requirement to the contract requirements');
			foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement){
				$sql = "INSERT INTO contractreqts (contractref,
												requirement,
												costperunit,
												quantity)
								VALUES ( '" . $_POST['ContractRef'] . "',
										'" . $Requirement->Requirement . "',
										'" . $Requirement->CostPerUnit . "',
										" . $Requirement->Quantity . ")";
				$result = DB_query($sql,$db,$ErrMsg);
			}
										
			prnMsg(_('The changes to the contract have been committed to the database'),'success');
		} 
		if ($ExistingContract['status']==1 AND ! $InputError){
			//then the quotation will need to be updated with the revised contract cost if necessary
			
			
			
		}
		if ($ExistingContract['status'] == 0 AND $_POST['Status']==1){ 
			/*we are updating the status on the contract to a quotation so we need to 
			 * add a new item for the contract into the stockmaster
			 * add a salesorder header and detail as a quotation for the item
			 */
			
			
		} 
	} else { /*Its a new contract - so insert */
		
		if ($_POST['Status'] !=0){
			//a new contract being created with a status of not a quotation - create it anyway but with status=0
			prnMsg(_('A contract can only be created with a status of costing initially'),'warn');
		}
		$sql = "INSERT INTO contracts ( contractref,
										debtorno,
										branchcode,
										categoryid,
										requireddate,
										margin,
										customerref,
										quantityreqd,
										units,
										exrate)
							VALUES ('" . $_POST['ContractRef'] . "',
									'" . $_SESSION['Contract'.$identifier]->DebtorNo  . "',
									'" . $_SESSION['Contract'.$identifier]->BranchCode . "',
									'" . $_POST['CategoryID'] . "',
									'" . FormatDateForSQL($_POST['RequiredDate']) . "', 
									" . $_POST['Margin'] . ",
									'" . $_POST['CustomerRef'] . "',
									" . $_POST['QuantityReqd'] . ",
									'" . $_POST['Units'] . "',
									". $_POST['ExRate'] .")";
				
		$ErrMsg = _('The new contract could not be added because');
		$result = DB_query($sql,$db,$ErrMsg);
		
		/*Also need to add the reqts and contracbom*/
		$ErrMsg = _('Could not add a component to the contract bill of material');
		foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component){
			$sql = "INSERT INTO contractbom (contractref,
											stockid,
											workcentreadded,
											quantity)
							VALUES ( '" . $_POST['ContractRef'] . "',
									'" . $Component->StockID . "',
									'" . $Component->WorkCentre . "',
									" . $Component->Quantity . ")";
			$result = DB_query($sql,$db,$ErrMsg);
		}												
		
		$ErrMsg = _('Could not add a requirement to the contract requirements');
		foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement){
			$sql = "INSERT INTO contractreqts (contractref,
											requirement,
											costperunit,
											quantity)
							VALUES ( '" . $_POST['ContractRef'] . "',
									'" . $Requirement->Requirement . "',
									'" . $Requirement->CostPerUnit . "',
									" . $Requirement->Quantity . ")";
			$result = DB_query($sql,$db,$ErrMsg);
		}
		prnMsg(_('The new contract has been added to the database'),'success');
	} //end of adding a new contract
}//end of commital to database

if (isset($_POST['SearchCustomers'])){

	if (($_POST['CustKeywords']!='') AND (($_POST['CustCode']!='') OR ($_POST['CustPhone']!=''))) {
		prnMsg( _('Customer Branch Name keywords have been used in preference to the Customer Branch Code or Branch Phone Number entered'), 'warn');
	}
	if (($_POST['CustCode']!='') AND ($_POST['CustPhone']!='')) {
		prnMsg(_('Customer Branch Code has been used in preference to the Customer Branch Phone Number entered'), 'warn');
	}
	if (($_POST['CustKeywords']=='') AND ($_POST['CustCode']=='')  AND ($_POST['CustPhone']=='')) {
		prnMsg(_('At least one Customer Branch Name keyword OR an extract of a Customer Branch Code or Branch Phone Number must be entered for the search'), 'warn');
	} else {
		if (strlen($_POST['CustKeywords'])>0) {
		//insert wildcard characters in spaces
			$_POST['CustKeywords'] = strtoupper(trim($_POST['CustKeywords']));
			$SearchString = '%' . str_replace(' ', '%', $_POST['CustKeywords']) . '%';

			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name
				FROM custbranch
				LEFT JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE custbranch.brname " . LIKE . " '$SearchString'
				AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno, custbranch.branchcode";
	
		} elseif (strlen($_POST['CustCode'])>0){

			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));

			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name
				FROM custbranch
				LEFT JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE custbranch.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%' OR custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%' 
				AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno";
			
		} elseif (strlen($_POST['CustPhone'])>0){
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name
				FROM custbranch
				LEFT JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
				AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno";
		}

		$ErrMsg = _('The searched customer records requested cannot be retrieved because');
		$result_CustSelect = DB_query($SQL,$db,$ErrMsg);

		if (DB_num_rows($result_CustSelect)==1){
			$myrow=DB_fetch_array($result_CustSelect);
			$_POST['SelectedCustomer'] = $myrow['debtorno'] . '-' . $myrow['branchcode'];
		} elseif (DB_num_rows($result_CustSelect)==0){
			prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
		}
	} /*one of keywords or custcode was more than a zero length string */
} /*end of if search for customer codes/names */


if (isset($_POST['SelectedCustomer'])) {

/* will only be true if page called from customer selection form
 * or set because only one customer record returned from a search
 * so parse the $Select string into debtorno and branch code */
	$CustomerBranchArray = explode('-',$_POST['SelectedCustomer']);
	$_SESSION['Contract'.$identifier]->DebtorNo  = trim($CustomerBranchArray[0]);
	$_SESSION['Contract'.$identifier]->BranchCode = trim($CustomerBranchArray[1]);
	
	$sql = "SELECT debtorsmaster.name,
					custbranch.brname,
					debtorsmaster.currcode,
					debtorsmaster.holdreason,
					holdreasons.dissallowinvoices,
					currencies.rate
			FROM debtorsmaster INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			INNER JOIN custbranch 
			ON debtorsmaster.debtorno=custbranch.debtorno
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			WHERE debtorsmaster.debtorno='" . $_SESSION['Contract'.$identifier]->DebtorNo  . "' 
			AND custbranch.branchcode='" . $_SESSION['Contract'.$identifier]->BranchCode . "'" ;

	$ErrMsg = _('The customer record selected') . ': ' . $_POST['SelectedCustomer'] . ' ' .
		_('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_array($result);
	if (DB_num_rows($result)==0){
		prnMsg(_('The customer details were unable to be retrieved'),'error');
		if ($debug==1){
			prnMsg(_('The SQL used that failed to get the customer details was:') . '<p>' . $sql,'error');
		}
	} else {
		$_SESSION['Contract'.$identifier]->BranchName = $myrow['brname'];
		$_SESSION['RequireCustomerSelection'] = 0;
		$_SESSION['Contract'.$identifier]->CustomerName = $myrow['name'];
		$_SESSION['Contract'.$identifier]->CurrCode = $myrow['currcode'];
		$_SESSION['Contract'.$identifier]->ExRate = $myrow['rate'];
	
		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit contracts */
			$CreditAvailable = GetCreditAvailable($_SESSION['Contract'.$identifier]->DebtorNo,$db);
			if ($_SESSION['CheckCreditLimits']==1 AND $CreditAvailable <=0){
				prnMsg(_('The') . ' ' . $_SESSION['Contract'.$identifier]->CustomerName . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.inc');
				exit;
			}
		}	
	} //a customer was retrieved ok
} //end if a customer has just been selected


if (!isset($_SESSION['Contract'.$identifier]->DebtorNo) 
		OR $_SESSION['Contract'.$identifier]->DebtorNo=='' ) {

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
		_('Contract') . '" alt="">' . ' ' . _('Contract: Select Customer') . '';
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' .SID .'&identifier=' . $identifier .'" name="CustomerSelection" method=post>';
	
	echo '<table cellpadding=3 colspan=4 class=selection>
			<tr>
			<td><h5>' . _('Part of the Customer Branch Name') . ':</h5></td>
			<td><input tabindex=1 type="Text" name="CustKeywords" size=20	maxlength=25></td>
			<td><h2><b>' . _('OR') . '</b></h2></td>
			<td><h5>' .  _('Part of the Customer Branch Code'). ':</h5></td>
			<td><input tabindex=2 type="Text" name="CustCode" size=15	maxlength=18></td>
			<td><h2><b>' . _('OR') . '</b></h2></td>
			<td><h5>' . _('Part of the Branch Phone Number') . ':</h5></td>
			<td><input tabindex=3 type="Text" name="CustPhone" size=15	maxlength=18></td>
		</tr>
		</table>
		<br><div class="centre"><input tabindex=4 type=submit name="SearchCustomers" value="' . _('Search Now') . '">
		<input tabindex=5 type=submit action=reset value="' . _('Reset') .'"></div>';
	
	if (isset($result_CustSelect)) {

		echo '<table cellpadding=2 colspan=7>';

		$TableHeader = '<br><tr>
				<th>' . _('Customer') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Contact') . '</th>
				<th>' . _('Phone') . '</th>
				<th>' . _('Fax') . '</th>
				</tr>';
		echo $TableHeader;

		$j = 1;
		$k = 0; //row counter to determine background colour
		$LastCustomer='';
		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			if ($LastCustomer != $myrow['name']) {
				echo '<td>'.$myrow['name'].'</td>';
			} else {
				echo '<td></td>';
			}
			echo '<td><input tabindex='.number_format($j+5).' type=submit name="Submit" value="'.$myrow['brname'].'"</td>
					<input type=hidden name="SelectedCustomer" value="'.$myrow['debtorno'].' - '.$myrow['branchcode'].'">
					<td>'.$myrow['contactname'].'</td>
					<td>'.$myrow['phoneno'].'</td>
					<td>'.$myrow['faxno'].'</td>
					</tr></form>';
			$LastCustomer=$myrow['name'];
			$j++;
//end of page full new headings if
		}
//end of while loop

		echo '</table>';

	}//end if results to show

//end if RequireCustomerSelection
} else { /*A customer is already selected so get into the contract setup proper */

	echo '<form name="ContractEntry" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier=' . $identifier . '" method="post">';

	echo '<p class="page_title_text">
            <img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Contract') . '" alt="">
	        ' . $_SESSION['Contract'.$identifier]->CustomerName;
	
	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['Contract'.$identifier]->CurrCode){
		echo ' - ' . _('All amounts stated in') . ' ' . $_SESSION['Contract'.$identifier]->CurrCode . '<br />';
	}
	if ($_SESSION['ExistingContract']) {
		echo  _('Modify Contract') . ': ' . $_SESSION['Contract'.$identifier]->ContractRef;
	}
	echo '</p>';
	
	/*Set up form for entry of contract header stuff */

	echo '<table>';
	echo '<tr><td>' . _('Contract Reference') . ':</td><td>';
	if ($_SESSION['Contract'.$identifier]->Status==0) { 
		/*Then the contract has not become an order yet and we can allow changes to the ContractRef */
		echo '<input type="text" name="ContractRef" size=21	maxlength=20 value="' . $_SESSION['Contract'.$identifier]->ContractRef . '">';
	} else {
		/*Just show the contract Ref - dont allow modification */
		echo '<input type="hidden" name="ContractRef" value="' . $_SESSION['Contract'.$identifier]->ContractRef . '">' . $_SESSION['Contract'.$identifier]->ContractRef;
	}
	echo '</td></tr>';
	echo '<tr><td>' . _('Category') . ':</td><td><select name="CategoryID" onChange="ReloadForm(ItemForm.UpdateCategories)">';

	$sql = 'SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype!="A"';
	$ErrMsg = _('The stock categories could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	while ($myrow=DB_fetch_array($result)){
		if (!isset($_SESSION['Contract'.$identifier]->CategoryID) or $myrow['categoryid']==$_SESSION['Contract'.$identifier]->CategoryID){
			echo '<option selected VALUE="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'];
		} else {
			echo '<option VALUE="'. $myrow['categoryid'] . '">' . $myrow['categorydescription'];
		}
	}

	echo '</select><a target="_blank" href="'. $rootpath . '/StockCategories.php?' . SID . '">' . _('Add or Modify Contract Categories') . '</a></td></tr>';


	echo '<tr><td>' . _('Units of Measure') . ':</td><td><select name="Units">';
	$sql = 'SELECT unitname FROM unitsofmeasure ORDER by unitname';
	$UOMResult = DB_query($sql,$db);
	
	while( $UOMrow = DB_fetch_array($UOMResult) ) {
	     if (isset($_SESSION['Contract'.$identifier]->Units) AND $_SESSION['Contract'.$identifier]->Units==$UOMrow['unitname']){
		    echo '<option selected value="' . $UOMrow['unitname'] . '">' . $UOMrow['unitname'] . '</option>';
	     } else {
		    echo '<option value="' . $UOMrow['unitname'] . '">' . $UOMrow['unitname']  . '</option>';
	     }
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Contract Description');
	echo ':</td><td><textarea name="ContractDescription" style="width:100%" rows=5>' . $_SESSION['Contract'.$identifier]->ContractDescription . '</textarea></td></tr>';

	echo '<tr><td>'. _('Drawing File') . ' .jpg' . ' ' . _('format only') .':</td><td><input type="file" id="Drawing" name="Drawing"></td></tr>';
	
	if (!isset($_SESSION['Contract'.$identifier]->RequiredDate)) {
		$_SESSION['Contract'.$identifier]->RequiredDate = DateAdd(date($_SESSION['DefaultDateFormat']),'m',1);
	}
	
	echo '<tr><td>' . _('Required Date') . ':</td><td><input type="text" class=date alt="' .$_SESSION['DefaultDateFormat'] . '" name="RequiredDate" size=11 value="' . $_SESSION['Contract'.$identifier]->RequiredDate . '"></td></tr>';
	
	echo '<tr><td>' . _('Quantity Required') . ':</td><td><input type="text" name="QuantityReqd" size=10	maxlength=10 value="' . $_SESSION['Contract'.$identifier]->QuantityReqd . '"></td></tr>';
	
	echo '<tr><td>' . _('Customer Reference') . ':</td><td><input type="text" name="CustomerRef" size=21	maxlength=20 value="' . $_SESSION['Contract'.$identifier]->CustomerRef . '"></td></tr>';
	if (!isset($_SESSION['Contract'.$identifier]->Margin)){
		$_SESSION['Contract'.$identifier]->Margin =50;
	}
	echo '<tr><td>' . _('Gross Profit') . ' %:</td><td><input type="text" name="Margin" size=4 maxlength=4 value="' . $_SESSION['Contract'.$identifier]->Margin . '"></td></tr>';
		
	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['Contract'.$identifier]->CurrCode){
		echo '<tr><td>' . $_SESSION['Contract'.$identifier]->CurrCode . ' ' . _('Exchange Rate') . ':</td>
				<td><input type="text" name="ExRate" size=10 maxlength=10 value=' . $_SESSION['Contract'.$identifier]->ExRate . '></td></tr>';
	} else {
		echo '<input type="hidden" name="ExRate" value=' . $_SESSION['Contract'.$identifier]->ExRate . '>';
	}
	
	echo '<tr><td>' . _('Contract Status') . ':</td><td><select name="Status">';
	
	$StatusText = array();
	$StatusText[0] = _('Setup');
	$StatusText[1] = _('Quote');
	$StatusText[2] = _('Completed');
	for ($Status=0;$Status<3;$Status++) {
		if ($_SESSION['Contract'.$identifier]->Status == $Status){
			echo '<option selected value="' . $Status . '">' . $StatusText[$Status] . '</option>';
		} else {
			echo '<option value="'.$Status.'">' . $StatusText[$Status] . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	if ($_SESSION['Contract'.$identifier]->Status!=0) {
		echo '<tr><td>' . _('Contract Work Order Ref') . ':</td><td>' . $_SESSION['Contract'.$identifier]->WorkOrder . '</td></tr>';
	}
	echo '</table>';

	echo '<table><tr><td>
				<table><tr><th colspan=6>' . _('Stock Items Required') . '</th></tr>';
	$ContractBOMCost = 0;					
	if (count($_SESSION['Contract'.$identifier]->ContractBOM)!=0){
		echo '<tr><th>' . _('Item Code') . '</th>
					<th>' . _('Item Description') . '</th>
					<th>' . _('Quantity') . '</th>
					<th>' . _('Unit') . '</th>
					<th>' . _('Unit Cost') . '</th>
					<th>' . _('Total Cost') . '</th></tr>';
		foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $Component) {
			echo '<tr><td>' . $Component->StockID . '</td>
					<td>' . $Component->ItemDescription . '</td>
					<td class="number">' . $Component->Quantity . '</td>
					<td>' . $Component->UOM . '</td>
					<td class="number">' . number_format($Component->ItemCost,2) . '</td>
					<td class="number">' . number_format(($Component->ItemCost * $Component->Quantity),2) . '</td>
				</tr>';
			$ContractBOMCost += ($Component->ItemCost *  $Component->Quantity);
		}
		echo '<tr><td colspan="5">' . _('Total stock cost') . '</td><td class="number">' . number_format($ContractBOMCost,2) . '</td></tr>';
	} else { //there are no items set up against this contract
		echo '<tr><td colspan="6"><i>' . _('None Entered') . '</i></td></tr>';
	}  
	echo '</table></td>'; //end of contract BOM table
	echo '<td>
			<table><tr><th colspan=4>' . _('Other Requirements') . '</th></tr>';
	$ContractReqtsCost = 0;	
	if (count($_SESSION['Contract'.$identifier]->ContractReqts)!=0){
		echo '<tr><th>' . _('Requirement') . '</th>
					<th>' . _('Quantity') . '</th>
					<th>' . _('Unit Cost') . '</th>
					<th>' . _('Total Cost') . '</th></tr>';
		foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $Requirement) {
			echo '<tr><td>' . $Requirement->Requirement . '</td>
					<td class="number">' . $Requirement->Quantity . '</td>
					<td class="number">' . $Requirement->CostPerUnit . '</td>
					<td class="number">' . number_format(($Requirement->CostPerUnit * $Requirement->Quantity),2) . '</td>
				</tr>';
			$ContractReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
		}
		echo '<tr><td colspan="3">' . _('Total other costs') . '</td><td class="number">' . number_format($ContractReqtsCost,2) . '</td></tr>';
	} else { //there are no items set up against this contract
		echo '<tr><td colspan="4"><i>' . _('None Entered') . '</i></td></tr>';
	}
	echo '</table></td></tr></table>';		
	
	echo'<table><tr><th>' . _('Total Contract Cost') . '</th><th class="number">' . number_format(($ContractBOMCost+$ContractReqtsCost),2) . '</th><th>' . _('Contract Price') . '</th><th class="number">' . number_format(($ContractBOMCost+$ContractReqtsCost)/((100-$_SESSION['Contract'.$identifier]->Margin)/100),2) . '</th></tr></table>';
	
	echo'<p></p>';
			
	echo '<div class="centre"><input type="submit" name="EnterContractBOM" value="' . _('Enter Items Required') . '">
		<input type=submit name="EnterContractRequirements" value="' . _('Enter Other Requirements') .'">
		<input type=submit name="CommitContract" value="' . _('Commit Changes') .'"></div>';
	if ($_SESSION['Contract'.$identifier]->Status!=2) {
		echo '<p><div class="centre"><input type="submit" name="CancelContract" value="' . _('Cancel and Delete Contract') . '"></div></p>';
	}	
} /*end of if customer selected  and entering contract header*/

echo '</form>';
include('includes/footer.inc');
?>