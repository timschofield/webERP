<?php
/* $Revision: 1.5 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php

*/

//we start with a batch or serial no header and need to display something for verification...
global $tableheader;

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

/*Display the batches already entered with quantities if not serialised */

echo '<TABLE><TR><TD valign=top><TABLE>';
echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

foreach ($LineItem->SerialItems as $Bundle){

	if ($RowCounter == 10){
		echo $tableheader;
		$RowCounter =0;
	} else {
		$RowCounter++;
	}

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	echo '<TD>' . $Bundle->BundleRef . '</TD>';

	if ($LineItem->Serialised==0){
		echo '<TD ALIGN=RIGHT>' . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</TD>';
	}

	echo '<TD><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $Bundle->BundleRef . '&StockID=' . $LineItem->StockID . '&LineNo=' . $LineNo .'">'. _('Delete'). '</A></TD></TR>';

	$TotalQuantity += $Bundle->BundleQty;
}


/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo '<TR><TD ALIGN=RIGHT><B>'. _('Total Quantity'). ': ' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</B></TD></TR>';
} else {
	echo '<TR><TD ALIGN=RIGHT><B>'. _('Total Quantity'). ':</B></TD><TD ALIGN=RIGHT><B>' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</B></TD></TR>';
}

/*Close off old table */
echo '</TABLE></TD><TD valign=top>';

/*Start a new table for the Serial/Batch ref input  in one column (as a sub table
then the multi select box for selection of existing bundle/serial nos for dispatch if applicable*/
//echo '<TABLE><TR><TD valign=TOP>';

/*in the first column add a table for the input of newies */
echo '<TABLE>';
echo $tableheader;


echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" METHOD="POST">
      <input type=hidden name=LineNo value="' . $LineNo . '">
      <input type=hidden name=StockID value="' . $StockID . '">
      <input type=hidden name=EntryType value="KEYED">';
if ( isset($_GET['EditControlled']) ) {
	$EditControlled = isset($_GET['EditControlled'])?$_GET['EditControlled']:false;
} elseif ( isset($_POST['EditControlled']) ){
	$EditControlled = isset($_POST['EditControlled'])?$_POST['EditControlled']:false;
}
$StartAddingAt = 0;
if ($EditControlled){
	foreach ($LineItem->SerialItems as $Bundle){

		echo '<TR><TD valign=top><input type=text name="SerialNo'. $StartAddingAt .'"
			value="'.$Bundle->BundleRef.'" size=21  maxlength=20></td>';

		/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
		into the form for entry of quantites manually */

		if ($LineItem->Serialised==1){
			echo '<input type=hidden name="Qty' . $StartAddingAt .'" Value=1></TR>';
		} else {
			echo '<TD><input type=text name="Qty' . $StartAddingAt .'" size=11
				value="'. number_format($Bundle->BundleQty, $LineItem->DecimalPlaces). '" maxlength=10></TR>';
		}

		$StartAddingAt++;
	}
}

for ($i=0;$i < 10;$i++){

	echo '<TR><TD valign=top><input type=text name="SerialNo'. ($StartAddingAt+$i) .'" size=21  maxlength=20></td>';

	/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
	into the form for entry of quantites manually */

	if ($LineItem->Serialised==1){
		echo '<input type=hidden name="Qty' . ($StartAddingAt+$i) .'" Value=1></TR>';
	} else {
		echo '<TD><input type=text name="Qty' . ($StartAddingAt+$i) .'" size=11  maxlength=10></TR>';
	}
}

echo '</table>';
echo '<br><center><INPUT TYPE=SUBMIT NAME="AddBatches" VALUE="'. _('Enter'). '"></center><BR>';
echo '</FORM></TD><TD valign=top>';
if ($ShowExisting){
	include('includes/InputSerialItemsExisting.php');
}
echo '</TD></TR></TABLE>'; /*end of nested table */
?>
