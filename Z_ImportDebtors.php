<?php
/* Import debtors by csv file */

include('includes/session.php');
$Title = __('Import Debtors And branches');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FormID'])) {
	if (!isset($_POST['AutoDebtorNo'])) {
		$_POST['AutoDebtorNo'] = 0;
	} else {
		$_POST['AutoDebtorNo'] = 1;
	}
	if ($_POST['AutoDebtorNo'] == 1) {
		$_POST['UpdateIfExists'] = 0;
	} else {
		if (!isset($_POST['UpdateIfExists'])) {
			$_POST['UpdateIfExists'] = 0;
		} else {
			$_POST['UpdateIfExists'] = 1;
		}
	}
} else {
	$_POST['AutoDebtorNo'] = $_SESSION['AutoDebtorNo'];
	$_POST['UpdateIfExists'] = 0;
}

// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed
$FieldHeadings = array('debtorno', //0
'name', //1
'address1', //2
'address2', //3
'address3', //4
'address4', //5
'address5', //6
'address6', //7
'currcode', //8
'salestype', //9
'clientsince', //10
'holdreason', //11
'paymentterms', //12
'discount', //13
'pymtdiscount', //14
'lastpaid', //15
'lastpaiddate', //16
'creditlimit', //17
'invaddrbranch', //18
'discountcode', //19
'Languageid', //20
'ediinvoices', //21
'ediorders', //22
'edireference', //23
'editransport', //24
'ediaddress', //25
'ediserveruser', //26
'ediserverpwd', //27
'taxref', //28
'customerpoline', //29
'typeid', //30
'lat', //31
'lng', //32
'estdeliverydays', //33
'area', //34
'salesman', //35
'fwddate', //36
'phoneno', //37
'faxno', //38
'contactname', //39
'email', //40
'defaultlocation', //41
'taxgroupid', //42
'defaultshipvia', //43
'deliverblind', //44
'disabletrans', //45
'brpostaddr1', //46
'brpostaddr2', //47
'brpostaddr3', //48
'brpostaddr4', //49
'brpostaddr5', //50
'brpostaddr6', //51
'specialinstructions', //52
'custbranchcode', //53
);

