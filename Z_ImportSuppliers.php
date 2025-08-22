<?php
/* Import suppliers by csv file */

include('includes/session.php');
$Title = __('Import Items');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

if(isset($_POST['FormID'])) {
	if(!isset($_POST['UpdateIfExists'])) {
		$_POST['UpdateIfExists']=0;
	} else {
		$_POST['UpdateIfExists']=1;
	}
} else {
	$_POST['UpdateIfExists']=0;
}
// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed

$FieldHeadings = array(
	'SupplierID',//0
	'SuppName',//1
	'Address1',//2
	'Address2',//3
	'Address3',//4
	'Address4',//5
	'Address5',//6
	'Address6',//7
	'Phone',//8
	'Fax',//9
	'Email',//10
	'SupplierType',//11
	'CurrCode',//12
	'SupplierSince',//13
	'PaymentTerms',//14
	'BankPartics',//15
	'BankRef',//16
	'BankAct',//17
	'Remittance',//18
	'TaxGroup',//19
	'FactorID',//20
	'TaxRef',//21
	'lat',	//22
	'lng',	//23
);

if(isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing

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
	if( count($HeadRow) != count($FieldHeadings) ) {
		prnMsg(__('File contains '. count($HeadRow). ' columns, expected '. count($FieldHeadings). '. Try downloading a new template.'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$Head = 0;
	foreach($HeadRow as $HeadField) {
		if( mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$Head]) ) {
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
	while( ($Filerow = fgetcsv($FileHandle, 10000, ",")) !== false ) {
		//check for correct number of fields
		$FieldCount = count($Filerow);
		if($FieldCount != $FieldTarget) {
			prnMsg(__($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		foreach($Filerow as &$Value) {
			$Value = trim($Value);
		}

		$SupplierID=mb_strtoupper($Filerow[0]);
		$_POST['SuppName']=$Filerow[1];
		$_POST['Address1']=$Filerow[2];
		$_POST['Address2']=$Filerow[3];
		$_POST['Address3']=$Filerow[4];
		$_POST['Address4']=$Filerow[5];
		$_POST['Address5']=$Filerow[6];
		$_POST['Address6']=$Filerow[7];
		$_POST['Phone']=$Filerow[8];
		$_POST['Fax']=$Filerow[9];
		$_POST['Email']=$Filerow[10];
		$_POST['SupplierType']=$Filerow[11];
		$_POST['CurrCode']=$Filerow[12];
		$_POST['SupplierSince']=$Filerow[13];
		$_POST['PaymentTerms']=$Filerow[14];
		$_POST['BankPartics']=$Filerow[15];
		$_POST['BankRef']=$Filerow[16];
		$_POST['BankAct']=$Filerow[17];
		$_POST['Remittance']=$Filerow[18];
		$_POST['TaxGroup']=$Filerow[19];
		$_POST['FactorID']=$Filerow[20];
		$_POST['TaxRef']=$Filerow[21];
		$latitude = $Filerow[22];
		$longitude = $Filerow[23];
		//initialise no input errors assumed initially before we test
		$i=1;
		/* actions to take once the user has clicked the submit button
		ie the page has called itself with some user input */

		if(mb_strlen(trim($_POST['SuppName'])) > 40
			OR mb_strlen(trim($_POST['SuppName'])) == 0
			OR trim($_POST['SuppName']) == '') {

			$InputError = 1;
			prnMsg(__('The supplier name must be entered and be forty characters or less long'),'error');
			$Errors[$i]='Name';
			$i++;
		}
		if(mb_strlen($SupplierID) == 0) {
			$InputError = 1;
			prnMsg(__('The Supplier Code cannot be empty'),'error');
			$Errors[$i]='ID';
			$i++;
		}
		if(ContainsIllegalCharacters($SupplierID)) {
			$InputError = 1;
			prnMsg(__('The supplier code cannot contain any of the illegal characters') . ' ' . '" \' - &amp; or a space' ,'error');
			$Errors[$i]='ID';
			$i++;
		}
		if(mb_strlen($_POST['Phone']) >25) {
			$InputError = 1;
			prnMsg(__('The telephone number must be 25 characters or less long'),'error');
			$Errors[$i] = 'Telephone';
			$i++;
		}
		if(mb_strlen($_POST['Fax']) >25) {
			$InputError = 1;
			prnMsg(__('The fax number must be 25 characters or less long'),'error');
			$Errors[$i] = 'Fax';
			$i++;
		}
		if(mb_strlen($_POST['Email']) >55) {
			$InputError = 1;
			prnMsg(__('The email address must be 55 characters or less long'),'error');
			$Errors[$i] = 'Email';
			$i++;
		}
		if(mb_strlen($_POST['Email'])>0 AND !IsEmailAddress($_POST['Email'])) {
			$InputError = 1;
			prnMsg(__('The email address is not correctly formed'),'error');
			$Errors[$i] = 'Email';
			$i++;
		}
		if(mb_strlen($_POST['BankRef']) > 12) {
			$InputError = 1;
			prnMsg(__('The bank reference text must be less than 12 characters long'),'error');
			$Errors[$i]='BankRef';
			$i++;
		}
		if(!Is_Date($_POST['SupplierSince'])) {
			$InputError = 1;
			prnMsg(__('The supplier since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
			$Errors[$i]='SupplierSince';
			$i++;
		}

		if($InputError != 1) {

			$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);

			//first off validate inputs sensible
			$SQL="SELECT COUNT(supplierid) FROM suppliers WHERE supplierid='".$SupplierID."'";
			$Result = DB_query($SQL);
			$MyRow=DB_fetch_row($Result);

			$SuppExists = ($MyRow[0]>0);

			if($SuppExists AND $_POST['UpdateIfExists']!=1) {
				$UpdatedNum++;
			}elseif($SuppExists) {
				$UpdatedNum++;
				$SuppTransSQL = "SELECT supplierno
								FROM supptrans
								WHERE supplierno='".$SupplierID ."'";
				$SuppResult = DB_query($SuppTransSQL);
				$SuppTrans = DB_num_rows($SuppResult);

				$SuppCurrsSQL = "SELECT currcode
								FROM suppliers
								WHERE supplierid='".$SupplierID ."'";
				$Currresult = DB_query($SuppCurrsSQL);
				$SuppCurrs = DB_fetch_row($Currresult);

				$SQL = "UPDATE suppliers SET suppname='" . $_POST['SuppName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							address4='" . $_POST['Address4'] . "',
							address5='" . $_POST['Address5'] . "',
							address6='" . $_POST['Address6'] . "',
							telephone='". $_POST['Phone'] ."',
							fax = '". $_POST['Fax']."',
							email = '" . $_POST['Email'] . "',
							supptype = '".$_POST['SupplierType']."',";
				if($SuppTrans == 0)$SQL.="currcode='" . $_POST['CurrCode'] . "',";
							$SQL.="suppliersince='".$SQL_SupplierSince . "',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							bankpartics='" . $_POST['BankPartics'] . "',
							bankref='" . $_POST['BankRef'] . "',
							bankact='" . $_POST['BankAct'] . "',
							remittance='" . $_POST['Remittance'] . "',
							taxgroupid='" . $_POST['TaxGroup'] . "',
							factorcompanyid='" . $_POST['FactorID'] ."',
							lat='" . $latitude ."',
							lng='" . $longitude ."',
							taxref='". $_POST['TaxRef'] ."'
						WHERE supplierid = '".$SupplierID."'";

				if($SuppCurrs[0] != $_POST['CurrCode']) {
					prnMsg( __('Cannot change currency code as transactions already exist'), 'info');
				}

				$ErrMsg = __('The supplier could not be updated because');
				$Result = DB_query($SQL, $ErrMsg);

			} else { //its a new supplier
				$InsertNum++;
				$SQL = "INSERT INTO suppliers (supplierid,
											suppname,
											address1,
											address2,
											address3,
											address4,
											address5,
											address6,
											telephone,
											fax,
											email,
											supptype,
											currcode,
											suppliersince,
											paymentterms,
											bankpartics,
											bankref,
											bankact,
											remittance,
											taxgroupid,
											factorcompanyid,
											lat,
											lng,
											taxref)
									 VALUES ('" . $SupplierID . "',
										'" . $_POST['SuppName'] . "',
										'" . $_POST['Address1'] . "',
										'" . $_POST['Address2'] . "',
										'" . $_POST['Address3'] . "',
										'" . $_POST['Address4'] . "',
										'" . $_POST['Address5'] . "',
										'" . $_POST['Address6'] . "',
										'" . $_POST['Phone'] . "',
										'" . $_POST['Fax'] . "',
										'" . $_POST['Email'] . "',
										'".$_POST['SupplierType']."',
										'" . $_POST['CurrCode'] . "',
										'" . $SQL_SupplierSince . "',
										'" . $_POST['PaymentTerms'] . "',
										'" . $_POST['BankPartics'] . "',
										'" . $_POST['BankRef'] . "',
										'" . $_POST['BankAct'] . "',
										'" . $_POST['Remittance'] . "',
										'" . $_POST['TaxGroup'] . "',
										'" . $_POST['FactorID'] . "',
										'" . $latitude ."',
										'" . $longitude ."',
										'" . $_POST['TaxRef'] . "')";

				$ErrMsg = __('The supplier') . ' ' . $_POST['SuppName'] . ' ' . __('could not be added because');

				$Result = DB_query($SQL, $ErrMsg);

			}
			if(DB_error_no() ==0) {

			} else { //location insert failed so set some useful error info
				$InputError = 1;
			}
		} else { //item insert failed so set some useful error info
			$InputError = 1;
		}
		if($InputError == 1) { //this row failed so exit loop
			break;
		}

		$Row++;

	}

	if($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on row '. $Row. '. Batch import has been rolled back.'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( __('Batch Import of') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database.'),'success');
		if($_POST['UpdateIfExists']==1) {
			prnMsg( __('Updated:') .' ' . $UpdatedNum .' '. __('Insert') . ':' . $InsertNum );
		} else {
			prnMsg( __('Exist:') .' ' . $UpdatedNum .' '. __('Insert') . ':' . $InsertNum );
		}

	}

	fclose($FileHandle);

} elseif( isset($_POST['gettemplate']) || isset($_GET['gettemplate']) ) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$FieldHeadings). '"<br /><br /><br />';

} else { //show file upload form

	prnMsg(__('Please ensure that your csv file charset is UTF-8, otherwise the data will not store correctly in database'),'warn');

	echo '
		<br />
		<a href="Z_ImportSuppliers.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			__('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . __('Send File') . '" />';

	echo '<br/>',__('Update if SupplierNo exists'),':<input type="checkbox" name="UpdateIfExists">';
    echo '</div>
		</form>';

}


include('includes/footer.php');
