<?php
include('includes/session.php');
$Title = __('Update Item Costs From CSV');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Update Item Costs from CSV file') . '" />' . ' ' .
		__('Update Item Costs from CSV file') . '</p>';

$FieldHeadings = array('StockID',
						'Material Cost',
						'Labour Cost',
						'Overhead Cost');

if (isset($_FILES['CostUpdateFile']) and $_FILES['CostUpdateFile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['CostUpdateFile']['name'];
	$TempName  = $_FILES['CostUpdateFile']['tmp_name'];
	$FileSize = $_FILES['CostUpdateFile']['size'];
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
		prnMsg(__('File contains') . ' '. count($HeadRow). ' ' . __('columns, expected') . ' '. count($FieldHeadings) ,'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit();
	}

	//test header row field name and sequence
	$HeadingColumnNumber = 0;
	foreach ($HeadRow as $HeadField) {
		if (trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$HeadingColumnNumber]))) {
			prnMsg(__('The file to import the item cost updates from contains incorrect column headings') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$HeadingColumnNumber]). '<br />' . __('The column headings must be') . ' StockID, Material Cost, Labour Cost, Overhead Cost','error');
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

		$StockID = mb_strtoupper($MyRow[0]);

		$NewCost = (double)$MyRow[1]+(double)$MyRow[2]+(double)$MyRow[3];

		$SQL = "SELECT mbflag,
						materialcost,
						labourcost,
						overheadcost,
						sum(quantity) AS totalqoh
				FROM stockmaster
				INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				WHERE stockmaster.stockid = '" . $StockID . "'
				GROUP BY materialcost,
					labourcost,
					overheadcost";

		$ErrMsg = __('The selected item code does not exist');
	    $OldResult = DB_query($SQL, $ErrMsg);
	    $OldRow = DB_fetch_array($OldResult);
	    $QOH = $OldRow['totalqoh'];

	 	$OldCost = $OldRow['materialcost'] + $OldRow['labourcost'] + $OldRow['overheadcost'];
		//dont update costs for assembly or kit-sets or ghost items!!
		if ((abs($NewCost - $OldCost) > pow(10,-($_SESSION['StandardCostDecimalPlaces']+1)))
			AND $OldRow['mbflag']!='K'
			AND $OldRow['mbflag']!='A'
			AND $OldRow['mbflag']!='G'){

			ItemCostUpdateGL($StockID, $NewCost, $OldCost, $QOH);

			$SQL = "UPDATE stockmaster
					SET	materialcost='" . (double) $MyRow[1] . "',
						labourcost='" . (double) $MyRow[2] . "',
						overheadcost='" . (double) $MyRow[3] . "',
						lastcost='" . $OldCost . "',
						lastcostupdate = CURRENT_DATE
					WHERE stockid='" . $StockID . "'";

			$ErrMsg = __('The cost details for the stock item could not be updated because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			UpdateCost($StockID); //Update any affected BOMs
		}

		$LineNumber++;
	}

	DB_Txn_Commit();
	prnMsg( __('Batch Update of costs') .' ' . $FileName  . ' '. __('has been completed. All transactions committed to the database.'),'success');

	fclose($FileHandle);

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' .
			__('This function updates the costs of all items from a comma separated variable (csv) file.') . '<br />' .
			__('The file must contain four columns, and the first row should be the following headers:') . '<br /><i>StockID, Material Cost, Labour Cost, Overhead Cost</i><br />' .
			__('followed by rows containing these four fields for each cost to be updated.') .  '<br />' .
			__('The StockID field must have a corresponding entry in the stockmaster table.') . '</div>';

	echo '<br /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .__('Upload file') . ': <input name="CostUpdateFile" type="file" />
			<input type="submit" name="submit" value="' . __('Send File') . '" />
		</div>
		</form>';
}

include('includes/footer.php');
