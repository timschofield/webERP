<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Import Bills of Materials');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include(__DIR__ . '/includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] .
		'/images/inventory.png" title="' .
		__('Import BOMs from .csv') . '" />' . ' ' .
		__('Import BOMs from .csv') . '</p>';

// BOM CSV Import Utility
// The CSV file structure must match the template provided
// The CSV should be encoded in UTF-8 (no BOM) but ANSI should be acceptable if there are no special characters
//   (likely only applies to the Remark column)

// If this script is called with a file object the file contents are imported.
// If this script is called with the gettemplate flag a template file is served.
// If neither, a file upload form is displayed.

// The "digitals" column in the bom table is not imported. The digitals column was added
// in upgrade4.12.3-4.13.sql but does not appear to be used or found in any business logic
// EXCEPT CopyBOM.php which preserves the value when copying a BOM.

$FieldHeadings = array(
	'ParentStockID',		//  0 - Finished good/Assembly item code
	'ComponentStockID',		//  1 - Component/raw material item code
	'Quantity',             //  2 - Quantity required per unit of parent
	'Sequence',             //  3 - Order sequence in BOM (10, 20, 30...)
	'WorkCentreCode',       //  4 - Work centre where component is added
	'LocationCode',         //  5 - Location/warehouse code
	'EffectiveAfter',       //  6 - Date component becomes active (YYYY-MM-DD)
	'EffectiveTo',          //  7 - Date component becomes inactive (YYYY-MM-DD)
	'AutoIssue',            //  8 - Auto-issue to work orders (0=No, 1=Yes)
	'Remark'                //  9 - Notes about this BOM line
);

