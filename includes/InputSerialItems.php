<?php
/* $Revision: 1.6 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php

*/
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

if ($_POST['EntryType']== ""){
	if ($RecvQty <= 50) {
		$_POST['EntryType'] = "KEYED";
	} //elseif ($RecvQty <= 50) { $EntryType = "BARCODE"; }
	else {
		$_POST['EntryType'] = "FILE";
	}
}

$invalid_imports = 0;
$valid = true;


echo "<INPUT TYPE=HIDDEN NAME='LineNo' VALUE=" . $LineNo . ">";

echo "<CENTER><TABLE BORDER=1><TR><TD>";
echo "<input type=radio name=EntryType ";
if ($_POST['EntryType']=="KEYED") {
	echo "checked ";
}
echo "value='KEYED' valign=texttop>Keyed Entry";
echo "</TD><TD>";
echo "<input type=radio name=EntryType";
if ($_POST['EntryType']=="BARCODE") {
	echo "checked";
}
echo " value='BARCODE'>Barcode Entry";
echo "</TD><TD>";
echo "<input type=radio name=EntryType";
if ($_POST['EntryType']=="FILE") {
	echo "checked";
}
echo " value='FILE'>File Upload";
echo "<input type=file name='ImportFile'>";
echo "</TD></TR><TR><TD ALIGN=CENTER COLSPAN=3>";
echo "<input type=submit value='Set Entry Type:'>";
echo "</TD></TR></TABLE>";

global $tableheader;

if ($LineItem->Serialised==1){
	$tableheader .= "<TR>
			<TD class='tableheader'>Serial No</TD>
			</TR>";
} else {
	$tableheader = "<TR>
			<TD class='tableheader'>Batch/Roll/Bundle#</TD>
			<TD class='tableheader'>Quantity</TD>
			</TR>";
}

if ($_POST['EntryType'] == "FILE"){
	include("includes/InputSerialItemsFile.php");
} else { /*KEYED or BARCODE */
	include("includes/InputSerialItemsKeyed.php");
}
?>
