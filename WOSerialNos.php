<?php

/* $Revision: 1.3 $ */
/*This page shows the serial numbers created for a works order 
 * - creating automatically from the last serial number counter in the stockmaster or by manual entry 
 * - If the item is lot controlled then the lot reference and the quantity in the lot can be entered 
 * - this updates the quantity on the work order. 
 * The work order quantity can only be modified by creating lots or serial numbers for controlled item work orders 
 * 
 * Need to allow adding serial numbers/batches and deleting batches/serial numbers
 */

$PageSecurity = 10;

include('includes/session.inc');
if ($_REQUEST['Serialised']==1){
	$title = _('Work Order Serial Numbers in Progress');
} else {
	$title = _('Work Order Batches in Progress');
}
include('includes/header.inc');

if (!isset($_REQUEST['WO']) OR $_REQUEST['WO']==''){
	prnMsg(_('This page must to be called from the work order entry screen'),'error');
	include('includes/footer.inc');
	exit;
}
if (isset($_GET['StockID'])){ //the page was called for the first time - get variables into $_POST array
	$StockID = $_GET['StockID'];
	$Description = $_GET['Description'];
	$WO = $_GET['WO'];
	$Serialised = $_GET['Serialised'];
	$NextSerialNo = $_GET['NextSerialNo'];
} else {
	$StockID = $_POST['StockID'];
	$Description = $_POST['Description'];
	$WO = $_POST['WO'];
	$Serialised = $_POST['Serialised'];
	$NextSerialNo = $_POST['NextSerialNo'];
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="" alt="">' . ' ' . _('For Work Order Number') . ' ' . $WO . ' ' . _('and output item') . ' ' . $StockID . ' - ' . $Description;

$DbgMsg = _('The SQL that failed was');

if (isset($_POST['AddControlledItems'])){
	if (isset($_POST['NumberToAdd'])){ // Must be adding serial numbers automatically
		if (!is_numeric($_POST['NumberToAdd'])){
			prnMsg(_('The quantity of controlled items to add was not numeric - a number is expected'),'error');
		} else {
			DB_Txn_Begin($db);
			/*Process the additional controlled items into woserialnos and update the quantity on the work order order in woitems*/
			$sql = "INSERT INTO woserialnos (stockid,
												wo,
												qualitytext,
												serialno) 
										VALUES ";
			$ValueLine = " ('" . $StockID . "', " . $WO . ",'' ,";
			for ($i=0;$i<$_POST['NumberToAdd'];$i++){
				$NextItemNumber = $NextSerialNo + $i;
				$result = DB_query("SELECT serialno FROM woserialnos
									WHERE wo=" . $WO . "
									AND stockid='" . $StockID ."'
									AND serialno='" . $NextItemNumber . "'",$db);
				if (DB_num_rows($result)!=0){
					$InputError=true;
					prnMsg($NextItemNumber . ' ' . _('is already entered on this work order'),'error');
				}
				$result = DB_query("SELECT serialno FROM stockserialitems
									WHERE serialno='" . $NextItemNumber . "'
									AND stockid='" . $StockID ."'",$db);
				if (DB_num_rows($result)!=0){
					$InputError=true;
					prnMsg($NextItemNumber . ' ' . _('has already been used for this item'),'error');
				}
				if (!$InputError){
					if ($i>0){
						$sql .= ',';
					}
					$sql .= $ValueLine . $NextItemNumber . "')";
				}
			}
			$NextSerialNo = $NextItemNumber + 1;
			$ErrMsg = _('Unable to add the serial numbers requested');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	
			// update the nextserialno in the stockmaster for the item
			$result = DB_query('UPDATE stockmaster 
								SET nextserialno=' . $NextSerialNo . " 
								WHERE stockid='" . $StockID . "'",$db);
			$result = DB_query('UPDATE woitems SET qtyreqd=qtyreqd+' . $_POST['NumberToAdd'] . "
								WHERE stockid='" . $StockID . "' 
								AND wo=" . $WO,$db,$ErrMsg,$DbgMsg,true);
			DB_Txn_Commit($db);
		}
	} // end Adding a number of serial numbers automatically
	  else { //adding just an individual entry
			$InputError = false;
			if (strlen($_POST['Reference'])==0){
				prnMsg(_('The batch or serial number reference has not been entered - a reference is required'),'error');
				$InputError=true;
			}
			if (!is_numeric($_POST['Quantity'])){
				prnMsg(_('The quantity for the batch must be numeric'),'error');
				$InputError=true;
			}
			$result = DB_query("SELECT serialno FROM woserialnos
								WHERE wo=" . $WO . "
								AND stockid='" . $StockID ."'
								AND serialno='" . $_POST['Reference'] . "'",$db);
			if (DB_num_rows($result)!=0){
				$InputError=true;
				prnMsg(_('The serial number or batch reference must be unique to the item - the reference entered is already entered on this work order'),'error');
			}
			$result = DB_query("SELECT serialno FROM stockserialitems
								WHERE serialno='" . $_POST['Reference'] . "'
								AND stockid='" . $StockID ."'",$db);
			if (DB_num_rows($result)!=0){
				$InputError=true;
				prnMsg(_('The serial number or batch reference must be unique to the item. The serial number/batch entered already exists'),'error');
			}
			if (!$InputError){
				DB_Txn_Begin($db);
				$ErrMsg = _('Could not add a new serial number/batch');
				$result = DB_query('UPDATE woitems 
									SET qtyreqd=qtyreqd+' . $_POST['Quantity'] . "
									WHERE stockid='" . $StockID . "' 
									AND wo=" . $WO,$db,$ErrMsg,$DbgMsg,true);
				$sql = "INSERT INTO woserialnos (stockid,
												 wo,
												 qualitytext,
												 quantity,
												 serialno)
									 VALUES ('" . $StockID . "', 
											  " . $WO . ",
											  '',
											 " . $_POST['Quantity'] .  ",
											 '" . $_POST['Reference'] . "')";
											 
				$ErrMsg = _('Unable to add the batch or serial number requested');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				DB_Txn_Commit($db);	
			}
	}								
}

if (isset($_GET['Delete'])){ //user hit delete link 

/*when serial numbers /lots received they are removed from the woserialnos table so no need to check if already received - they will only show here if they are in progress */
	$result = DB_query("DELETE FROM woserialnos 
						WHERE wo=" . $WO . " 
						AND stockid='" . $StockID . "'
						AND serialno='" . $_GET['Reference'] ."'",
						$db);
						
	$result = DB_query("UPDATE woitems SET qtyreqd=qtyreqd-" . $_GET['Quantity'] . "
						WHERE wo=" . $WO . "
						AND stockid = '" . $StockID . "'",$db);							
						
	prnMsg(_('The batch/serial number') . ' ' . $_GET['Reference'] . ' ' . _('has been deleted from this work order'),'info'); 
}

if (isset($_POST['UpdateItems'])){
//update the serial numbers and quantities and notes for each serial number or batch
		$WOQuantityTotal=0;
		for ($i=0;$i<$_POST['CountOfItems'];$i++){
			
				if (strlen($_POST['Reference' . $i])==0){
					prnMsg($_POST['OldReference' .$i] . ': ' , _('The new batch or serial number reference has not been entered - a reference is required'),'error');
					$InputError=true;
				}
				if (!is_numeric($_POST['Quantity' . $i])){
					prnMsg(_('The quantity for the batch must be numeric'),'error');
					$InputError=true;
				}
				if ($_POST['Reference' .$i] != $_POST['OldReference' .$i]){
					$result = DB_query("SELECT serialno FROM woserialnos
										WHERE wo=" . $WO . "
										AND stockid='" . $StockID ."'
										AND serialno='" . $_POST['Reference' . $i] . "'",$db);
					if (DB_num_rows($result)!=0){
						$InputError=true;
						prnMsg($_POST['Reference' .$i] . ': ' . _('The reference entered is already entered on this work order'),'error');
					}
					$result = DB_query("SELECT serialno FROM stockserialitems
										WHERE serialno='" . $_POST['Reference' .$i] . "'
										AND stockid='" . $StockID ."'",$db);
					if (DB_num_rows($result)!=0){
						$InputError=true;
						prnMsg($_POST['Reference' .$i] . ': ' . _('The serial number/batch entered already exists'),'error');
					}
				}
				if (!$InputError){
					$sql[] = "UPDATE woserialnos SET serialno='" . $_POST['Reference'.$i] . "',
														quantity=" . $_POST['Quantity'.$i] .",
														qualitytext='" . $_POST['Notes'.$i] . "'
											WHERE    wo=" . $WO . "
											AND stockid='" . $StockID . "'
											AND serialno='" . $_POST['OldReference'.$i] . "'";
					$WOQuantityTotal += $_POST['Quantity'.$i];
				} else {
					$WOQuantityTotal += $_POST['OldQuantity'.$i];
				}			
		}//end loop around all serial numbers/batches
		$ErrMsg = _('Could not update serial/batches on the work order');
		if (sizeof($sql)>0){
			$result = DB_Txn_Begin($db);
			foreach ($sql as $SQLStatement){
					$result = DB_query($SQLStatement,$db,$ErrMsg,$DbgMsg,true);
			}
			$result = DB_query("UPDATE woitems SET qtyreqd = " . $WOQuantityTotal . "
								WHERE wo = " .$WO . " 
								AND stockid='" . $StockID . "'", $db, $ErrMsg,$DbgMsg,true);
			$result = DB_Txn_Commit($db);
		}

}


echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="form">';

echo '<input type="hidden" name="StockID" value="' . $StockID . '">';
echo '<input type="hidden" name="Description" value="' . $Description . '">';
echo '<input type="hidden" name="WO" value="' . $WO . '">';
echo '<input type="hidden" name="Serialised" value="' . $Serialised . '">';
echo '<input type="hidden" name="NextSerialNo" value="' . $NextSerialNo . '">';

echo '<table>';
		
if ($Serialised==1 AND $NextSerialNo>0){
	echo '<tr><td>' . _('Add A Number of New Serial Numbers');
	echo ':</td>
		<td><input type="text" name="NumberToAdd" size=10 onKeyPress="return restrictToNumbers(this, event)" maxlength=10 value="1"></td>
		<td>' . _('Starting at') . ':</td><td align="right">' . $NextSerialNo . '</td>';
} else {
	//Need to allow entry of batch or serial number and its a batch a quantity too for individual entry
	if ($Serialised==1){
		echo '<tr><th></th><th>' . _('Serial No') . '</th></tr>
				<tr><td>';
		echo _('Add a single serial number');
	} else {
		echo '<tr><th></th><th>' . _('Batch/Lot Ref') . '</th><th>' . _('Quantity') . '</th></tr>
				<tr><td>';
		echo _('Add a single batch/lot number');
	}
	echo '<td><input type="text" name="Reference" maxlength="30" size="30"></td>';
	if ($Serialised==0){ //also need to add the quantity
		echo '<td><input type="text" name="Quantity" size=10 onKeyPress="return restrictToNumbers(this, event)" maxlength=10 value="1"></td>';
	} else { //it will be 1 for a serial item
		echo '<input type="hidden" name="Quantity" value=1>';
	}
}

echo '<td><input type="submit" name="AddControlledItems" value="' . _('Add') . '"></td></tr>
	</table>';
echo '<hr>';

$sql = "SELECT serialno, 
				quantity, 
				qualitytext 
		FROM woserialnos 
		WHERE wo=" . $WO . "
		AND stockid='" . $StockID . "'";

$ErrMsg = _('Could note get the work order serial/batch items');
$WOSerialNoResult = DB_query($sql,$db,$ErrMsg);

if (DB_num_rows($WOSerialNoResult)==0){
	prnMsg(_('There are no serial items or batches yet defined for this work order item. Create new items first'),'info');
} else {
	echo '<table>';
	if ($Serialised==1){
		$Header = '<tr><th>' . _('Serial No') . '</th><th>' . _('Notes') . '</th></tr>';
	} else {
		$Header = '<tr><th>' . _('Batch Ref') . '</th><th>' . _('Quantity') . '</th><th>' . _('Notes') . '</th></tr>';
	}
	echo $Header;
	$i = 0;
	$j = 0;
	while ($WOSNRow = DB_fetch_array($WOSerialNoResult)){
		
		if ($j==5){
			echo $Header;
			$j=0;
		}
		
		echo '<tr><td><input type="text" name="Reference' . $i .'" value="' . $WOSNRow['serialno'] . '"></td>';
		echo '<input type="hidden" name="OldReference' . $i . '" value="' . $WOSNRow['serialno'] . '">';
		if ($Serialised==0){
			echo '<td><input type="text" name="Quantity' . $i .'" value="' . $WOSNRow['quantity'] . '"</td>';
			echo '<input type="hidden" name="OldQuantity' . $i . '" value="' . $WOSNRow['quantity'] . '">';
		} else {
			echo '<input type="hidden" name="Quantity' . $i . '" value="1">';
		}
		echo '<td><textarea name="Notes' . $i .'" cols=60 rows=3>' . $WOSNRow['qualitytext'] .'</textarea></td>';
		echo '<td><a href="' . $_SESSION['PHP_SELF'] . '?' . SID . '&Delete=1&Reference=' . $WOSNRow['serialno'] . '&Quantity=' . $WOSNRow['quantity'] . '&WO=' . $WO . '&StockID=' . $StockID . '&Description=' . $Description . '&Serialised=' . $Serialised . '&NextSerialNo=' . $NextSerialNo . '">' . _('Delete') . '</td></tr>';
		$i++;
		$j++;		
	}
	
	echo '<input type="hidden" name="CountOfItems" value=' . $i . '>';
	
	if ($Serialised==0){
		echo '<tr><td align="center" colspan=3>';
	} else {
		echo '<tr><td align="center" colspan=2>';
	}
	echo '<input type="submit" name="UpdateItems" value="' . _('Update') . '"></td></tr>';
	echo '</table>';
	
} //end of if there are woserialno items defined

echo '<p/><a href="' . $rootpath . '/WorkOrderEntry.php?' . SID . '&WO=' . $WO . '">' . _('Back To Work Order') . ' ' . $WO .'</a>';
echo '</form>';

include('includes/footer.inc');

?>
