<?php

include('includes/session.php');
$Title = __('Import Chart Of Accounts');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Import Chart of Accounts from CSV file') . '" />' . ' ' .
		__('Import Chart of Accounts from CSV file') . '</p>';

$FieldHeadings = array(
	'Account Code',			//  0 'Account Code
	'Description',		//  1 'Account Description',
	'Account Group'		//  2 'Account Group',
);

if (isset($_FILES['ChartFile']) and $_FILES['ChartFile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['ChartFile']['name'];
	$TempName  = $_FILES['ChartFile']['tmp_name'];
	$FileSize = $_FILES['ChartFile']['size'];

	$InputError = 0;

	//get file handle
	$FileHandle = fopen($TempName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ',');
	// Remove UTF-8 BOM if present
	if (substr($HeadRow[0], 0, 3) === "\xef\xbb\xbf") {
		$HeadRow[0] = substr($HeadRow[0], 3);
	}

	//check for correct number of fields
	if ( count($HeadRow) != count($FieldHeadings) ) {
		prnMsg(__('File contains') . ' '. count($HeadRow). ' ' . __('columns, expected') . ' '. count($FieldHeadings) . '<br/>' . __('There should be three column headings:') . ' Account Code, Description, Account Group','error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$HeadingColumnNumber = 0;
	foreach ($HeadRow as $HeadField) {
		if ( trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$HeadingColumnNumber]))) {
			prnMsg(__('The file to import the chart of accounts from contains incorrect column headings') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$HeadingColumnNumber]). '<br />' . __('There should be three column headings:') . ' Account Code, Description, Account Group','error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}
		$HeadingColumnNumber++;
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$LineNumber = 1;
	while ( ($MyRow = fgetcsv($FileHandle, 10000, ',')) !== false ) {

		//check for correct number of fields
		$FieldCount = count($MyRow);
		if ($FieldCount != count($FieldHeadings)){
			prnMsg(count($FieldHeadings) . ' ' . __('fields required') . ', '. $FieldCount. ' ' . __('fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		$AccountCode = mb_strtoupper($MyRow[0]);
		foreach ($MyRow as &$Value) {
			$Value = trim($Value);
			$Value = str_replace('"', '', $Value);
		}

		//Then check that the account group actually exists
		$SQL = "SELECT COUNT(groupname) FROM accountgroups WHERE groupname='" . $MyRow[2] . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(__('Account Group') . ' "' . $MyRow[2]. '" ' . __('does not exist. First enter the account groups you require in webERP before attempting to import the accounts.'),'error');
		}

		if ($InputError !=1){

			//Insert the chart record
			$SQL = "INSERT INTO chartmaster (accountcode,
											accountname,
											group_
										) VALUES (
										'" . $MyRow[0] . "',
										'" . $MyRow[1] . "',
										'" . $MyRow[2] . "')";

			$ErrMsg =  __('The general ledger account could not be added because');
			$Result = DB_query($SQL, $ErrMsg);
		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}
		$LineNumber++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on row') . ' '. $LineNumber. '. ' . __('Batch import of the chart of accounts has been rolled back.'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( __('Batch Import of') .' ' . $FileName  . ' '. __('has been completed') . '. ' . __('All general ledger accounts have been added to the chart of accounts'),'success');
	}

	fclose($FileHandle);
	//Now create the chartdetails records as necessary for the new chartsmaster records
	include('includes/GLPostings.php');

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' .
			__('This function loads a chart of accounts from a comma separated variable (csv) file.') . '<br />' .
			__('The file must contain three columns, and the first row should be the following headers:') . '<br />Account Code, Description, Account Group<br />' .
			__('followed by rows containing these three fields for each general ledger account to be uploaded.') .  '<br />' .
			__('The Account Group field must have a corresponding entry in the account groups table. So these need to be set up first.') . '</div>';

	echo '<br /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			__('Upload file') . ': <input name="ChartFile" type="file" />
			<input type="submit" name="submit" value="' . __('Send File') . '" />
		</div>
		</form>';

}

include('includes/footer.php');
