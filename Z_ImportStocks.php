 <?php

include('includes/session.php');
$Title = __('Import Items');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/inventory.png" title="' .
		__('Import Stock Items from .csv') . '" />' . ' ' .
		__('Import Stock Items from .csv') . '</p>';

// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed

// The CSV file must be saved in a format like the template in the import module I.E. "RECVALUE","RECVALUE2". The CSV file needs ANSI encoding for the import to work properly.


$FieldHeadings = array(
	'StockID',         	//  0 'STOCKID',
	'Description',     	//  1 'DESCRIPTION',
	'LongDescription', 	//  2 'LONGDESCRIPTION',
	'CategoryID',      	//  3 'CATEGORYID',
	'Units',           	//  4 'UNITS',
	'MBFlag',          	//  5 'MBFLAG',
	'EOQ',             	//  6 'EOQ',
	'Discontinued',    	//  7 'DISCONTINUED',
	'Controlled',      	//  8 'CONTROLLED',
	'Serialised',      	//  9 'SERIALISED',
	'Perishable',      	// 10 'PERISHABLE',
	'Volume',          	// 11 'VOLUME',
	'grossweight',		// 12 'grossweight',
	'BarCode',         	// 13 'BARCODE',
	'DiscountCategory',	// 14 'DISCOUNTCATEGORY',
	'TaxCat',          	// 15 'TAXCAT',
	'DecimalPlaces',   	// 16 'DECIMALPLACES',
	'ItemPDF'          	// 17 'ITEMPDF'
);

