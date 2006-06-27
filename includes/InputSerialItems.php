<?php
/* $Revision: 1.11 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php
*/

include ('includes/Add_SerialItems.php');

/*Setup the Data Entry Types */
if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}
/*
        Entry Types:
             Keyed Mode: 'Qty' Rows of Input Fields. Upto X shown per page (100 max)
             Barcode Mode: Part Keyed, part not. 1st, 'Qty' of barcodes entered. Then extra data as/if
             necessary
             FileUpload Mode: File Uploaded must fulfill item requirements when parsed... no form based data
                 entry. 1-upload, 2-parse&validate, 3-bad>1 good>4, 4-import.
        switch the type we are updating from, w/ some rules...
                Qty < X   - Default to keyed
                X < Qty < Y - Default to barcode
                Y < Qty - Default to upload

        possibly override setting elsewhere.
*/
if (!isset($_POST['EntryType']) OR trim($_POST['EntryType']) == ''){
	if ($RecvQty <= 50) {
		$_POST['EntryType'] = 'KEYED';
	} //elseif ($RecvQty <= 50) { $EntryType = "BARCODE"; }
	else {
		$_POST['EntryType'] = 'FILE';
	}
}
	
$invalid_imports = 0;
$valid = true;

echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" enctype="multipart/form-data" >';
echo '<INPUT TYPE=HIDDEN NAME="LineNo" VALUE="' . $LineNo . '">';
echo '<INPUT TYPE=HIDDEN NAME="StockID" VALUE="'. $StockID. '">';
echo '<CENTER><TABLE BORDER=1><TR><TD>';
echo '<input type=radio name=EntryType onClick="submit();" ';
if ($_POST['EntryType']=='KEYED') {
	echo ' checked ';
}
echo 'value="KEYED">'. _('Keyed Entry');
echo '</TD>';

if ($LineItem->Serialised==1){
	echo '<TD>';
	echo '<input type=radio name=EntryType onClick="submit();" ';
	if ($_POST['EntryType']=='SEQUENCE') {
		echo ' checked ';
	}
	echo ' value="SEQUENCE">'. _('Sequential');
	echo '</TD>';
}

echo '<TD valign=bottom>';
echo '<input type=radio id="FileEntry" name=EntryType onClick="submit();" ';
if ($_POST['EntryType']=='FILE') {
	echo ' checked ';
}
echo ' value="FILE">'. _('File Upload');
echo '&nbsp; <input type="file" name="ImportFile" onClick="document.getElementById(\'FileEntry\').checked=true;" >';
echo '</TD></TR><TR><TD ALIGN=CENTER COLSPAN=3>';
echo '<input type=submit value="'. _('Set Entry Type'). ':">';
echo '</TD></TR></TABLE>';
echo '</FORM></CENTER>';

global $tableheader;
/* Link to clear the list and start from scratch */
$EditLink =  '<br><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&EditControlled=true&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .'">'. _('Edit'). '</a> | ';
$RemoveLink = '<A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . '&DELETEALL=YES&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .'">'. _('Remove All'). '</a><br>';
if ($LineItem->Serialised==1){
	$tableheader .= '<TR>
			<TD class=tableheader>'. _('Serial No').'</TD>
			</TR>';
} else {
	$tableheader = '<TR>
			<TD class=tableheader>'. _('Batch/Roll/Bundle'). ' #</TD>
			<TD class=tableheader>'. _('Quantity'). '</TD>
			</TR>';
}

echo $EditLink . $RemoveLink;
echo '<TABLE><TR><TD>';
if ($_POST['EntryType'] == 'FILE'){
	include('includes/InputSerialItemsFile.php');
} elseif ($_POST['EntryType'] == 'SEQUENCE'){
        include('includes/InputSerialItemsSequential.php');
} else { /*KEYED or BARCODE */
	include('includes/InputSerialItemsKeyed.php');
}
echo '</TD></TR></TABLE>';
?>
