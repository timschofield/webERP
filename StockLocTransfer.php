<?php

/* Inventory Transfer - Bulk Dispatch */

require(__DIR__ . '/includes/session.php');

$Title = __('Inventory Location Transfer Shipment');
$BookMark = "LocationTransfers";
$ViewTopic = "Inventory";
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

if (isset($_POST['Submit']) OR isset($_POST['EnterMoreItems'])){
/*Trap any errors in input */

	$InputError = false; /*Start off hoping for the best */
	$TotalItems = 0;
	//Make sure this Transfer has not already been entered... aka one way around the refresh & insert new records problem
	$Result = DB_query("SELECT * FROM loctransfers WHERE reference='" . $_POST['Trf_ID'] . "'");
	if (DB_num_rows($Result)!=0){
		$InputError = true;
		$ErrorMessage = __('This transaction has already been entered') . '. ' . __('Please start over now') . '<br />';
		unset($_POST['submit']);
		unset($_POST['EnterMoreItems']);
		for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
			unset($_POST['StockID' . $i]);
			unset($_POST['StockQTY' . $i]);
		}
	}  else {
	  if ($_FILES['SelectedTransferFile']['name']) { //start file processing
	  	//initialize
	   	$InputError = false;
		$ErrorMessage='';
		//get file handle
		$FileHandle = fopen($_FILES['SelectedTransferFile']['tmp_name'], 'r');
		$TotalItems=0;
		//loop through file rows
		while ( ($MyRow = fgetcsv($FileHandle, 10000, ',')) !== false ) {

			if (count($MyRow) != 2){
				prnMsg(__('File contains') . ' '. count($MyRow) . ' ' . __('columns, but only 2 columns are expected. The comma separated file should have just two columns the first for the item code and the second for the quantity to transfer'),'error');
				fclose($FileHandle);
				include('includes/footer.php');
				exit();
			}

			// cleanup the data (csv files often import with empty strings and such)
			$StockID='';
			$Quantity=0;
			for ($i=0; $i<count($MyRow);$i++) {
				switch ($i) {
					case 0:
						$StockID = trim(mb_strtoupper($MyRow[$i]));
						$Result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $StockID . "'");
						$StockIDCheck = DB_fetch_row($Result);
						if ($StockIDCheck[0]==0){
							$InputError = true;
							$ErrorMessage .= __('The part code entered of'). ' ' . $StockID . ' '. __('is not set up in the database') . '. ' . __('Only valid parts can be entered for transfers'). '<br />';
						}
						break;
					case 1:
						$Quantity = filter_number_format($MyRow[$i]);
						if (!is_numeric($Quantity)){
						   $InputError = true;
						   $ErrorMessage .= __('The quantity entered for'). ' ' . $StockID . ' ' . __('of') . $Quantity . ' '. __('is not numeric.') . __('The quantity entered for transfers is expected to be numeric');
						}
						break;
				} // end switch statement
				if ($_SESSION['ProhibitNegativeStock']==1){
					$InTransitQuantity = GetItemQtyInTransitFromLocation($StockID, $_POST['FromStockLocation']);
					// Only if stock exists at this location
					$Result = DB_query("SELECT quantity
										FROM locstock
										WHERE stockid='" . $StockID . "'
										AND loccode='".$_POST['FromStockLocation']."'");
					$CheckStockRow = DB_fetch_array($Result);
					if (($CheckStockRow['quantity']-$InTransitQuantity) < $Quantity){
						$InputError = true;
						$ErrorMessage .= __('The item'). ' ' . $StockID . ' ' . __('does not have enough stock available (') . ' ' . $CheckStockRow['quantity'] . ')' . ' ' . __('The quantity required to transfer was') .  ' ' . $Quantity . '.<br />';
					}
				}
			} // end for loop through the columns on the row being processed
			if ($StockID!='' AND $Quantity!=0){
				$_POST['StockID' . $TotalItems] = $StockID;
				$_POST['StockQTY' . $TotalItems] = $Quantity;
				$StockID='';
				$Quantity=0;
				$TotalItems++;
			}
		  } //end while there are lines in the CSV file
		  $_POST['LinesCounter']=$TotalItems;
	   } //end if there is a CSV file to import
		  else { // process the manually input lines
			$ErrorMessage='';

			if (isset($_POST['ClearAll'])){
				unset($_POST['EnterMoreItems']);
				for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
					unset($_POST['StockID' . $i]);
					unset($_POST['StockQTY' . $i]);
				}
			}
			$StockIDAccQty = array(); //set an array to hold all items' quantity
			for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
				if (isset($_POST['Delete' . $i])){ //check box to delete the item is set
					unset($_POST['StockID' . $i]);
					unset($_POST['StockQTY' . $i]);
				}
				if (isset($_POST['StockID' . $i]) AND $_POST['StockID' . $i]!=''){
					$_POST['StockID' . $i]=trim(mb_strtoupper($_POST['StockID' . $i]));
					$Result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'");
					$MyRow = DB_fetch_row($Result);
					if ($MyRow[0]==0){
						$InputError = true;
						$ErrorMessage .= __('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. __('is not set up in the database') . '. ' . __('Only valid parts can be entered for transfers'). '<br />';
						$_POST['LinesCounter'] -= 10;
					}
					DB_free_result( $Result );
					if (!is_numeric(filter_number_format($_POST['StockQTY' . $i]))){
						$InputError = true;
						$ErrorMessage .= __('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. __('for part code'). ' ' . $_POST['StockID' . $i] . ' '. __('is not numeric') . '. ' . __('The quantity entered for transfers is expected to be numeric') . '<br />';
						$_POST['LinesCounter'] -= 10;
					}
					if (filter_number_format($_POST['StockQTY' . $i]) <= 0){
						$InputError = true;
						$ErrorMessage .= __('The quantity entered for').' '. $_POST['StockID' . $i] . ' ' . __('is less than or equal to 0') . '. ' . __('Please correct this or remove the item') . '<br />';
						$_POST['LinesCounter'] -= 10;
					}
					if ($_SESSION['ProhibitNegativeStock']==1){
						$InTransitQuantity = GetItemQtyInTransitFromLocation($_POST['StockID' . $i], $_POST['FromStockLocation']);
						// Only if stock exists at this location
						$Result = DB_query("SELECT quantity
											FROM locstock
											WHERE stockid='" . $_POST['StockID' . $i] . "'
											AND loccode='".$_POST['FromStockLocation']."'");

						$MyRow = DB_fetch_array($Result);
						if (($MyRow['quantity']-$InTransitQuantity) < filter_number_format($_POST['StockQTY' . $i])){
							$InputError = true;
							$ErrorMessage .= __('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. __('does not have enough stock available for transfer.') . '.<br />';
							$_POST['LinesCounter'] -= 10;
						}
					}
					// Check the accumulated quantity for each item
					if(isset($StockIDAccQty[$_POST['StockID'.$i]])){
						$StockIDAccQty[$_POST['StockID'.$i]] += filter_number_format($_POST['StockQTY' . $i]);
						if($MyRow[0] < $StockIDAccQty[$_POST['StockID'.$i]]){
							$InputError = true;
							$ErrorMessage .=__('The part code entered of'). ' ' . $_POST['StockID'.$i] . ' '.__('does not have enough stock available for transter due to accumulated quantity is over quantity on hand.') . '<br />';
							$_POST['LinesCounter'] -= 10;
						}
					} else {
						$StockIDAccQty[$_POST['StockID'.$i]] = filter_number_format($_POST['StockQTY' . $i]);
					} //end of accumulated check

					$TotalItems++;
				}
			}//for all LinesCounter
		}

		if ($TotalItems == 0){
			$InputError = true;
			$ErrorMessage .= __('You must enter at least 1 Stock Item to transfer') . '<br />';
		}

	/*Ship location and Receive location are different */
		if ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
			$InputError=true;
			$ErrorMessage .= __('The transfer must have a different location to receive into and location sent from');
		}
	 } //end if the transfer is not a duplicated
}

