<?php
include('includes/session.php');
$Title = _('Update Item Costs From CSV');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('Update Item Costs from CSV file') . '" />' . ' ' .
		_('Update Item Costs from CSV file') . '</p>';

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

	//check for correct number of fields
	if ( count($HeadRow) != count($FieldHeadings) ) {
		prnMsg (_('File contains') . ' '. count($HeadRow). ' ' . _('columns, expected') . ' '. count($FieldHeadings) ,'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit;
	}

	//test header row field name and sequence
	$HeadingColumnNumber = 0;
	foreach ($HeadRow as $HeadField) {
		if ( trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$HeadingColumnNumber]))) {
			prnMsg (_('The file to import the item cost updates from contains incorrect column headings') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$HeadingColumnNumber]). '<br />' . _('The column headings must be') . ' StockID, Material Cost, Labour Cost, Overhead Cost','error');
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

		$StockID = mb_strtoupper($myrow[0]);

		$NewCost = (double)$myrow[1]+(double)$myrow[2]+(double)$myrow[3];

		$sql = "SELECT mbflag,
						materialcost,
						labourcost,
						overheadcost,
						sum(quantity) as totalqoh
				FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
				WHERE stockmaster.stockid='" . $StockID . "'
				GROUP BY materialcost,
						labourcost,
						overheadcost";

		$ErrMsg = _('The selected item code does not exist');
	    $OldResult = DB_query($sql,$ErrMsg);
	    $OldRow = DB_fetch_array($OldResult);
	    $QOH = $OldRow['totalqoh'];

	 	$OldCost = $OldRow['materialcost'] + $OldRow['labourcost'] + $OldRow['overheadcost'];
		//dont update costs for assembly or kit-sets or ghost items!!
		if ((abs($NewCost - $OldCost) > pow(10,-($_SESSION['StandardCostDecimalPlaces']+1)))
			AND $OldRow['mbflag']!='K'
			AND $OldRow['mbflag']!='A'
			AND $OldRow['mbflag']!='G'){

			ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $QOH);

			$SQL = "UPDATE stockmaster SET	materialcost='" . (double) $myrow[1] . "',
											labourcost='" . (double) $myrow[2] . "',
											overheadcost='" . (double) $myrow[3] . "',
											lastcost='" . $OldCost . "',
											lastcostupdate ='" . Date('Y-m-d')."'
									WHERE stockid='" . $StockID . "'";

			$ErrMsg = _('The cost details for the stock item could not be updated because');
			$DbgMsg = _('The SQL that failed was');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			UpdateCost($db, $StockID); //Update any affected BOMs

		}

		$LineNumber++;
	}

	DB_Txn_Commit();
	prnMsg( _('Batch Update of costs') .' ' . $FileName  . ' '. _('has been completed. All transactions committed to the database.'),'success');

	fclose($FileHandle);

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' .
			_('This function updates the costs of all items from a comma separated variable (csv) file.') . '<br />' .
			_('The file must contain four columns, and the first row should be the following headers:') . '<br /><i>StockID, Material Cost, Labour Cost, Overhead Cost</i><br />' .
			_('followed by rows containing these four fields for each cost to be updated.') .  '<br />' .
			_('The StockID field must have a corresponding entry in the stockmaster table.') . '</div>';

	echo '<br /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' ._('Upload file') . ': <input name="CostUpdateFile" type="file" />
			<input type="submit" name="submit" value="' . _('Send File') . '" />
		</div>
		</form>';
}

include('includes/footer.php');

?>