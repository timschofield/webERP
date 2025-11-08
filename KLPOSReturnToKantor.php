<?php

/**************************************************************************************
KL RICARD Clean up of StockLocTransfer so SPG can create transfers from their shop to KANTO
***************************************************************************************/

require(__DIR__ . '/includes/session.php');

$Title = __('Return Transfer from Shop to Kantor');
$BookMark = "LocationTransfers";
$ViewTopic = "Inventory";
include('includes/header.php');
include('includes/StockFunctions.php');

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');
include('includes/KLPOSGeneral.php');

include('includes/WebClientPrint/WebClientPrint.php');
include('includes/KLESCPOSCommands.php');

if (isset($_POST['Submit']) OR isset($_POST['EnterMoreItems'])){
/*Trap any errors in input */

	$InputError = False; /*Start off hoping for the best */
	$TotalItems = 0;
	//Make sure this Transfer has not already been entered... aka one way around the refresh & insert new records problem
	$Result = DB_query("SELECT * FROM loctransfers WHERE reference='" . $_POST['Trf_ID'] . "'");
	if (DB_num_rows($Result)!=0){
		$InputError = true;
		$ErrorMessage = __('This transaction has already been entered') . '. ' . __('Please start over now') . '<br />';
		unset($_POST['submit']);
		unset($_POST['EnterMoreItems']);
		for ($i=$_POST['LinesCounter']-2;$i<$_POST['LinesCounter'];$i++){
			unset($_POST['StockID' . $i]);
			unset($_POST['StockQTY' . $i]);
		}
	}  else {
		$ErrorMessage='';

		if (isset($_POST['ClearAll'])){
			unset($_POST['EnterMoreItems']);
			for ($i=$_POST['LinesCounter']-2;$i<$_POST['LinesCounter'];$i++){
				unset($_POST['StockID' . $i]);
				unset($_POST['StockQTY' . $i]);
			}
		}
		for ($i=0;$i<$_POST['LinesCounter'];$i++){
			if (isset($_POST['Delete' . $i])){ //check box to delete the item is set
				unset($_POST['StockID' . $i]);
				unset($_POST['StockQTY' . $i]);
			}
			if (isset($_POST['StockID' . $i]) AND $_POST['StockID' . $i]!=''){
				$_POST['StockID' . $i]=trim(mb_strtoupper($_POST['StockID' . $i]));
				$Result = DB_query("SELECT COUNT(*) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'");
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0]==0){
					$InputError = True;
					$ErrorMessage .= __('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. __('is not set up in the database') . '. ' . __('Only valid parts can be entered for transfers'). '<br />';
					$_POST['LinesCounter'] -= 1;
				}
				if (!is_numeric(filter_number_format($_POST['StockQTY' . $i]))){
					$InputError = True;
					$ErrorMessage .= __('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. __('for part code'). ' ' . $_POST['StockID' . $i] . ' '. __('is not numeric') . '. ' . __('The quantity entered for transfers is expected to be numeric') . '<br />';
					$_POST['LinesCounter'] -= 1;
				}
				if (filter_number_format($_POST['StockQTY' . $i]) <= 0){
					$InputError = True;
					$ErrorMessage .= __('The quantity entered for').' '. $_POST['StockID' . $i] . ' ' . __('is less than or equal to 0') . '. ' . __('Please correct this or remove the item') . '<br />';
					$_POST['LinesCounter'] -= 1;
				}
				if ($_SESSION['ProhibitNegativeStock']==1){
					$InTransitQuantity = GetItemQtyInTransitFromLocation($_POST['StockID' . $i], $_POST['FromStockLocation']);
				}
				// Check if the last one entered already exists on the transfer
				$LastItem = $_POST['LinesCounter']-1;
				if ((!$InputError) AND ($i < $LastItem) AND ($_POST['StockID' . $i] == trim(mb_strtoupper($_POST['StockID' . $LastItem])))){
					$_POST['StockQTY' . $i] += $_POST['StockQTY' . $LastItem];
					$_POST['StockID' . $LastItem] ='';
					$_POST['StockQTY' . $LastItem] =1;
					$_POST['LinesCounter'] -= 1;
					prnMsg("Item ". $_POST['StockID' . $i] . " was already in the transfer. Just updating quantity to " . $_POST['StockQTY' . $i],"warn");
				}

				$TotalItems++;
			}
		}//for all LinesCounter

		if ($TotalItems == 0){
			$InputError = True;
			$ErrorMessage .= __('You must enter at least 1 Stock Item to transfer') . '<br />';
		}

		/*Ship location and Receive location are different */
		if ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
			$InputError=True;
			$ErrorMessage .= __('The transfer must have a different location to receive into and location sent from');
		}
	} //end if the transfer is not a duplicated
}