if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing

	// initialize
	$FieldTarget = 10;
	$InputError = 0;

	// check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];

	// open CSV file
	$FileHandle = fopen($TempName, 'r');

	// get header row from CSV file
	$HeadRow = fgetcsv($FileHandle, 10000, ",", '"');  // support embedded commas in text strings using " " enclosed csv
	// remove UTF-8 BOM if present
	if (substr($HeadRow[0], 0, 3) === "\xef\xbb\xbf") {
		$HeadRow[0] = substr($HeadRow[0], 3);
	}

	// check header for correct number of fields
	if ( count($HeadRow) != count($FieldHeadings) ) {
		prnMsg(__('File contains '. count($HeadRow). ' columns, expected '. count($FieldHeadings). '. Try downloading a new template.'),'error');
		fclose($FileHandle);
		include(__DIR__ . '/includes/footer.php');
		exit();
	}

	// check header for correct field names and order
	$Head = 0;
	foreach ($HeadRow as $HeadField) {
		if ( mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$Head]) ) {
			prnMsg(__('File contains incorrect headers '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$Head]). '. Try downloading a new template.'),'error');
			fclose($FileHandle);
			include(__DIR__ . '/includes/footer.php');
			exit();
		}
		$Head++;
	}

	// start database transaction
	DB_Txn_Begin();

	// loop to process CSV file rows
	$Row = 1;
	while ( ($MyRow = fgetcsv($FileHandle, 10000, ",")) !== false ) {

		// check for correct number of fields
		$FieldCount = count($MyRow);
		if ($FieldCount != $FieldTarget){
			prnMsg(__($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include(__DIR__ . '/includes/footer.php');
			exit();
		}

		// extract ParentStockID (uppercase, no trimming on first pass)
		$ParentStockID = mb_strtoupper($MyRow[0]);
		$ComponentStockID = mb_strtoupper($MyRow[1]);

		// cleanup row fields (strip spaces, tabs, and control chars)
		foreach ($MyRow as &$Value) {
			$Value = DB_escape_string(trim($Value));
		}

		// Update extracted IDs after cleanup
		$ParentStockID = mb_strtoupper($MyRow[0]);
		$ComponentStockID = mb_strtoupper($MyRow[1]);

		//==================================================================================
		// VALIDATION LOGIC
		//==================================================================================

		// Check for empty required fields
		if (mb_strlen($ParentStockID) == 0) {
			$InputError = 1;
			prnMsg(__('The Parent Stock Item code cannot be empty on row') . ' ' . $Row, 'error');
		}
		if (mb_strlen($ComponentStockID) == 0) {
			$InputError = 1;
			prnMsg(__('The Component Stock Item code cannot be empty on row') . ' ' . $Row, 'error');
		}

		// Check if ParentStockID exists in stockmaster
		if ($InputError != 1) {
			$SQL = "SELECT stockid, mbflag FROM stockmaster WHERE stockid='" . $ParentStockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0) {
				$InputError = 1;
				prnMsg(__('Parent stock item code') . ' ' . $ParentStockID . ' ' . __('does not exist in the system on row') . ' ' . $Row, 'error');
			} else {
				$ParentRow = DB_fetch_array($Result);
				$ParentMBFlag = $ParentRow['mbflag'];
				
				// Check if parent has valid MBFlag for BOM
				// Only M (Manufactured), A (Assembly), K (Kit-Set), and G (Phantom) can have BOMs
				if ($ParentMBFlag != 'M' AND $ParentMBFlag != 'A' AND $ParentMBFlag != 'K' AND $ParentMBFlag != 'G') {
					$InputError = 1;
					prnMsg(__('Parent item') . ' ' . $ParentStockID . ' ' . __('must be type Manufactured (M), Assembly (A), Kit-Set (K), or Phantom (G) to have a BOM on row') . ' ' . $Row, 'error');
				}
			}
		}

		// Check if ComponentStockID exists in stockmaster
		if ($InputError != 1) {
			$SQL = "SELECT stockid FROM stockmaster WHERE stockid='" . $ComponentStockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0) {
				$InputError = 1;
				prnMsg(__('Component stock item code') . ' ' . $ComponentStockID . ' ' . __('does not exist in the system on row') . ' ' . $Row, 'error');
			}
		}

		// Check that parent and component are not the same
		if ($ParentStockID == $ComponentStockID) {
			$InputError = 1;
			prnMsg(__('The component and parent cannot be the same on row') . ' ' . $Row, 'error');
		}

		// Check for recursive BOM (parent cannot be a component of itself at any level)
		if ($InputError != 1) {
			$SQL = "SELECT component FROM bom WHERE parent='" . $ComponentStockID . "'";
			$Result = DB_query($SQL);
			while ($BomRow = DB_fetch_array($Result)) {
				if ($BomRow['component'] == $ParentStockID) {
					$InputError = 1;
					prnMsg(__('Recursive BOM detected: component') . ' ' . $ComponentStockID . ' ' . __('already uses') . ' ' . $ParentStockID . ' ' . __('as a component on row') . ' ' . $Row, 'error');
					break;
				}
			}
		}

		// Validate Quantity
		// - quantity is normally expected to be non-zero
		// - comment/uncomment test and error message to allow zero-quantity items on a BOM (usually a potential substitute)
		// - BOMs.php also includes a check for zero-quantity which must also be disabled
		// - it is assumed all business logic handles zero-quantity items appropriately (ignoring or reporting as appropriate)
		if (!is_numeric(filter_number_format($MyRow[2]))) {
			$InputError = 1;
			prnMsg(__('The quantity must be numeric on row') . ' ' . $Row, 'error');
		//} elseif (filter_number_format($MyRow[2]) <= 0) { // allow positive quantity only
		} elseif (filter_number_format($MyRow[2]) < 0) {
			$InputError = 1;
			//prnMsg(__('The quantity must be a positive number on row') . ' ' . $Row, 'error');  // allow positive quantity only
			prnMsg(__('The quantity must be zero or greater on row') . ' ' . $Row, 'error');
		}

		// Validate Sequence (numeric, non-negative)
		if (!is_numeric($MyRow[3]) OR $MyRow[3] < 0) {
			$InputError = 1;
			prnMsg(__('The sequence must be a non-negative number on row') . ' ' . $Row, 'error');
		}

		// Validate WorkCentreCode exists
		if (mb_strlen($MyRow[4]) > 0) {
			$SQL = "SELECT code FROM workcentres WHERE code='" . $MyRow[4] . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0) {
				$InputError = 1;
				prnMsg(__('Work centre code') . ' ' . $MyRow[4] . ' ' . __('does not exist on row') . ' ' . $Row, 'error');
			}
		} else {
			$InputError = 1;
			prnMsg(__('Work centre code cannot be empty on row') . ' ' . $Row, 'error');
		}

		// Validate LocationCode exists
		if (mb_strlen($MyRow[5]) > 0) {
			$SQL = "SELECT loccode FROM locations WHERE loccode='" . $MyRow[5] . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0) {
				$InputError = 1;
				prnMsg(__('Location code') . ' ' . $MyRow[5] . ' ' . __('does not exist on row') . ' ' . $Row, 'error');
			}
		} else {
			$InputError = 1;
			prnMsg(__('Location code cannot be empty on row') . ' ' . $Row, 'error');
		}

		// Validate EffectiveAfter date
		if (mb_strlen($MyRow[6]) > 0) {
			$EffectiveAfter = trim($MyRow[6]);
			// Try to parse date - accept YYYY-MM-DD format
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $EffectiveAfter)) {
				$InputError = 1;
				prnMsg(__('EffectiveAfter date must be in YYYY-MM-DD format on row') . ' ' . $Row, 'error');
			}
		} else {
			$EffectiveAfter = '1000-01-01'; // Default as per schema
		}

		// Validate EffectiveTo date
		if (mb_strlen($MyRow[7]) > 0) {
			$EffectiveTo = trim($MyRow[7]);
			// Try to parse date - accept YYYY-MM-DD format
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $EffectiveTo)) {
				$InputError = 1;
				prnMsg(__('EffectiveTo date must be in YYYY-MM-DD format on row') . ' ' . $Row, 'error');
			}
		} else {
			$EffectiveTo = '9999-12-31'; // Default as per schema
		}

		// Validate AutoIssue (must be 0 or 1)
		if ($MyRow[8] != '0' AND $MyRow[8] != '1') {
			$InputError = 1;
			prnMsg(__('AutoIssue must be 0 (No) or 1 (Yes) on row') . ' ' . $Row, 'error');
		}

		// Validate Remark length (max 500 chars per schema)
		if (mb_strlen($MyRow[9]) > 500) {
			$InputError = 1;
			prnMsg(__('Remark cannot exceed 500 characters on row') . ' ' . $Row, 'error');
		}

		// Check if BOM entry already exists
		if ($InputError != 1) {
			$SQL = "SELECT parent FROM bom 
					WHERE parent='" . $ParentStockID . "'
					AND component='" . $ComponentStockID . "'
					AND workcentreadded='" . $MyRow[4] . "'
					AND loccode='" . $MyRow[5] . "'";
			$Result = DB_query($SQL);
			
			if (DB_num_rows($Result) > 0) { // BOM entry exists
				if (isset($_POST['SkipExisting']) && $_POST['SkipExisting'] == 'on') {
					prnMsg(__('BOM entry for parent') . ' ' . $ParentStockID . ' ' . __('component') . ' ' . $ComponentStockID . ' ' . __('already exists, row is being skipped'), 'warn');
					$Row++;
					continue;
				} elseif (isset($_POST['UpdateExisting']) && $_POST['UpdateExisting'] == 'on') {
					prnMsg(__('BOM entry for parent') . ' ' . $ParentStockID . ' ' . __('component') . ' ' . $ComponentStockID . ' ' . __('already exists, existing data will be updated (TODO, skipping)'), 'warn');
					// TODO: implement update logic
					$Row++;
					continue;
				} else {
					$InputError = 1;
					prnMsg(__('Aborting, BOM entry already exists: Parent=') . $ParentStockID . ', Component=' . $ComponentStockID, 'error');
					fclose($FileHandle);
					include(__DIR__ . '/includes/footer.php');
					exit();
				}
			}
		}

		//==================================================================================
		// INSERT BOM ENTRY
		//==================================================================================

		if ($InputError != 1) {
			
			$SQL = "INSERT INTO bom (
					parent,
					component,
					workcentreadded,
					loccode,
					quantity,
					sequence,
					effectiveafter,
					effectiveto,
					autoissue,
					remark,
					digitals
				) VALUES (
					'" . $ParentStockID . "',
					'" . $ComponentStockID . "',
					'" . $MyRow[4] . "',
					'" . $MyRow[5] . "',
					" . filter_number_format($MyRow[2]) . ",
					" . $MyRow[3] . ",
					'" . $EffectiveAfter . "',
					'" . $EffectiveTo . "',
					" . $MyRow[8] . ",
					'" . $MyRow[9] . "',
					0
				)";

			$ErrMsg = __('The BOM entry could not be added because');
			$Result = DB_query($SQL, $ErrMsg);

			if (DB_error_no() == 0) {
				prnMsg(__('New BOM entry') . ': ' . $ParentStockID . ' -> ' . $ComponentStockID . ' ' . __('has been added to the transaction'), 'info');
			} else {
				$InputError = 1;
				prnMsg(__('BOM entry insert failed on row') . ' ' . $Row . ': ' . DB_error_msg(), 'error');
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
		prnMsg(__('Batch Import of') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database.'),'success');
	}

	fclose($FileHandle);

} elseif ( isset($_POST['gettemplate']) || isset($_GET['gettemplate']) ) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$FieldHeadings). '"<br /><br /><br />';

} else { //show file upload form
	
	prnMsg(__('The CSV file should be encoded in UTF-8 (may be ANSI if no special characters).'), 'warn');
	prnMsg(__('Date fields must be in YYYY-MM-DD format.'), 'info');
	prnMsg(__('Sequence numbers are typically in increments of 10 (10, 20, 30, etc).'), 'info');

	echo '<a href="' . $RootPath . '/Z_ImportStocksBOMs.php?gettemplate=1">Download Import Template</a>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>';
	echo '<legend>Upload BOM Import File</legend>';
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
	echo __('Upload file') . ': <input name="userfile" type="file" required="required" />';
	echo '<br /><br />';
	echo '<input type="submit" value="' . __('Send File') . '" />';

	echo '<br /><br />';
	echo '<label>';
	echo '<input type="checkbox" name="SkipExisting" /> ';
	echo __('Skip Row if BOM Entry Exists');
	echo '</label>';

	echo '<br />';
	echo '<label>';
	echo '<input type="checkbox" name="UpdateExisting" /> ';
	echo __('Update Current Data if BOM Entry Exists (not yet implemented)');
	echo '</label>';

	echo '</fieldset>';
	echo '</form>';
	
	echo '<fieldset>';
	echo '<legend>BOM CSV File Format</legend>';
	echo '<p><strong>Required Columns:</strong></p>';
	echo '<table border="1" cellpadding="5">';
	echo '<tr><th>Column</th><th>Description</th><th>Example</th><th>Required</th></tr>';
	echo '<tr><td>ParentStockID</td><td>Finished good/Assembly item code</td><td>FINISHED_GOOD_01</td><td>Yes</td></tr>';
	echo '<tr><td>ComponentStockID</td><td>Component/raw material item code</td><td>RAW_MATERIAL_01</td><td>Yes</td></tr>';
	echo '<tr><td>Quantity</td><td>Quantity required per unit of parent</td><td>1.5</td><td>Yes</td></tr>';
	echo '<tr><td>Sequence</td><td>Order sequence in BOM (10, 20, 30...)</td><td>10</td><td>Yes</td></tr>';
	echo '<tr><td>WorkCentreCode</td><td>Work centre where component is added</td><td>WC001</td><td>Yes</td></tr>';
	echo '<tr><td>LocationCode</td><td>Location/warehouse code</td><td>LOC001</td><td>Yes</td></tr>';
	echo '<tr><td>EffectiveAfter</td><td>Date component becomes active (YYYY-MM-DD)</td><td>2026-05-04</td><td>No (default: 1000-01-01)</td></tr>';
	echo '<tr><td>EffectiveTo</td><td>Date component becomes inactive (YYYY-MM-DD)</td><td>9999-12-31</td><td>No (default: 9999-12-31)</td></tr>';
	echo '<tr><td>AutoIssue</td><td>Auto-issue to work orders (0=No, 1=Yes)</td><td>0</td><td>Yes</td></tr>';
	echo '<tr><td>Remark</td><td>Notes about this BOM line (max 500 chars)</td><td>Quality inspection required</td><td>No</td></tr>';
	echo '</table>';
	echo '</fieldset>';
}

include(__DIR__ . '/includes/footer.php');
?>