if(isset($_POST['Submit']) AND $InputError==false){

	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to BEGIN Location Transfer transaction');

	DB_Txn_Begin();

	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_query($DecimalsSql);
			$DecimalRow = DB_fetch_array($DecimalResult);
			$SQL = "INSERT INTO loctransfers (reference,
								stockid,
								shipqty,
								shipdate,
								shiploc,
								recloc)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . "',
							'" . Date('Y-m-d H-i-s') . "',
							'" . $_POST['FromStockLocation']  ."',
							'" . $_POST['ToStockLocation'] . "')";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$ResultLocShip = DB_query($SQL, $ErrMsg);
		}
	}
	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to COMMIT Location Transfer transaction');
	DB_Txn_Commit();

	prnMsg( __('The inventory transfer records have been created successfully'),'success');
	echo '<p><a href="'.$RootPath.'/PDFStockLocTransfer.php?TransferNo=' . $_POST['Trf_ID'] . '" target="_blank">' .  __('Print the Transfer Docket'). '</a></p>';
	include('includes/footer.php');

} else {
	//Get next Inventory Transfer Shipment Reference Number
	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if(!isset($Trf_ID)){
		$Trf_ID = GetNextTransNo(16);
	}

	if (isset($InputError) and $InputError==true){
		echo '<br />';

		prnMsg($ErrorMessage, 'error');
		echo '<br />';

	}

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Dispatch') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="4"><input type="hidden" name="Trf_ID" value="' . $Trf_ID . '" /><h3>' .  __('Inventory Location Transfer Shipment Reference').' # '. $Trf_ID. '</h3></th>
		</tr>';

	$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1 ORDER BY locationname";
	$ResultStkLocs = DB_query($SQL);

	echo '<tr>
			<td>' . __('From Stock Location') . ':</td>
			<td><select name="FromStockLocation">';

	while ($MyRow=DB_fetch_array($ResultStkLocs)){
		if (isset($_POST['FromStockLocation'])){
			if ($MyRow['loccode'] == $_POST['FromStockLocation']){
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']. '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		} elseif ($MyRow['loccode']==$_SESSION['UserStockLocation']){
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			$_POST['FromStockLocation']=$MyRow['loccode'];
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select></td>';

	DB_data_seek($ResultStkLocs,0); //go back to the start of the locations result
	echo '<td>' . __('To Stock Location').':</td>
			<td><select name="ToStockLocation">';
	while ($MyRow=DB_fetch_array($ResultStkLocs)){
		if (isset($_POST['ToStockLocation'])){
			if ($MyRow['loccode'] == $_POST['ToStockLocation']){
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		} elseif ($MyRow['loccode']==$_SESSION['UserStockLocation']){
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			$_POST['ToStockLocation']=$MyRow['loccode'];
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . __('Upload CSV file of Transfer Items and Quantites') . ':</td>
			<td><input name="SelectedTransferFile" type="file" /></td>
		  </tr>
		  </table>
		  <br />
		  <table class="selection">
			<tr>
				<th>' .  __('Item Code'). '</th>
				<th>' .  __('Quantity'). '</th>
				<th>' . __('Clear All') . ':<input type="checkbox" name="ClearAll" /></th>
			</tr>';

	$j=0; /* row counter for reindexing */
	if(isset($_POST['LinesCounter'])){

		for ($i=0;$i < $_POST['LinesCounter'];$i++){
			if (!isset($_POST['StockID'. $i])){
				continue;
			}
			if ($_POST['StockID' . $i] ==''){
				break;
			}

			echo '<tr>
					<td><input type="text" name="StockID' . $j .'" size="21"  maxlength="20" value="' . $_POST['StockID' . $i] . '" /></td>
					<td><input type="text" name="StockQTY' . $j .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $i],'Variable') . '" /></td>
					<td>' . __('Delete') . '<input type="checkbox" name="Delete' . $j .'" /></td>
				</tr>';
			$j++;
		}
	} else {
		$j = 0;
	}
	// $i is incremented an extra time, so 9 to get 10...
	$z=($j + 9);

	while($j < $z) {
		if (!isset($_POST['StockID' . $j])) {
			$_POST['StockID' . $j]='';
		}
		if (!isset($_POST['StockQTY' . $j])) {
			$_POST['StockQTY' . $j]=0;
		}
		echo '<tr>
				<td><input type="text" name="StockID' . $j .'" ' . ($j==0 OR $j==$z-9 ? 'autofocus="autofocus"' : '') . ' size="21"  maxlength="20" value="' . $_POST['StockID' . $j] . '" /></td>
				<td><input type="text" name="StockQTY' . $j .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $j]) . '" /></td>
			</tr>';
		$j++;
	}

	echo '</table>
		<br />
		<div class="centre">
		<input type="hidden" name="LinesCounter" value="'. $j .'" />
		<input type="submit" name="EnterMoreItems" value="'. __('Add More Items'). '" />
		<input type="submit" name="Submit" value="'. __('Create Transfer Shipment'). '" />
		<br />
		</div>
		</div>
		</form>';
	include('includes/footer.php');
}