if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing
	//initialize
	$FieldTarget = count($FieldHeadings);
	$InputError = 0;

	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName = $_FILES['userfile']['tmp_name'];
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
	if (count($HeadRow) != count($FieldHeadings)) {
		prnMsg(__('File contains ' . count($HeadRow) . ' columns, expected ' . count($FieldHeadings) . '. Try downloading a new template.'), 'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$Head = 0;
	foreach ($HeadRow as $HeadField) {
		if (mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$Head])) {
			prnMsg(__('File contains incorrect headers (' . mb_strtoupper($HeadField) . ' != ' . mb_strtoupper($Header[$Head]) . '. Try downloading a new template.'), 'error');
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
	$UpdatedNum = 0;
	$InsertNum = 0;
	while (($Filerow = fgetcsv($FileHandle, 10000, ",")) !== false) {

		//check for correct number of fields
		$FieldCount = count($Filerow);
		if ($FieldCount != $FieldTarget) {
			prnMsg(__($FieldTarget . ' fields required, ' . $FieldCount . ' fields received'), 'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		foreach ($Filerow as & $Value) {
			$Value = trim($Value);
		}

		$_POST['DebtorNo'] = $Filerow[0];
		$_POST['CustName'] = $Filerow[1];
		$_POST['Address1'] = $Filerow[2];
		$_POST['Address2'] = $Filerow[3];
		$_POST['Address3'] = $Filerow[4];
		$_POST['Address4'] = $Filerow[5];
		$_POST['Address5'] = $Filerow[6];
		$_POST['Address6'] = $Filerow[7];
		$_POST['CurrCode'] = $Filerow[8];
		$_POST['SalesType'] = $Filerow[9];
		$_POST['ClientSince'] = $Filerow[10];
		$_POST['HoldReason'] = $Filerow[11];
		$_POST['PaymentTerms'] = $Filerow[12];
		$_POST['Discount'] = $Filerow[13];
		$_POST['PymtDiscount'] = $Filerow[14];
		$_POST['lastpaid'] = $Filerow[15];
		$_POST['lastpaiddate'] = $Filerow[16];
		$_POST['CreditLimit'] = $Filerow[17];
		$_POST['InvAddrBranch'] = $Filerow[18];
		$_POST['DiscountCode'] = $Filerow[19];
		$_POST['LanguageID'] = $Filerow[20];
		$_POST['EDIInvoices'] = $Filerow[21];
		$_POST['EDIOrders'] = $Filerow[22];
		$_POST['EDIReference'] = $Filerow[23];
		$_POST['EDITransport'] = $Filerow[24];
		$_POST['EDIAddress'] = $Filerow[25];
		$_POST['EDIServerUser'] = $Filerow[26];
		$_POST['EDIServerPwd'] = $Filerow[27];
		$_POST['TaxRef'] = $Filerow[28];
		$_POST['CustomerPOLine'] = $Filerow[29];
		$_POST['typeid'] = $Filerow[30];

		if ($_POST['AutoDebtorNo'] == 1) {
			$_POST['DebtorNo'] = GetNextTransNo(500);
		} else {
			$_POST['DebtorNo'] = mb_strtoupper($_POST['DebtorNo']);
		}

		//$_POST['DebtorNo']=$_POST['DebtorNo'];
		$_POST['BranchCode'] = $_POST['DebtorNo'];
		$_POST['BrName'] = $_POST['CustName'];
		$_POST['BrAddress1'] = $_POST['Address1'];
		$_POST['BrAddress2'] = $_POST['Address2'];
		$_POST['BrAddress3'] = $_POST['Address3'];
		$_POST['BrAddress4'] = $_POST['Address4'];
		$_POST['BrAddress5'] = $_POST['Address5'];
		$_POST['BrAddress6'] = $_POST['Address6'];
		$Latitude = $Filerow[31];
		$Longitude = $Filerow[32];
		$_POST['EstDeliveryDays'] = $Filerow[33];
		$_POST['Area'] = $Filerow[34];
		$_POST['Salesman'] = $Filerow[35];
		$_POST['FwdDate'] = $Filerow[36];
		$_POST['PhoneNo'] = $Filerow[37];
		$_POST['FaxNo'] = $Filerow[38];
		$_POST['ContactName'] = $Filerow[39];
		$_POST['Email'] = $Filerow[40];
		$_POST['DefaultLocation'] = $Filerow[41];
		$_POST['TaxGroup'] = $Filerow[42];
		$_POST['DefaultShipVia'] = $Filerow[43];
		$_POST['DeliverBlind'] = $Filerow[44];
		$_POST['DisableTrans'] = $Filerow[45];
		$_POST['BrPostAddr1'] = $Filerow[46];
		$_POST['BrPostAddr2'] = $Filerow[47];
		$_POST['BrPostAddr3'] = $Filerow[48];
		$_POST['BrPostAddr4'] = $Filerow[49];
		$_POST['BrPostAddr5'] = $Filerow[50];
		$_POST['CustBranchCode'] = $Filerow[51];
		$_POST['SpecialInstructions'] = $Filerow[52];

		$i = 0;
		if ($_POST['AutoDebtorNo'] == 0 and mb_strlen($_POST['DebtorNo']) == 0) {
			$InputError = 1;
			prnMsg(__('The debtor code cannot be empty'), 'error');
			$Errors[$i] = 'DebtorNo';
			$i++;
		} elseif ($_POST['AutoDebtorNo'] == 0 and (ContainsIllegalCharacters($_POST['DebtorNo']) or mb_strpos($_POST['DebtorNo'], ' '))) {
			$InputError = 1;
			prnMsg(__('The customer code cannot contain any of the following characters') . " . - ' &amp; + \" " . __('or a space'), 'error');
			$Errors[$i] = 'DebtorNo';
			$i++;
		}
		if (mb_strlen($_POST['CustName']) > 40 or mb_strlen($_POST['CustName']) == 0) {
			$InputError = 1;
			prnMsg(__('The customer name must be entered and be forty characters or less long'), 'error');
			$Errors[$i] = 'CustName';
			$i++;
		} elseif (mb_strlen($_POST['Address1']) > 40) {
			$InputError = 1;
			prnMsg(__('The Line 1 of the address must be forty characters or less long'), 'error');
			$Errors[$i] = 'Address1';
			$i++;
		} elseif (mb_strlen($_POST['Address2']) > 40) {
			$InputError = 1;
			prnMsg(__('The Line 2 of the address must be forty characters or less long'), 'error');
			$Errors[$i] = 'Address2';
			$i++;
		} elseif (mb_strlen($_POST['Address3']) > 40) {
			$InputError = 1;
			prnMsg(__('The Line 3 of the address must be forty characters or less long'), 'error');
			$Errors[$i] = 'Address3';
			$i++;
		} elseif (mb_strlen($_POST['Address4']) > 50) {
			$InputError = 1;
			prnMsg(__('The Line 4 of the address must be fifty characters or less long'), 'error');
			$Errors[$i] = 'Address4';
			$i++;
		} elseif (mb_strlen($_POST['Address5']) > 20) {
			$InputError = 1;
			prnMsg(__('The Line 5 of the address must be twenty characters or less long'), 'error');
			$Errors[$i] = 'Address5';
			$i++;
		} elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
			$InputError = 1;
			prnMsg(__('The credit limit must be numeric'), 'error');
			$Errors[$i] = 'CreditLimit';
			$i++;
		} elseif (!is_numeric(filter_number_format($_POST['PymtDiscount']))) {
			$InputError = 1;
			prnMsg(__('The payment discount must be numeric'), 'error');
			$Errors[$i] = 'PymtDiscount';
			$i++;
		} elseif (!Is_Date($_POST['ClientSince'])) {
			$InputError = 1;
			prnMsg(__('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
			$Errors[$i] = 'ClientSince';
			$i++;
		} elseif (!is_numeric(filter_number_format($_POST['Discount']))) {
			$InputError = 1;
			prnMsg(__('The discount percentage must be numeric'), 'error');
			$Errors[$i] = 'Discount';
			$i++;
		} elseif (filter_number_format($_POST['CreditLimit']) < 0) {
			$InputError = 1;
			prnMsg(__('The credit limit must be a positive number'), 'error');
			$Errors[$i] = 'CreditLimit';
			$i++;
		} elseif ((filter_number_format($_POST['PymtDiscount']) > 10) or (filter_number_format($_POST['PymtDiscount']) < 0)) {
			$InputError = 1;
			prnMsg(__('The payment discount is expected to be less than 10% and greater than or equal to 0'), 'error');
			$Errors[$i] = 'PymtDiscount';
			$i++;
		} elseif ((filter_number_format($_POST['Discount']) > 100) or (filter_number_format($_POST['Discount']) < 0)) {
			$InputError = 1;
			prnMsg(__('The discount is expected to be less than 100% and greater than or equal to 0'), 'error');
			$Errors[$i] = 'Discount';
			$i++;
		}

		if (ContainsIllegalCharacters($_POST['EDIReference']) or mb_strstr($_POST['EDIReference'], ' ')) {
			$InputError = 1;
			prnMsg(__('The customers EDI reference code cannot contain any of the following characters') . ' - \' &amp; + \" ' . __('or a space'), 'warn');
		}
		if (mb_strlen($_POST['EDIReference']) < 4 and ($_POST['EDIInvoices'] == 1 or $_POST['EDIOrders'] == 1)) {
			$InputError = 1;
			prnMsg(__('The customers EDI reference code must be set when EDI Invoices or EDI orders are activated'), 'warn');
			$Errors[$i] = 'EDIReference';
			$i++;
		}
		if (mb_strlen($_POST['EDIAddress']) < 4 and $_POST['EDIInvoices'] == 1) {
			$InputError = 1;
			prnMsg(__('The customers EDI email address or FTP server address must be entered if EDI Invoices are to be sent'), 'warn');
			$Errors[$i] = 'EDIAddress';
			$i++;
		}

		if ($InputError != 1) {
			$SQL = "SELECT 1 FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "' LIMIT 1";
			$Result = DB_query($SQL);
			$DebtorExists = (DB_num_rows($Result) > 0);
			if ($DebtorExists and $_POST['UpdateIfExists'] != 1) {
				$UpdatedNum++;
			} else {

				$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

				if ($DebtorExists) { //update
					$UpdatedNum++;
					$SQL = "SELECT 1
							  FROM debtortrans
							where debtorno = '" . $_POST['DebtorNo'] . "' LIMIT 1";
					$Result = DB_query($SQL);

					$Curr = false;
					if (DB_num_rows($Result) == 0) {
						$Curr = true;
					} else {
						$CurrSQL = "SELECT currcode
							FROM debtorsmaster
							where debtorno = '" . $_POST['DebtorNo'] . "'";
						$CurrResult = DB_query($CurrSQL);
						$CurrRow = DB_fetch_array($CurrResult);
						$OldCurrency = $CurrRow[0];
						if ($OldCurrency != $_POST['CurrCode']) {
							prnMsg(__('The currency code cannot be updated as there are already transactions for this customer'), 'info');
						}
					}

					$SQL = "UPDATE debtorsmaster SET
							name='" . $_POST['CustName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							address4='" . $_POST['Address4'] . "',
							address5='" . $_POST['Address5'] . "',
							address6='" . $_POST['Address6'] . "',";

					if ($Curr) $SQL.= "currcode='" . $_POST['CurrCode'] . "',";

					$SQL.= "clientsince='" . $SQL_ClientSince . "',
							holdreason='" . $_POST['HoldReason'] . "',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							discount='" . filter_number_format($_POST['Discount']) / 100 . "',
							discountcode='" . $_POST['DiscountCode'] . "',
							pymtdiscount='" . filter_number_format($_POST['PymtDiscount']) / 100 . "',
							creditlimit='" . filter_number_format($_POST['CreditLimit']) . "',
							salestype = '" . $_POST['SalesType'] . "',
							invaddrbranch='" . $_POST['InvAddrBranch'] . "',
							taxref='" . $_POST['TaxRef'] . "',
							customerpoline='" . $_POST['CustomerPOLine'] . "',
							typeid='" . $_POST['typeid'] . "',
							language_id='" . $_POST['LanguageID'] . "'
						  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";

					$ErrMsg = __('The customer could not be updated because');
					$Result = DB_query($SQL, $ErrMsg);

				} else { //insert
					$InsertNum++;
					$SQL = "INSERT INTO debtorsmaster (
							debtorno,
							name,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							currcode,
							clientsince,
							holdreason,
							paymentterms,
							discount,
							discountcode,
							pymtdiscount,
							creditlimit,
							salestype,
							invaddrbranch,
							taxref,
							customerpoline,
							typeid,
							language_id)
						VALUES ('" . $_POST['DebtorNo'] . "',
							'" . $_POST['CustName'] . "',
							'" . $_POST['Address1'] . "',
							'" . $_POST['Address2'] . "',
							'" . $_POST['Address3'] . "',
							'" . $_POST['Address4'] . "',
							'" . $_POST['Address5'] . "',
							'" . $_POST['Address6'] . "',
							'" . $_POST['CurrCode'] . "',
							'" . $SQL_ClientSince . "',
							'" . $_POST['HoldReason'] . "',
							'" . $_POST['PaymentTerms'] . "',
							'" . filter_number_format($_POST['Discount']) / 100 . "',
							'" . $_POST['DiscountCode'] . "',
							'" . filter_number_format($_POST['PymtDiscount']) / 100 . "',
							'" . filter_number_format($_POST['CreditLimit']) . "',
							'" . $_POST['SalesType'] . "',
							'" . $_POST['InvAddrBranch'] . "',
							'" . $_POST['TaxRef'] . "',
							'" . $_POST['CustomerPOLine'] . "',
							'" . $_POST['typeid'] . "',
							'" . $_POST['LanguageID'] . "')";

					$ErrMsg = __('This customer could not be added because');
					$Result = DB_query($SQL, $ErrMsg);
				}
			}

		} else {

			break;
		}

		$i = 0;

		if (ContainsIllegalCharacters($_POST['BranchCode']) or mb_strstr($_POST['BranchCode'], ' ')) {
			$InputError = 1;
			prnMsg(__('The Branch code cannot contain any of the following characters') . " -  &amp; \' &lt; &gt;", 'error');
			$Errors[$i] = 'BranchCode';
			$i++;
		}
		if (mb_strlen($_POST['BranchCode']) == 0) {
			$InputError = 1;
			prnMsg(__('The Branch code must be at least one character long'), 'error');
			$Errors[$i] = 'BranchCode';
			$i++;
		}
		if (!is_numeric($_POST['FwdDate'])) {
			$InputError = 1;
			prnMsg(__('The date after which invoices are charged to the following month is expected to be a number and a recognised number has not been entered'), 'error');
			$Errors[$i] = 'FwdDate';
			$i++;
		}
		if ($_POST['FwdDate'] > 30) {
			$InputError = 1;
			prnMsg(__('The date (in the month) after which invoices are charged to the following month should be a number less than 31'), 'error');
			$Errors[$i] = 'FwdDate';
			$i++;
		}
		if (!is_numeric(filter_number_format($_POST['EstDeliveryDays']))) {
			$InputError = 1;
			prnMsg(__('The estimated delivery days is expected to be a number and a recognised number has not been entered'), 'error');
			$Errors[$i] = 'EstDeliveryDays';
			$i++;
		}
		if (filter_number_format($_POST['EstDeliveryDays']) > 60) {
			$InputError = 1;
			prnMsg(__('The estimated delivery days should be a number of days less than 60') . '. ' . __('A package can be delivered by seafreight anywhere in the world normally in less than 60 days'), 'error');
			$Errors[$i] = 'EstDeliveryDays';
			$i++;
		}

		if ($InputError != 1) {
			if (DB_error_no() == 0) {

				$SQL = "SELECT 1
				     FROM custbranch
           			 WHERE debtorno='" . $_POST['DebtorNo'] . "' AND
				           branchcode='" . $_POST['BranchCode'] . "' LIMIT 1";
				$Result = DB_query($SQL);
				$BranchExists = (DB_num_rows($Result) > 0);
				if ($BranchExists and $_POST['UpdateIfExists'] != 1) {
					//do nothing

				} else {

					if (!isset($_POST['EstDeliveryDays'])) {
						$_POST['EstDeliveryDays'] = 1;
					}
					if (!isset($Latitude)) {
						$Latitude = 0.0;
						$Longitude = 0.0;
					}
					if ($BranchExists) {
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
									custbranchcode='" . $_POST['CustBranchCode'] . "',
									deliverblind='" . $_POST['DeliverBlind'] . "'
								WHERE branchcode = '" . $_POST['BranchCode'] . "' AND debtorno='" . $_POST['DebtorNo'] . "'";

					} else {

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
									'" . $_POST['CustBranchCode'] . "',
									'" . $_POST['DeliverBlind'] . "')";
					}

					//run the SQL from either of the above possibilites
					$ErrMsg = __('The branch record could not be inserted or updated because');
					$Result = DB_query($SQL, $ErrMsg);

				}
			} else { //item insert failed so set some useful error info
				$InputError = 1;
				prnMsg(__($Result), 'error');
			}

		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}

		$Row++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on row ' . $Row . '. Batch import has been rolled back.'), 'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg(__('Batch Import of') . ' ' . $FileName . ' ' . __('has been completed. All transactions committed to the database.'), 'success');
		if ($_POST['UpdateIfExists'] == 1) {
			prnMsg(__('Updated:') . ' ' . $UpdatedNum . ' ' . __('Insert') . ':' . $InsertNum);
		} else {
			prnMsg(__('Exist:') . ' ' . $UpdatedNum . ' ' . __('Insert') . ':' . $InsertNum);
		}
	}

	fclose($FileHandle);

} elseif (isset($_POST['gettemplate']) || isset($_GET['gettemplate'])) { //download an import template
	echo '<br /><br /><br />"' . implode('","', $FieldHeadings) . '"<br /><br /><br />';

} else { //show file upload form
	prnMsg(__('Please ensure that your csv file is encoded in UTF-8, otherwise the input data will not store correctly in database'), 'warn');

	echo '<a href="Z_ImportDebtors.php?gettemplate=1">Get Import Template</a>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' . __('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . __('Send File') . '" />';
	echo '<br/>', __('Create Debtor Codes Automatically'), ':<input type="checkbox" name="AutoDebtorNo" ';
	if ($_POST['AutoDebtorNo'] == 1) echo 'checked="checked"';
	echo '>';
	echo '<br/>', __('Update if DebtorNo exists'), ':<input type="checkbox" name="UpdateIfExists">';
	echo '</div>
		</form>';

}

include('includes/footer.php');
