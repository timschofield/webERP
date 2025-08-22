<?php

include('includes/session.php');
$Title = __('Import General Ledger Transactions');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Import GL Payments Receipts Or Journals From CSV') . '" />' . ' ' .
		__('Import GL Payments Receipts Or Journals From CSV') . '</p>';

$FieldHeadings = array(
	'Date',			//  0 'Transaction Date',
	'Account',		//  1 'GL Account Code,
	'ChequeNo',		//  2 'Cheque/Voucher Number',
	'Amount',		//  3 'Amount',
	'Narrative',	//  4 'Narrative'
	'Tag'			//  5 'Tag reference'
);


if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName  = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];
	$FieldTarget = 6;
	$InputError = 0;

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
		prnMsg(__('File contains') . ' '. count($HeadRow) . ' ' . __('columns, expected') . ' ' . count($FieldHeadings) . '. ' . __('Try downloading a new template'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$i = 0;
	foreach ($HeadRow as $HeadField) {
		if ( trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$i]))) {
			prnMsg(__('File contains incorrect headers') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$i]). '. ' . __('Try downloading a new template'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}
		$i++;
	}

	//Get the next transaction number
	$TransNo = GetNextTransNo( $_POST['TransactionType']);

	//Get the exchange rate to use between the transaction currency and the functional currency
	$SQL = "SELECT rate FROM currencies WHERE currabrev='" . $_POST['Currency'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$ExRate = $MyRow['rate'];

	//start database transaction
	DB_Txn_Begin();

	//Total for transactions must come back to zero
	$TransactionTotal = 0;

	//loop through file rows
	$Row = 1;
	while ( ($MyRow = fgetcsv($FileHandle, 10000, ',')) !== false ) {

		//check for correct number of fields
		$FieldCount = count($MyRow);
		if ($FieldCount != $FieldTarget){
			prnMsg(__($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		foreach ($MyRow as &$Value) {
			$Value = trim($Value);
			$Value = str_replace('"', '', $Value);
		}

		//first off check that the account code actually exists
		$SQL = "SELECT COUNT(accountcode) FROM chartmaster WHERE accountcode='" . $MyRow[1] . "'";
		$Result = DB_query($SQL);
		$TestRow = DB_fetch_row($Result);
		if ($TestRow[0] == 0) {
			$InputError = 1;
			prnMsg(__('Account code' . ' ' . $MyRow[1] . ' ' . 'does not exist'),'error');
		}

		//Then check that the date is in a correct format
		if (!Is_date($MyRow[0])) {
			$InputError = 1;
			prnMsg(__('The date') . ' ' . $MyRow[0]. ' ' . __('is not in the correct format'),'error');
		}

		//Find the period number from the date
		$Period = GetPeriod($MyRow[0]);

		//All transactions must be in the same period
		if (isset($PreviousPeriod) and $PreviousPeriod != $Period) {
			$InputError = 1;
			prnMsg(__('All transactions must be in the same period'),'error');
		}

		//Finally force the amount to be a double
		$MyRow[3] = (double)$MyRow[3];
		if ($InputError !=1){

			//Firstly add the line to the gltrans table
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										chequeno,
										trandate,
										periodno,
										account,
										narrative,
										amount
									) VALUES (
										'" . $_POST['TransactionType'] . "',
										'" . $TransNo . "',
										'" . $MyRow[2] . "',
										'" . FormatDateForSQL($MyRow[0]) . "',
										'" . $Period . "',
										'" . $MyRow[1] . "',
										'" . $MyRow[4] . "',
										'" . round($MyRow[3]/$ExRate, 2) . "'
									)";

			$Result = DB_query($SQL);

			if ($_POST['TransactionType'] != 0 AND IsBankAccount($MyRow[1])) {

				//Get the exchange rate to use between the transaction currency and the bank account currency
				$SQL = "SELECT rate
						FROM currencies
						INNER JOIN bankaccounts
							ON currencies.currabrev=bankaccounts.currcode
						WHERE bankaccounts.accountcode='" . $MyRow[1] . "'";

				$Result = DB_query($SQL);
				$MyRateRow = DB_fetch_array($Result);
				$FuncExRate = $MyRateRow['rate'];
				$SQL = "INSERT INTO banktrans (transno,
												type,
												bankact,
												ref,
												chequeno,
												exrate,
												functionalexrate,
												transdate,
												banktranstype,
												amount,
												currcode
											) VALUES (
												'" . $TransNo . "',
												'" . $_POST['TransactionType'] . "',
												'" . $MyRow[1] . "',
												'" . $MyRow[4] . "',
												'" . $MyRow[2] . "',
												'" . ($ExRate/$FuncExRate) . "',
												'" . $FuncExRate . "',
												'" . FormatDateForSQL($MyRow[0]) . "',
												'" . __('Cheque') . "',
												'" . round($MyRow[3], 2) . "',
												'" . $_POST['Currency'] . "'
											)";
				$Result = DB_query($SQL);
			}
			$PreviousPeriod = $Period;
			$TransactionTotal = $TransactionTotal + $MyRow[3];
		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}
		$Row++;

	}

	if ($InputError != 1 and round($TransactionTotal, 2) != 0) {
		$InputError = 1;
		prnMsg(__('The total of the transactions must balance back to zero'),'error');
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on row') . ' ' . $Row. '. ' . __('Batch import has been rolled back'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( __('Batch Import of') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database'),'success');
	}

	fclose($FileHandle);
	include('includes/GLPostings.php');

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' .
			__('This function loads a set of general ledger transactions from a comma separated variable (csv) file.') . '<br />' .
			__('The file must contain six columns, and the first row should be the following headers:') . '<br />' .
			$FieldHeadings[0] . ', ' . $FieldHeadings[1] . ', ' . $FieldHeadings[2] . ', ' . $FieldHeadings[3] . ', ' . $FieldHeadings[4] . ', ' . $FieldHeadings[5] . '<br />' .
			__('followed by rows containing these six fields for each price to be uploaded.') .  '<br />' .
			__('The total of the transactions must come back to zero. Debits are positive, credits are negative.') .  '<br />' .
			__('All the transactions must be within the same accounting period.') .  '<br />' .
			__('The Account field must have a corresponding entry in the chartmaster table.') . '</div>';

	echo '<fieldset>
			<legend>', __('Import Details'), '</legend>
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
	echo '<field>
			<label>', __('Select Transaction Type') . ':&nbsp;</label>
			<select name="TransactionType">
				<option value=0>' . __('GL Journal') . '</option>
				<option value=1>' . __('GL Payment') . '</option>
				<option value=2>' . __('GL Receipt') . '</option>
			</select>
		</field>';

	echo '<field>
			<label>', __('Select Currency') . ':&nbsp;</label>
			<select name="Currency">';
	$SQL = "SELECT currency, currabrev, rate FROM currencies";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		echo '</select>';
		prnMsg(__('No currencies are defined yet') . '. ' . __('Receipts cannot be entered until a currency is defined'), 'warn');

	} else {
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_SESSION['CompanyRecord']['currencydefault'] == $MyRow['currabrev']) {
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
			}
		}
		echo '</select>
			</field>';
	}
	echo '<field>
			<label>', __('Upload file') . ':</label>
			<input name="userfile" type="file" />
		</field>';
	echo '</fieldset>';
	echo '<div class="centre">';
	echo '<input type="submit" name="submit" value="' . __('Send File') . '" />
		</div>
		</form>';

}

include('includes/footer.php');

function IsBankAccount($Account) {
	$SQL ="SELECT accountcode FROM bankaccounts WHERE accountcode='" . $Account . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		return false;
	} else {
		return true;
	}
}
