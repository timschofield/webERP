<?php

include('includes/session.php');
$Title = __('Import Debtors And branches');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');

if(!isset($_POST['UpdateIfExists'])) {
	$_POST['UpdateIfExists']=0;
}else{
	$_POST['UpdateIfExists']=1;
}

// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed
$FieldHeadings = array(
	'branchcode',//0
	'debtorno',//1
	'brname',//2
	'braddress1',//3
	'braddress2',//4
	'braddress3',//5
	'braddress4',//6
	'braddress5',//7
	'braddress6',//8
	'lat',//9
	'lng',//10
	'estdeliverydays',//11
	'area',//12
	'salesman',//13
	'fwddate',//14
	'phoneno',//15
	'faxno',//16
	'contactname',//17
	'email',//18
	'defaultlocation',//19
	'taxgroupid',//20
	'defaultshipvia',//21
	'deliverblind',//22
	'disabletrans',//23
	'brpostaddr1',//24
	'brpostaddr2',//25
	'brpostaddr3',//26
	'brpostaddr4',//27
	'brpostaddr5',//28
	'specialinstructions',//29
	'custbranchcode',//30
);

if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing

	//initialize
	$FieldTarget = count($FieldHeadings);
	$InputError = 0;

	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName  = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];
	//get file handle
	$FileHandle = fopen($TempName, 'r');
	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ",");
	// Remove UTF-8 BOM if present
	if (substr($HeadRow[0], 0, 3) === "\xef\xbb\xbf") {
		$HeadRow[0] = substr($HeadRow[0], 3);
	}

	//check for correct number of fields
	if ( count($HeadRow) != count($FieldHeadings)) {
		prnMsg(__('File contains '. count($HeadRow). ' columns, expected '. count($FieldHeadings). '. Try downloading a new template.'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}
	$Salesmen=array();
	$SQL = "SELECT salesmancode
				     FROM salesman";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Salesmen[]=$MyRow['salesmancode'];
	}
	$Areas=array();
	$SQL = "SELECT areacode
				     FROM areas";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Areas[]=$MyRow['areacode'];
	}
	$Locations=array();
	$SQL = "SELECT loccode
				     FROM locations";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Locations[]=$MyRow['loccode'];
	}
	$Shippers=array();
	$SQL = "SELECT shipper_id
				     FROM shippers";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Shippers[]=$MyRow['shipper_id'];
	}
	$Taxgroups=array();
	$SQL = "SELECT taxgroupid
				     FROM taxgroups";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Taxgroups[]=$MyRow['taxgroupid'];
	}

	//test header row field name and sequence
	$Head = 0;
	foreach ($HeadRow as $HeadField) {
		if ( mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$Head])) {
			prnMsg(__('File contains incorrect headers ('. mb_strtoupper($HeadField). ' != '. mb_strtoupper($Header[$Head]). '. Try downloading a new template.'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}
		$Head++;
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$Row = 1;
	$UpdatedNum=0;
	$InsertNum=0;
	$ExistDebtorNos=array();
	$NotExistDebtorNos=array();
	$ExistedBranches = array();
	while ( ($Filerow = fgetcsv($FileHandle, 10000, ",")) !== false ) {

		//check for correct number of fields
		$FieldCount = count($Filerow);
		if ($FieldCount != $FieldTarget) {
			prnMsg(__($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		foreach ($Filerow as &$Value) {
			$Value = trim($Value);
		}
		$_POST['BranchCode']=$Filerow[0];
		$_POST['DebtorNo']=$Filerow[1];
		$_POST['BrName']=$Filerow[2];
		$_POST['BrAddress1']=$Filerow[3];
		$_POST['BrAddress2']=$Filerow[4];
		$_POST['BrAddress3']=$Filerow[5];
		$_POST['BrAddress4']=$Filerow[6];
		$_POST['BrAddress5']=$Filerow[7];
		$_POST['BrAddress6']=$Filerow[8];
		$Latitude=$Filerow[9];
		$Longitude=$Filerow[10];
		$_POST['SpecialInstructions']=$Filerow[29];
		$_POST['EstDeliveryDays']=$Filerow[11];
		$_POST['FwdDate']=$Filerow[14];
		$_POST['Salesman']=$Filerow[13];
		$_POST['PhoneNo']=$Filerow[15];
		$_POST['FaxNo']=$Filerow[16];
		$_POST['ContactName']=$Filerow[17];
		$_POST['Area']=$Filerow[12];
		$_POST['Email']=$Filerow[18];
		$_POST['TaxGroup']=$Filerow[20];
		$_POST['DefaultLocation']=$Filerow[19];
		$_POST['BrPostAddr1']=$Filerow[24];
		$_POST['BrPostAddr2']=$Filerow[25];
		$_POST['BrPostAddr3']=$Filerow[26];
		$_POST['BrPostAddr4']=$Filerow[27];
		$_POST['BrPostAddr5']=$Filerow[28];
		$_POST['DisableTrans']=$Filerow[23];
		$_POST['DefaultShipVia']=$Filerow[21];
		$_POST['CustBranchCode']=$Filerow[30];
		$_POST['DeliverBlind']=$Filerow[22];

		$i=0;

		if (ContainsIllegalCharacters($_POST['BranchCode']) OR mb_strstr($_POST['BranchCode'],' ') OR mb_strstr($_POST['BranchCode'],'-')) {
			$InputError = 1;
			prnMsg(__('The Branch code cannot contain any of the following characters')." -  &amp; \' &lt; &gt;",'error');
			$Errors[$i] = 'BranchCode';
			$i++;
		}
		if (ContainsIllegalCharacters($_POST['DebtorNo'])) {
			$InputError = 1;
			prnMsg(__('The Debtor No cannot contain any of the following characters')." - &amp; \' &lt; &gt;",'error');
			$Errors[$i] = 'Debtor No';
			$i++;
		}
		if (mb_strlen($_POST['BranchCode'])==0 OR mb_strlen($_POST['BranchCode'])>10) {
			$InputError = 1;
			prnMsg(__('The Branch code must be at least one character long and cannot be more than 10 characters'),'error');
			$Errors[$i] = 'BranchCode';
			$i++;
		}
		for ($c=1;$c<7;$c++) { //Branch address validataion
			$Lenth = 40;
			if($c == 4) {
				$Lenth = 50;
			}
			if($c == 5) {
				$Lenth = 20;
			}
			if (isset($_POST['BrAddress'.$c]) AND mb_strlen($_POST['BrAddress'.$c])>$Lenth) {
				$InputError = 1;
				prnMsg(__('The Branch address1 must be no more than') . ' ' . $Lenth . ' '. __('characters'),'error');
				$Errors[$i] = 'BrAddress'.$c;
				$i++;
		} 		}
		if($Latitude !== null AND !is_numeric($Latitude)) {
			$InputError = 1;
			prnMsg(__('The latitude is expected to be a numeric'),'error');
			$Errors[$i] = 'Latitude';
			$i++;
		}
		if($Longitude !== null AND !is_numeric($Longitude)) {
			$InputError = 1;
			prnMsg(__('The longitude is expected to be a numeric'),'error');
		       	$Errors[$i] = 'Longitued';
			$i++;
		}
		if (!is_numeric($_POST['FwdDate'])) {
			$InputError = 1;
			prnMsg(__('The date after which invoices are charged to the following month is expected to be a number and a recognised number has not been entered'),'error');
			$Errors[$i] = 'FwdDate';
			$i++;
		}
		if ($_POST['FwdDate'] >30) {
			$InputError = 1;
			prnMsg(__('The date (in the month) after which invoices are charged to the following month should be a number less than 31'),'error');
			$Errors[$i] = 'FwdDate';
			$i++;
		}
		if (!is_numeric(filter_number_format($_POST['EstDeliveryDays']))) {
			$InputError = 1;
			prnMsg(__('The estimated delivery days is expected to be a number and a recognised number has not been entered'),'error');
			$Errors[$i] = 'EstDeliveryDays';
			$i++;
		}
		if (filter_number_format($_POST['EstDeliveryDays']) >60) {
			$InputError = 1;
			prnMsg(__('The estimated delivery days should be a number of days less than 60') . '. ' . __('A package can be delivered by seafreight anywhere in the world normally in less than 60 days'),'error');
			$Errors[$i] = 'EstDeliveryDays';
			$i++;
		}
		if(empty($_POST['Salesman']) OR !in_array($_POST['Salesman'],$Salesmen,true)) {
			$InputError = 1;
			prnMsg(__('The salesman not empty and must exist.'),'error');
			$Errors[$i] = 'Salesman';
			$i++;
		}
		if($_POST['PhoneNo'] !== null AND preg_match('/[^\d+()\s-]/',$_POST['PhoneNo'])) {
			$InputError = 1;
			prnMsg(__('The phone no should not contains characters other than digital,parenthese,space,minus and plus sign'),'error');
			$Errors[$i] = 'Phone No';
			$i++;
		}
		if($_POST['FaxNo'] !== null AND preg_match('/[^\d+()\s-]/',$_POST['FaxNo'])) {
			$InputError = 1;
			prnMsg(__('The fax no should not contains characters other than digital,parenthese,space,minus and plus sign'),'error');
			$Errors[$i] = 'FaxNo';
			$i++;
		}
		if($_POST['ContactName'] !== null AND mb_strlen($_POST['ContactName']) > 30) {
			$InputError = 1;
			prnMsg(__('The contact name must not be over 30 characters'),'error');
			$Errors[$i] = 'ContactName';
			$i++;
		}
		if($_POST['Email'] !== null AND !filter_var($_POST['Email'],FILTER_VALIDATE_EMAIL)) {
			$InputError = 1;
			prnMsg(__('The email address is not valid'),'error');
			$Errors[$i] = 'Email';
			$i++;
		}

		if(ContainsIllegalCharacters($_POST['BrName']) OR mb_strlen($_POST['BrName']) >40) {
			$InputError = 1;
			prnMsg(__('The Branch code cannot contain any of the following characters')." -  &amp; \' &lt; &gt;" .' ' . __('Or length is over 40'),'error');
			$Errors[$i] = 'BrName';
			$i++;
		}
		if(empty($_POST['Area']) OR !in_array($_POST['Area'],$Areas,true)) {
			$InputError = 1;
			prnMsg(__('The sales area not empty and must exist.'),'error');
			$Errors[$i] = 'Area';
			$i++;
		}
		if(empty($_POST['DefaultLocation']) OR !in_array($_POST['DefaultLocation'],$Locations,true)) {
			$InputError = 1;
			prnMsg(__('The default location not empty and must exist.'),'error');
			$Errors[$i] = 'DefaultLocation';
			$i++;
		}
		if(empty($_POST['DefaultShipVia']) OR !in_array($_POST['DefaultShipVia'],$Shippers,true)) {
			$InputError = 1;
			prnMsg(__('The default shipper not empty and must exist.'),'error');
			$Errors[$i] = 'DefaultShipVia';
			$i++;
		}
		if(empty($_POST['TaxGroup']) OR !in_array($_POST['TaxGroup'],$Taxgroups,true)) {
			$InputError = 1;
			prnMsg(__('The taxgroup not empty and must exist.'),'error');
			$Errors[$i] = 'TaxGroup';
			$i++;
		}
		if(!isset($_POST['DeliverBlind']) OR ($_POST['DeliverBlind'] !=1 AND $_POST['DeliverBlind'] != 2)) {
			$InputError = 1;
			prnMsg(__('The Deliver Blind must be set as 2 or 1'),'error');
			$Errors[$i] = 'DeliverBlind';
			$i++;
		}
		if(!isset($_POST['DisableTrans']) OR ($_POST['DisableTrans'] != 0 AND $_POST['DisableTrans'] != 1)) {
			$InputError = 1;
			prnMsg(__('The Disable Trans status should be 0 or 1'),'error');
			$Errors[$i] = 'DisableTrans';
			$i++;
		}
		for($c=1;$c<6;$c++) {
			$Lenth = 40;
			if($c == 4) {
				$Lenth = 50;
			}
			if($c == 5) {
				$Lenth = 20;
			}
			if (isset($_POST['BrPostAddr'.$c]) AND mb_strlen($_POST['BrPostAddr'.$c])>$Lenth) {
				$InputError = 1;
				prnMsg(__('The Branch Post Address') . ' ' . $c . ' ' . __('must be no more than') . ' ' . $Lenth . ' '. __('characters'),'error');
				$Errors[$i] = 'BrPostAddr'.$c;
				$i++;
			}

		}
		if(isset($_POST['CustBranchCode']) AND mb_strlen($_POST['CustBranchCode']) > 30) {
			$InputError = 1;
			prnMsg(__('The Cust branch code for EDI must be less than 30 characters'),'error');
			$Errors[$i] = 'CustBranchCode';
			$i++;
		}

		if ($InputError !=1) {
			if (DB_error_no() ==0) {

				if(in_array($_POST['DebtorNo'],$NotExistDebtorNos,true)) {
					continue;
				}else{
					$SQL = "SELECT 1
						 FROM debtorsmaster
						 WHERE debtorno='".$_POST['DebtorNo']."' LIMIT 1";
					$Result = DB_query($SQL);
					$DebtorExists=(DB_num_rows($Result)>0);
					if ($DebtorExists) {
						$ExistDebtorNos[]=$_POST['DebtorNo'];
					}else{
						$NotExistDebtorNos[]=$_POST['DebtorNo'];
						prnMsg(__('The Debtor No') . $_POST['DebtorNo'] . ' ' . __('has not existed, and its branches data cannot be imported'),'error');
						include('includes/footer.php');
						exit();
					}
				}
				$SQL = "SELECT 1
				     FROM custbranch
           			 WHERE debtorno='".$_POST['DebtorNo']."' AND
				           branchcode='".$_POST['BranchCode']."' LIMIT 1";
				$Result = DB_query($SQL);
				$BranchExists=(DB_num_rows($Result)>0);
				if ($BranchExists AND $_POST['UpdateIfExists']!=1) {
					$ExistedBranches[] = array('debtor'=>$_POST['DebtorNo'],
								'branch'=>$_POST['BranchCode']);
					$UpdatedNum++;
				}else{

					if (!isset($_POST['EstDeliveryDays'])) {
						$_POST['EstDeliveryDays']=1;
					}
					if (!isset($Latitude)) {
						$Latitude=0.0;
						$Longitude=0.0;
					}
					if ($BranchExists) {
						$UpdatedNum++;
						$SQL = "UPDATE custbranch SET brname = '" . $_POST['BrName'] . "',
									braddress1 = '" . $_POST['BrAddress1'] . "',
									braddress2 = '" . $_POST['BrAddress2'] . "',
									braddress3 = '" . $_POST['BrAddress3'] . "',
									braddress4 = '" . $_POST['BrAddress4'] . "',
									braddress5 = '" . $_POST['BrAddress5'] . "',
									braddress6 = '" . $_POST['BrAddress6'] . "',
									lat = '" . $Latitude . "',
									lng = '" . $Longitude . "',
									specialinstructions = '" . $_POST['SpecialInstructions'] . "',
									phoneno='" . $_POST['PhoneNo'] . "',
									faxno='" . $_POST['FaxNo'] . "',
									fwddate= '" . $_POST['FwdDate'] . "',
									contactname='" . $_POST['ContactName'] . "',
									salesman= '" . $_POST['Salesman'] . "',
									area='" . $_POST['Area'] . "',
									estdeliverydays ='" . filter_number_format($_POST['EstDeliveryDays']) . "',
									email='" . $_POST['Email'] . "',
									taxgroupid='" . $_POST['TaxGroup'] . "',
									defaultlocation='" . $_POST['DefaultLocation'] . "',
									brpostaddr1 = '" . $_POST['BrPostAddr1'] . "',
									brpostaddr2 = '" . $_POST['BrPostAddr2'] . "',
									brpostaddr3 = '" . $_POST['BrPostAddr3'] . "',
									brpostaddr4 = '" . $_POST['BrPostAddr4'] . "',
									brpostaddr5 = '" . $_POST['BrPostAddr5'] . "',
									disabletrans='" . $_POST['DisableTrans'] . "',
									defaultshipvia='" . $_POST['DefaultShipVia'] . "',
									custbranchcode='" . $_POST['CustBranchCode'] ."',
									deliverblind='" . $_POST['DeliverBlind'] . "'
								WHERE branchcode = '".$_POST['BranchCode']."' AND debtorno='".$_POST['DebtorNo']."'";

					} else {
						$InsertNum++;
						$SQL = "INSERT INTO custbranch (branchcode,
										debtorno,
										brname,
										braddress1,
										braddress2,
										braddress3,
										braddress4,
										braddress5,
										braddress6,
										lat,
										lng,
										specialinstructions,
										estdeliverydays,
										fwddate,
										salesman,
										phoneno,
										faxno,
										contactname,
										area,
										email,
										taxgroupid,
										defaultlocation,
										brpostaddr1,
										brpostaddr2,
										brpostaddr3,
										brpostaddr4,
										brpostaddr5,
										disabletrans,
										defaultshipvia,
										custbranchcode,
										deliverblind)
								VALUES ('" . $_POST['BranchCode'] . "',
									'" . $_POST['DebtorNo'] . "',
									'" . $_POST['BrName'] . "',
									'" . $_POST['BrAddress1'] . "',
									'" . $_POST['BrAddress2'] . "',
									'" . $_POST['BrAddress3'] . "',
									'" . $_POST['BrAddress4'] . "',
									'" . $_POST['BrAddress5'] . "',
									'" . $_POST['BrAddress6'] . "',
									'" . $Latitude . "',
									'" . $Longitude . "',
									'" . $_POST['SpecialInstructions'] . "',
									'" . filter_number_format($_POST['EstDeliveryDays']) . "',
									'" . $_POST['FwdDate'] . "',
									'" . $_POST['Salesman'] . "',
									'" . $_POST['PhoneNo'] . "',
									'" . $_POST['FaxNo'] . "',
									'" . $_POST['ContactName'] . "',
									'" . $_POST['Area'] . "',
									'" . $_POST['Email'] . "',
									'" . $_POST['TaxGroup'] . "',
									'" . $_POST['DefaultLocation'] . "',
									'" . $_POST['BrPostAddr1'] . "',
									'" . $_POST['BrPostAddr2'] . "',
									'" . $_POST['BrPostAddr3'] . "',
									'" . $_POST['BrPostAddr4'] . "',
									'" . $_POST['BrPostAddr5'] . "',
									'" . $_POST['DisableTrans'] . "',
									'" . $_POST['DefaultShipVia'] . "',
									'" . $_POST['CustBranchCode'] ."',
									'" . $_POST['DeliverBlind'] . "')";
					}

					//run the SQL from either of the above possibilites
					$ErrMsg = __('The branch record could not be inserted or updated because');
					$Result = DB_query($SQL, $ErrMsg);

					if (DB_error_no() ==0) {
						prnMsg( __('New branch of debtor') .' ' .$_POST['DebtorNo'] . ' ' .__('with branch code') .' ' . $_POST['BranchCode'] . ' ' . $_POST['BrName']  . ' '. __('has been passed validation'),'info');
					} else { //location insert failed so set some useful error info
						$InputError = 1;
						prnMsg(__($Result),'error');
					}
				}
			} else { //item insert failed so set some useful error info
				$InputError = 1;
				prnMsg(__($Result),'error');
			}

		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}

		$Row++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on row '. $Row. '. Batch import has been rolled back.'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		if($_POST['UpdateIfExists']==1) {
			prnMsg( __('Updated brances total:') .' ' . $UpdatedNum .' '.__('Insert branches total:'). $InsertNum,'success' );
		}else{
			prnMsg( __('Exist branches total:') .' ' . $UpdatedNum .' '.__('Inserted branches total:'). $InsertNum,'info');
			if($UpdatedNum){
				echo '	<p>' . __('Branches not updated').'</p>
					<table class="selection">
					<tr><th>'.__('Debtor No').'</th><th>' . __('Branch Code').'</th></tr>';
				foreach($ExistedBranches as $key=>$Value){
					echo '<tr><td>'.$Value['debtor'].'</td><td>'.$Value['branch'].'</td></tr>';
				}
				echo '</table>';
			}
		}
	}

	fclose($FileHandle);

} elseif ( isset($_POST['gettemplate']) OR isset($_GET['gettemplate'])) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$FieldHeadings). '"<br /><br /><br />';

} else { //show file upload form

	prnMsg(__('Please ensure that your csv file is encoded in UTF-8, otherwise the input data will not store correctly in database'),'warn');

	echo '
		<br />
		<a href="Z_ImportCustbranch.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			__('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . __('Send File') . '" />';
	echo '<br/>',__('Update if Customer Branch exists'),':<input type="checkbox" name="UpdateIfExists">';
	echo'</div>
		</form>';

}


include('includes/footer.php');
