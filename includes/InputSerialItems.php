<?php
/* $Revision: 1.3 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
*/

if ($LineItem->Serialised==1){
	echo "<BR>Read From a file:<input type=file name='ImportFile'><BR>";
}

echo "<TABLE>";

if ($LineItem->Serialised==1){
	$tableheader .= "<TR><TD class='tableheader'>Serial No</TD></TR>";
} else {
	$tableheader = "<TR><TD class='tableheader'>Batch/Roll/Bundle#</TD><TD class='tableheader'>Quantity</TD></TR>";
}

echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

/*Display the batches already entered with quantities if not serialised */
foreach ($LineItem->SerialItems as $Bundle){

	if ($RowCounter == 18){
		echo $tableheader;
		$RowCounter =0;
	} else {
		$RowCounter++;
	}

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	echo "<TD>" . $Bundle->BundleRef . "</TD>";

	if ($LineItem->Serialised==0){
		echo "<TD ALIGN=RIGHT>" . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . "</TD>";
	}

	echo "<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $Bundle->BundleRef . "&StockID=" . $StockID . "'>Delete</A></TD></TR>";

	$TotalQuantity += $Bundle->BundleQty;
}



/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo "<TR><TD ALIGN=RIGHT><B>Total Quantity: " . number_format($TotalQuantity,$LineItem->DecimalPlaces) . "</B></TD></TR>";
	echo "<TR><TD><HR></TD></TR>";
} else {
	echo "<TR><TD ALIGN=RIGHT><B>Total Quantity:</B></TD><TD ALIGN=RIGHT><B>" . number_format($TotalQuantity,$LineItem->DecimalPlaces) . "</B></TD></TR>";
	echo "<TR><TD COLSPAN=2><HR></TD></TR>";
}

echo "</TABLE><HR>";
echo "<TABLE><TR><TD>";

echo "<TABLE>"; /*nested table */

echo $tableheader;

/*Now allow new entries in text input boxes */
for ($i=0;$i < 10;$i++){

	echo "<TR><td><input type=text name='SerialNo" . $i ."' size=21  maxlength=20></td>";

	/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
	into the form for entry of quantites manually */

	if ($LineItem->Serialised==1){
		echo "<input type=hidden name='Qty" . $i ."' Value=1></TR>";
	} else {
		echo "<TD><input type=text name='Qty" . $i ."' size=11  maxlength=10></TR>";
	}
}

echo "</table></TD>"; /*end of nested table */

?>
