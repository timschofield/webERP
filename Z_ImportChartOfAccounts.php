<?php

include('includes/session.php');
$Title = _('Import Chart Of Accounts');
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('Import Chart of Accounts from CSV file') . '" />' . ' ' .
		_('Import Chart of Accounts from CSV file') . '</p>';

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

	//check for correct number of fields
	if ( count($HeadRow) != count($FieldHeadings) ) {
		prnMsg (_('File contains') . ' '. count($HeadRow). ' ' . _('columns, expected') . ' '. count($FieldHeadings) . '<br/>' . _('There should be three column headings:') . ' Account Code, Description, Account Group','error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit;
	}

	//test header row field name and sequence
	$HeadingColumnNumber = 0;
	foreach ($HeadRow as $HeadField) {
		if ( trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$HeadingColumnNumber]))) {
			prnMsg (_('The file to import the chart of accounts from contains incorrect column headings') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$HeadingColumnNumber]). '<br />' . _('There should be three column headings:') . ' Account Code, Description, Account Group','error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit;
		}
		$HeadingColumnNumber++;
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$LineNumber = 1;
	while ( ($myrow = fgetcsv($FileHandle, 10000, ',')) !== FALSE ) {

		//check for correct number of fields
		$FieldCount = count($myrow);
		if ($FieldCount != count($FieldHeadings)){
			prnMsg (count($FieldHeadings) . ' ' . _('fields required') . ', '. $FieldCount. ' ' . _('fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		$AccountCode = mb_strtoupper($myrow[0]);
		foreach ($myrow as &$value) {
			$value = trim($value);
			$value = str_replace('"', '', $value);
		}

		//Then check that the account group actually exists
		$sql = "SELECT COUNT(groupname) FROM accountgroups WHERE groupname='" . $myrow[2] . "'";
		$result = DB_query($sql);
		$testrow = DB_fetch_row($result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg (_('Account Group') . ' "' . $myrow[2]. '" ' . _('does not exist. First enter the account groups you require in webERP before attempting to import the accounts.'),'error');
		}

		if ($InputError !=1){

			//Insert the chart record
			$sql = "INSERT INTO chartmaster (accountcode,
											accountname,
											group_
										) VALUES (
										'" . $myrow[0] . "',
										'" . $myrow[1] . "',
										'" . $myrow[2] . "')";

			$ErrMsg =  _('The general ledger account could not be added because');
			$DbgMsg = _('The SQL that was used to add the general ledger account that failed was');
			$result = DB_query($sql, $ErrMsg, $DbgMsg);
		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}
		$LineNumber++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on row') . ' '. $LineNumber. '. ' . _('Batch import of the chart of accounts has been rolled back.'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( _('Batch Import of') .' ' . $FileName  . ' '. _('has been completed') . '. ' . _('All general ledger accounts have been added to the chart of accounts'),'success');
	}

	fclose($FileHandle);
	//Now create the chartdetails records as necessary for the new chartsmaster records
	include('includes/GLPostings.inc');

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' .
			_('This function loads a chart of accounts from a comma separated variable (csv) file.') . '<br />' .
			_('The file must contain three columns, and the first row should be the following headers:') . '<br />Account Code, Description, Account Group<br />' .
			_('followed by rows containing these three fields for each general ledger account to be uploaded.') .  '<br />' .
			_('The Account Group field must have a corresponding entry in the account groups table. So these need to be set up first.') . '</div>';

	echo '<br /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			_('Upload file') . ': <input name="ChartFile" type="file" />
			<input type="submit" name="submit" value="' . _('Send File') . '" />
		</div>
		</form>';

}

include('includes/footer.php');

?>
