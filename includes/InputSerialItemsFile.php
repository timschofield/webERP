<?php

/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php
*/

// we start with a batch or serial no header and need to display something for verification...
global $Tableheader;
global $LineItem;
//$LineNo = initPvar('LineNo', $LineNo);
if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

echo '<div class="centre">';
echo '<table class="selection">';
echo $Tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

/*Display the batches already entered with quantities if not serialised */
foreach ($LineItem->SerialItems as $Bundle){

	$RowCounter++;
	//only show 1st 10 lines
	if ($RowCounter < 10){

		echo '<tr class="striped_row">
				<td>' . $Bundle->BundleRef . '</td>';

		if ($LineItem->Serialised==0){
			echo '<td class="number">' . locale_number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</td>';
		}
	}

	$TotalQuantity += $Bundle->BundleQty;
}


/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo '<tr>
			<td class="number"><b>' .   __('Total Quantity'). ': ' . locale_number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td>
		</tr>
		<tr>
			<td><hr /></td>
		</tr>';
} else {
	echo '<tr>
			<td class="number"><b>' .  __('Total Quantity'). ':</b></td>
			<td class="number"><b>' . locale_number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td>
		</tr>
		<tr>
			<td colspan="2"><hr /></td>
		</tr>';
}

echo '</table><br />';

//DISPLAY FILE INFO
// do some inits & error checks...
$ShowFileInfo = false;
if (!isset($_SESSION['CurImportFile']) ){
		$_SESSION['CurImportFile'] = '';
		$LineItem->SerialItemsValid=false;
}
if ((isset($_FILES['ImportFile']) AND $_FILES['ImportFile']['name'] == '') AND $_SESSION['CurImportFile'] == ''){
	$Msg = __('Please Choose a file and then click Set Entry Type to upload a file for import');
	prnMsg($Msg);
	$LineItem->SerialItemsValid=false;
	echo '</td></tr></table>';
	include('includes/footer.php');
	exit();
}
if ((isset($_FILES['ImportFile']) AND $_FILES['ImportFile']['error'] != '') AND !isset($_SESSION['CurImportFile'])){
		echo __('There was a problem with the uploaded file') . '. ' . __('We received').':<br />' .
				 __('Name').':'.$_FILES['ImportFile']['name'] . '<br />' .
				 __('Size').':'.locale_number_format($_FILES['ImportFile']['size']/1024,2).'kb<br />' .
				 __('Type').':'.$_FILES['ImportFile']['type'] . '<br />';
		echo '<br />' . __('Error was').' '.$_FILES['ImportFile']['error'] . '<br />';
		$LineItem->SerialItemsValid=false;
		echo '</td></tr></table><br />';
		include('includes/footer.php');
		exit();
} elseif ((isset($_FILES['ImportFile']) AND $_FILES['ImportFile']['name']!='')){
	//User has uploaded importfile. reset items, then just 'get hold' of it for later.

	$LineItem->SerialItems=array();
	$LineItem->SerialItemsValid=false;
	$_SESSION['CurImportFile']['Processed']=false;
	$_SESSION['CurImportFile'] = $_FILES['ImportFile'];
	$_SESSION['CurImportFile']['tmp_name'] = $PathPrefix . '/' . $_SESSION['reports_dir'] . '/'.$LineItem->StockID.'_'.$LineNo.'_'.uniqid(4);
	if (!move_uploaded_file($_FILES['ImportFile']['tmp_name'], $_SESSION['CurImportFile']['tmp_name'])){
		prnMsg(__('Error moving temporary file') . '. ' . __('Please check your configuration'),'error' );
		$LineItem->SerialItemsValid=false;
		echo '</td></tr></table>';
		include('includes/footer.php');
		exit();
	}
	$_SESSION['CurImportFile']['Processed']=false;
	if ($_FILES['ImportFile']['name']!=''){
		prnMsg( __('Successfully received'), 'success');
		$ShowFileInfo = true;
	}
} elseif (isset($_SESSION['CurImportFile']) and $_SESSION['CurImportFile']['Processed'] ) {
	//file exists, some action performed...
	$ShowFileInfo = true;
} elseif ($LineItem->SerialItemsValid and $_SESSION['CurImportFile']['Processed']){
	$ShowFileInfo = true;
}
if ($ShowFileInfo){
	/********************************************
	  Display file info for visual verification
	********************************************/
	$File=$_SESSION['CurImportFile']['tmp_name'];
	$TotalLines = 0;
	$Handle = fopen($File, 'r');
	while(!feof($Handle)){
		$Line = fgets($Handle);
		$TotalLines++;
	}
	fclose($Handle);
	if ($Line=='') {
		$TotalLines--;
	}

	echo '<br /><table class="selection" width="33%">';
	echo '<tr>
			<td>' . __('Name').':</td>
			<td>' . $_SESSION['CurImportFile']['name'] . '</td>
		</tr>
		<tr>
			<td>' .  __('Size') .':</td>
			<td>' . locale_number_format($_SESSION['CurImportFile']['size']/1024,4) . 'kb</td>
		</tr>
		<tr>
			<td>' .  __('Type') .':</td>
			<td>' . $_SESSION['CurImportFile']['type'] . '</td>
		</tr>
		<tr>
			<td>' .  __('TempName') .':</td>
			<td>' . basename($_SESSION['CurImportFile']['tmp_name']) . '</td>
		</tr>
		<tr>
			<td>' .  __('Status') .':</td>
			<td>' . ($InvalidImports==0 ? getMsg(__('Valid'),'success'):getMsg(__('Invalid'),'error')) . '</td>
		</tr>
	</table><br />';
	$FileName = $_SESSION['CurImportFile']['tmp_name'];
}

if ($InvalidImports>0 AND !$_SESSION['CurImportFile']['Processed']){
		// IF all items are not valid, show the raw first 10 lines of the file. maybe it will help.

	echo '<br /><form method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="submit" name="ValidateFile" value="' . __('Validate File') . '" />
			<input type="hidden" name="LineNo" value="' . $LineNo . '" />
			<input type="hidden" name="StockID" value="' . $StockID . '" />
			<input type="hidden" name="EntryType" value="FILE" />
			</form>
			<p>' .  __('1st 10 Lines of File'). '....</p>
			<hr width="15%">
		<pre>';

	echo $Contents;

	echo '</pre>';

} else {
	echo '<br /><form method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<button type="submit" name="ValidateFile">' . __('Update Batches') . '</button>
			<input type="hidden" name="LineNo" value="' . $LineNo . '" />
			<input type="hidden" name="InvalidImports" value="' . $InvalidImports . '" />
			<input type="hidden" name="StockID" value="' . $StockID . '" />
			<input type="hidden" name="EntryType" value="FILE" /><br />';
		//Otherwise we have all valid records. show the first (100)  for visual verification.
	echo '<br /><table class="selection">
			<tr>
				<th class="header" colspan="2">' . __('Below are the 1st 100 records as parsed') . '</th>
			</tr>
			<tr>
				<th>' . __('Batch Number') . '</th>
				<th>' . __('Quantity') . '</th>
			</tr>';
	$i=0;
	foreach($LineItem->SerialItems as $SItem){
		echo '<tr>
				<td>' . $SItem->BundleRef . '</td>
				<td class="number">' . locale_number_format($SItem->BundleQty, $LineItem->DecimalPlaces) . '</td>
			</tr>';
		$i++;
		if ($i == 100) {
			break;
		}
	}
	echo '</table></form>';
}
