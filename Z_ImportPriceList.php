<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Import Sales Price List');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

if (isset($_POST['StartDate'])){$_POST['StartDate'] = ConvertSQLDate($_POST['StartDate']);}

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Import Price List from CSV file') . '" />' . ' ' .
		__('Import Price List from CSV file') . '</p>';

$FieldHeadings = array(
	'StockID',			//  0 'STOCKID',
	'SalesType',		//  1 'Price list id',
	'CurrencyCode',		//  2 'Currency Code',
	'Price'				//  3 'Price'
);

if (isset($_FILES['PriceListFile']) and $_FILES['PriceListFile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['PriceListFile']['name'];
	$TempName = $_FILES['PriceListFile']['tmp_name'];
	$FileSize = $_FILES['PriceListFile']['size'];
	$FieldTarget = 4;
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
		prnMsg(__('File contains') . ' '. count($HeadRow). ' ' . __('columns, expected') . ' '. count($FieldHeadings). '. ' . __('Download the template to see the expected columns.'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$HeadingColumnNumber = 0;
	foreach ($HeadRow as $HeadField) {
		if ( trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$HeadingColumnNumber]))) {
			prnMsg(__('The file to import the price list from contains incorrect column headings') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$HeadingColumnNumber]). '<br />' . __('The column headings must be') . ' StockID, SalesType, CurrencyCode, Price','error');
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
		if ($FieldCount != $FieldTarget){
			prnMsg($FieldTarget . ' ' . __('fields required') . ', '. $FieldCount. ' ' . __('fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		$StockID = mb_strtoupper($MyRow[0]);
		foreach ($MyRow as &$Value) {
			$Value = trim($Value);
			$Value = str_replace('"', '', $Value);
		}

		//first off check that the item actually exist
		$SQL = "SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $StockID . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(__('Stock item') . ' "'. $MyRow[0]. '" ' . __('does not exist'),'error');
		}
		//Then check that the price list actually exists
		$SQL = "SELECT COUNT(typeabbrev) FROM salestypes WHERE typeabbrev='" . $MyRow[1] . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(__('SalesType/Price List') . ' "' . $MyRow[1]. '" ' . __('does not exist'),'error');
		}

		//Then check that the currency code actually exists
		$SQL = "SELECT COUNT(currabrev) FROM currencies WHERE currabrev='" . $MyRow[2] . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(__('Currency') . ' "' . $MyRow[2] . '" ' . __('does not exist'),'error');
		}

		//Finally force the price to be a double
		$MyRow[3] = (float)$MyRow[3];
		if ($InputError !=1){

			//Firstly close any open prices for this item
			$SQL = "UPDATE prices
						SET enddate='" . FormatDateForSQL($_POST['StartDate']) . "'
						WHERE stockid='" . $StockID . "'
						AND enddate>CURRENT_DATE
						AND typeabbrev='" . $MyRow[1] . "'";
			$Result = DB_query($SQL);

			//Insert the price
			$SQL = "INSERT INTO prices (stockid,
										typeabbrev,
										currabrev,
										price,
										startdate
									) VALUES (
										'" . $MyRow[0] . "',
										'" . $MyRow[1] . "',
										'" . $MyRow[2] . "',
										'" . $MyRow[3] . "',
										'" . FormatDateForSQL($_POST['StartDate']) . "')";

			$ErrMsg =  __('The price could not be added because');
			$Result = DB_query($SQL, $ErrMsg);
		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}
		$LineNumber++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(__('Failed on row '. $LineNumber. '. Batch import has been rolled back.'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( __('Batch Import of') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database.'),'success');
	}

	fclose($FileHandle);

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' .
			__('This function loads a new sales price list from a comma separated variable (csv) file.') . '<br />' .
			__('The file must contain four columns, and the first row should be the following headers:') . '<br />StockID, SalesType, CurrencyCode, Price<br />' .
			__('followed by rows containing these four fields for each price to be uploaded.') .  '<br />' .
			__('The StockID, SalesType, and CurrencyCode fields must have a corresponding entry in the stockmaster, salestypes, and currencies tables.') . '</div>';

	echo '<fieldset>
			<legend>', __('Import Criteria'), '</legend>
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
			<field>
				<label>', __('Prices effective from') . ':</label>
				<input name="StartDate" maxlength="10" size="11" type="date" value="' . date('Y-m-d') . '" />
			</field>
			<field>
				<label>', __('Upload file') . ':</label>
				<input name="PriceListFile" type="file" />
			</field>
			</fieldset>
			<div class="centre">
			<input type="submit" name="submit" value="' . __('Send File') . '" />
		</div>
		</form>';

}

include('includes/footer.php');