if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing

	//initialize
	$FieldTarget = 18;
	$InputError = 0;

	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName  = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];

	//get file handle
	$FileHandle = fopen($TempName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ",",'"');  // Modified to handle " "" " enclosed csv - useful if you need to include commas in your text descriptions
	// Remove UTF-8 BOM if present
	if (substr($HeadRow[0], 0, 3) === "\xef\xbb\xbf") {
		$HeadRow[0] = substr($HeadRow[0], 3);
	}

	//check for correct number of fields
	if ( count($HeadRow) != count($FieldHeadings) ) {
		prnMsg(__('File contains '. count($HeadRow). ' columns, expected '. count($FieldHeadings). '. Try downloading a new template.'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$Head = 0;
	foreach ($HeadRow as $HeadField) {
		if ( mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$Head]) ) {
			prnMsg(__('File contains incorrect headers '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$Head]). '. Try downloading a new template.'),'error');  //Fixed $FieldHeadings from $Headings
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
	while ( ($MyRow = fgetcsv($FileHandle, 10000, ",")) !== false ) {

		//check for correct number of fields
		$FieldCount = count($MyRow);
		if ($FieldCount != $FieldTarget){
			prnMsg(__($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit();
		}

		// cleanup the data (csv files often import with empty strings and such)
		$StockID = mb_strtoupper($MyRow[0]);
		foreach ($MyRow as &$Value) {
			$Value = trim($Value);
		}

		//first off check if the item already exists
		$SQL = "SELECT COUNT(stockid) FROM stockmaster WHERE stockid='".$StockID."'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] != 0) {
			$InputError = 1;
			prnMsg(__('Stock item '. $StockID. ' already exists'),'error');
		}

		//next validate inputs are sensible
		if (!$MyRow[1] or mb_strlen($MyRow[1]) > 50 OR mb_strlen($MyRow[1])==0) {
			$InputError = 1;
			prnMsg(__('The stock item description must be entered and be fifty characters or less long') . '. ' . __('It cannot be a zero length string either') . ' - ' . __('a description is required'). ' ("'. implode('","',$MyRow). $stockid. '") ','error');
		}
		if (mb_strlen($MyRow[2])==0) {
			$InputError = 1;
			prnMsg(__('The stock item description cannot be a zero length string') . ' - ' . __('a long description is required'),'error');
		}
		if (mb_strlen($StockID) ==0) {
			$InputError = 1;
			prnMsg(__('The Stock Item code cannot be empty'),'error');
		}
		if (ContainsIllegalCharacters($StockID) OR mb_strstr($StockID,' ')) {
			$InputError = 1;
			prnMsg(__('The stock item code cannot contain any of the following characters') . " ' & + \" \\ " . __('or a space'). " (". $StockID. ")",'error');
			$StockID='';
		}
		if (mb_strlen($MyRow[4]) >20) {
			$InputError = 1;
			prnMsg(__('The unit of measure must be 20 characters or less long'),'error');
		}
		if (mb_strlen($MyRow[13]) >20) {
			$InputError = 1;
			prnMsg(__('The barcode must be 20 characters or less long'),'error');
		}
		if ($MyRow[10]!=0 AND $MyRow[10]!=1) {
			$InputError = 1;
			prnMsg(__('Values in the Perishable field must be either 0 (No) or 1 (Yes)') ,'error');
		}
		if (!is_numeric($MyRow[11])) {
			$InputError = 1;
			prnMsg(__('The volume of the packaged item in cubic metres must be numeric') ,'error');
		}
		if ($MyRow[11] <0) {
			$InputError = 1;
			prnMsg(__('The volume of the packaged item must be a positive number'),'error');
		}
		if (!is_numeric($MyRow[12])) {
			$InputError = 1;
			prnMsg(__('The weight of the packaged item in KGs must be numeric'),'error');
		}
		if ($MyRow[12]<0) {
			$InputError = 1;
			prnMsg(__('The weight of the packaged item must be a positive number'),'error');
		}
		if (!is_numeric($MyRow[6])) {
			$InputError = 1;
			prnMsg(__('The economic order quantity must be numeric'),'error');
		}
		if ($MyRow[6] <0) {
			$InputError = 1;
			prnMsg(__('The economic order quantity must be a positive number'),'error');
		}
		if ($MyRow[8]==0 AND $MyRow[9]==1){
			$InputError = 1;
			prnMsg(__('The item can only be serialised if there is lot control enabled already') . '. ' . __('Batch control') . ' - ' . __('with any number of items in a lot/bundle/roll is enabled when controlled is enabled') . '. ' . __('Serialised control requires that only one item is in the batch') . '. ' . __('For serialised control') . ', ' . __('both controlled and serialised must be enabled'),'error');
		}

		$mbflag = $MyRow[5];
		if ($mbflag!='M' and $mbflag!='K' and $mbflag!='A' and $mbflag!='B' and $mbflag!='D' and $mbflag!='G') {
			$InputError = 1;
			prnMsg(__('Items must be of MBFlag type Manufactured(M), Assembly(A), Kit-Set(K), Purchased(B), Dummy(D) or Phantom(G)'),'error');
		}
		if (($mbflag=='A' OR $mbflag=='K' OR $mbflag=='D' OR $mbflag=='G') AND $MyRow[8]==1){
			$InputError = 1;
			prnMsg(__('Assembly/Kitset/Phantom/Service items cannot also be controlled items') . '. ' . __('Assemblies, Dummies and Kitsets are not physical items and batch/serial control is therefore not appropriate'),'error');
		}
		if ($MyRow[3]==''){
			$InputError = 1;
			prnMsg(__('There are no inventory categories defined. All inventory items must belong to a valid inventory category,'),'error');
		}
		if ($MyRow[17]==''){
			$InputError = 1;
			prnMsg(__('ItemPDF must contain either a filename, or the keyword `none`'),'error');
		}

		if ($InputError !=1){
			if ($MyRow[9]==1){ /*Not appropriate to have several dp on serial items */
				$MyRow[16]=0;
			}

			//attempt to insert the stock item
			$SQL = "
				INSERT INTO stockmaster (
					stockid,
					description,
					longdescription,
					categoryid,
					units,
					mbflag,
					eoq,
					discontinued,
					controlled,
					serialised,
					perishable,
					volume,
					grossweight,
					barcode,
					discountcategory,
					taxcatid,
					decimalplaces)
				VALUES (
					'$StockID',
					'" . $MyRow[1]	. "',
					'" . $MyRow[2]	. "',
					'" . $MyRow[3]	. "',
					'" . $MyRow[4]	. "',
					'" . $MyRow[5]	. "',
					"  . $MyRow[6]	. ",
					"  . $MyRow[7]	. ",
					"  . $MyRow[8]	. ",
					"  . $MyRow[9]	. ",
					"  . $MyRow[10]	. ",
					"  . $MyRow[11]	. ",
					"  . $MyRow[12]	. ",
					'" . $MyRow[13]	. "',
					'" . $MyRow[14]	. "',
					"  . $MyRow[15]	. ",
					"  . $MyRow[16]	. "
				);
			";

			$ErrMsg =  __('The item could not be added because');
			$Result = DB_query($SQL, $ErrMsg);

			if (DB_error_no() ==0) { //the insert of the new code worked so bang in the stock location records too

				$SQL = "INSERT INTO locstock (loccode,
												stockid)
									SELECT locations.loccode,
									'" . $StockID . "'
									FROM locations";

				$ErrMsg =  __('The locations for the item') . ' ' . $StockID .  ' ' . __('could not be added because');
				$InsResult = DB_query($SQL, $ErrMsg);

				if (DB_error_no() ==0) {
					prnMsg( __('New Item') .' ' . $StockID  . ' '. __('has been added to the transaction'),'info');
				} else { //location insert failed so set some useful error info
					$InputError = 1;
					prnMsg(__($InsResult),'error');
				}

			} else { //item insert failed so set some useful error info
				$InputError = 1;
				prnMsg(__($InsResult),'error');
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
		prnMsg( __('Batch Import of') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database.'),'success');
	}

	fclose($FileHandle);

} elseif ( isset($_POST['gettemplate']) || isset($_GET['gettemplate']) ) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$FieldHeadings). '"<br /><br /><br />';

} else { //show file upload form

	echo '
		<br />
		<a href="Z_ImportStocks.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			__('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . __('Send File') . '" />
        </div>
		</form>';

}


include('includes/footer.php');