if (isset($_POST['Submit']) AND $InputError==False){

	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to BEGIN Location Transfer transaction');

	DB_Txn_Begin();

	for ($i = 0; $i < $_POST['LinesCounter']; $i++){

		if ($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid = '" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_query($DecimalsSql);
			$DecimalRow = DB_fetch_array($DecimalResult);
			$SQL = "INSERT INTO loctransfers (reference,
								stockid,
								shipqty,
								shipdate,
								shiploc,
								recloc,
								reason)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . "',
							'" . date('Y-m-d H-i-s') . "',
							'" . $_POST['FromStockLocation']  ."',
							'" . $_POST['ToStockLocation'] . "',
							'" . (isset($_POST['ReasonReturn' . $i]) ? $_POST['ReasonReturn' . $i] : '') . "')";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$ResultLocShip = DB_query($SQL, $ErrMsg);

			if ($_POST['ToStockLocation'] == 'SERDE'){
				KLSendEmail("ItemTransferredToSpecialLocation", "Silent", $_POST['StockID' . $i], round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']),$_POST['FromStockLocation'], $_POST['ToStockLocation']);
			}
		}
	}
	DB_Txn_Commit();

	$TextToPrint = KLPrintReturnTransferToKantor($_POST['Trf_ID']);

	//################## PRINTING STUFF ##################### 
	$identifier=GetPOSIdentifier();
	$FileName = GetFilenameFromPOSIdentifier($identifier);   
	file_put_contents($FileName, $TextToPrint);
	$TextActionToPrint = 'Print Return Transfer number: '. $_POST['Trf_ID'];
	include('includes/KLSilentPrinting.php');
	//################## PRINTING STUFF ##################### 

	include('includes/footer.php');

} else {
	//Get next Inventory Transfer Shipment Reference Number
	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if (!isset($Trf_ID)){
		$Trf_ID = GetNextTransNo(16);
	}

	if (isset($InputError) and $InputError==true){
		echo '<br />';

		prnMsg($ErrorMessage, 'error');
		echo '<br />';

	}

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Return Transfer from Shop to Kantor') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="FromStockLocation" value="' . $_SESSION['UserStockLocation'] . '" />';
	echo '<input type="hidden" name="ToStockLocation" value="'."KANTO".'" />';

	echo '<table class="selection">
		<thead>
			<tr>
				<th colspan="4"><h3>' . __('Return Transfer from Shop to Kantor') . '</h3></th>
			</tr>';
	echo '<tr>
			<th colspan="5"><input type="hidden" name="Trf_ID" value="' . $Trf_ID . '" /><h3>' .  __('Return Transfer from Shop to Kantor').' # '. $Trf_ID. '</h3></th>
		</tr>';
	echo '<tr>
			<th class="SortedColum">' . __('Code') . '</th>
			<th class="SortedColum">' . __('Quantity') . '</th>
			<th class="SortedColum">' . __('Reason for Return') . '</th>
			<th colspan="3"></th>
		</tr>
		</thead>
		<tbody>';
	$j=0; /* row counter for reindexing */
	if (isset($_POST['LinesCounter'])){

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
					<td><select name="ReasonReturn' . $j .'">';
			
			echo '<option value="">' . __('Select Reason') . '</option>';
			
			// Add hard-coded options first
			$SelectedRequested = (isset($_POST['ReasonReturn' . $i]) && $_POST['ReasonReturn' . $i] == 'REQUESTED') ? 'selected="selected"' : '';
			$SelectedOthers = (isset($_POST['ReasonReturn' . $i]) && $_POST['ReasonReturn' . $i] == 'OTHERS') ? 'selected="selected"' : '';
			echo '<option value="REQUESTED_SS" ' . $SelectedRequested . '>' . __('Requested by Shop Support Team') . '</option>';
			echo '<option value="OTHERS_SPG" ' . $SelectedOthers . '>' . __('Return by any other reason') . '</option>';
			
			// Get return reasons from database
			$ReasonSQL = "SELECT servicecode,
							servicedescription
						FROM klservicetypes
						ORDER BY servicedescription";
			$ReasonResult = DB_query($ReasonSQL);
			
			while ($ReasonRow = DB_fetch_array($ReasonResult)) {
				$Selected = (isset($_POST['ReasonReturn' . $i]) && $_POST['ReasonReturn' . $i] == $ReasonRow['servicecode']) ? 'selected="selected"' : '';
				echo '<option value="' . $ReasonRow['servicecode'] . '" ' . $Selected . '>Service: ' . $ReasonRow['servicedescription'] . '</option>';
			}
			
			echo '</select></td>
					<td>' . __('Delete') . '<input type="checkbox" name="Delete' . $j .'" /></td>
				</tr>';
			$j++;
		}
	} else {
		$j = 0;
	}
	// $i is incremented an extra time, so 1 to get 2...
	if (!isset($_POST['StockID' . $j])) {
		$_POST['StockID' . $j]='';
	}
	if (!isset($_POST['StockQTY' . $j])) {
		$_POST['StockQTY' . $j]=1;
	}
	echo '<tr>
			<td><input type="text" name="StockID' . $j .'" autofocus="autofocus" size="21"  maxlength="20" value="' . $_POST['StockID' . $j] . '" /></td>
			<td><input type="text" name="StockQTY' . $j .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $j]) . '" /></td>
			<td><select name="ReasonReturn' . $j .'">';
	
	echo '<option value="">' . __('Select Reason') . '</option>';
	
	// Add hard-coded options first for new row
	$SelectedRequestedNew = (isset($_POST['ReasonReturn' . $j]) && $_POST['ReasonReturn' . $j] == 'REQUESTED') ? 'selected="selected"' : '';
	$SelectedOthersNew = (isset($_POST['ReasonReturn' . $j]) && $_POST['ReasonReturn' . $j] == 'OTHERS') ? 'selected="selected"' : '';
	echo '<option value="REQUESTED" ' . $SelectedRequestedNew . '>' . __('Requested by Shop Support Team') . '</option>';
	echo '<option value="OTHERS" ' . $SelectedOthersNew . '>' . __('Other reasons') . '</option>';
	
	// Get return reasons from database for new row
	$ReasonSQL = "SELECT servicecode,
					servicedescription
				FROM klservicetypes
				ORDER BY servicedescription";
	$ReasonResult = DB_query($ReasonSQL);
	
	while ($ReasonRow = DB_fetch_array($ReasonResult)) {
		$Selected = (isset($_POST['ReasonReturn' . $j]) && $_POST['ReasonReturn' . $j] == $ReasonRow['servicecode']) ? 'selected="selected"' : '';
		echo '<option value="' . $ReasonRow['servicecode'] . '" ' . $Selected . '>Service: ' . $ReasonRow['servicedescription'] . '</option>';
	}
	
	echo '</select></td>
		</tr>';
	$j++;

	echo '</tbody>
		</table>
		<br />
		<div class="centre">
		<input type="hidden" name="LinesCounter" value="'. $j .'" />
		<input type="submit" name="EnterMoreItems" value="'. __('Add More Items'). '" />
		<input type="submit" name="Submit" value="'. __('Create Transfer From Shop To Kantor'). '" />
		<br />
		</div>
		</div>
		</form>';
	include('includes/footer.php');
}

