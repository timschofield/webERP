<?php

/////////////////////////////////////////////////////////////////////
//  ITEMS AND PRICES Table
/////////////////////////////////////////////////////////////////////

echo '<br />
	<table width="90%" cellpadding="2" colspan="7">
	<tr bgcolor="#800000">';

echo '<th>' . _('Item Code') . '</th>
	  <th>' . _('Item Description') . '</th>
	  <th>' . _('Quantity') . '</th>
	  <th>' . _('QOH') . '</th>
	  <th>' . _('Unit') . '</th>
	  <th>' . _('Price') . '</th>
	  <th>' . _('Discount') . '</th>
	  <th>' . _('Total') . '</th>
	  </tr>';
	  
$_SESSION['Items'.$identifier]->total = 0;
$_SESSION['Items'.$identifier]->totalVolume = 0;
$_SESSION['Items'.$identifier]->totalWeight = 0;
$TaxTotals = array();
$TaxGLCodes = array();
$TaxTotal =0;
$k =0;  //row colour counter
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

	$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
	$QtyOrdered = $OrderLine->Quantity;
	$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

	if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
		/*There is a stock deficiency in the stock location selected */
		$RowStarter = '<tr bgcolor="#EEAABB">';
	} elseif ($k==1){
		$RowStarter = '<tr class="OddTableRows">';
		$k=0;
	} else {
		$RowStarter = '<tr class="EvenTableRows">';
		$k=1;
	}

	echo $RowStarter;
	echo '<input type="hidden" name="POLine_' .	 $OrderLine->LineNumber . '" value="" />';
	echo '<input type="hidden" name="ItemDue_' .	 $OrderLine->LineNumber . '" value="'.$OrderLine->ItemDue.'" />';

	echo '<td>' . $OrderLine->StockID . '</td>
		<td>' . $OrderLine->ItemDescription . '</td>';

	echo '<td><input class="number" tabindex="2" type="text" name="Quantity_' . $OrderLine->LineNumber . '" size="6" maxlength="6" value="' . $OrderLine->Quantity . '" />';

	echo '</td>
		<td class="number">' . $OrderLine->QOHatLoc . '</td>
		<td>' . $OrderLine->Units . '</td>';

	echo '<input type="hidden" name="Price_' .	 $OrderLine->LineNumber . '" value="' . $OrderLine->Price . '" />';
	echo '<input type="hidden" name="Discount_' .	 $OrderLine->LineNumber . '" value="' . ($OrderLine->DiscountPercent * 100) . '" />';
	echo '<input type="hidden" name="GPPercent_' .	 $OrderLine->LineNumber . '" value="' . $OrderLine->GPPercent . '" />';

	echo '<td class="number">' . number_format($OrderLine->Price,0) . '</td>';
	echo '<td class="number">' . number_format($OrderLine->DiscountPercent *100,0) . '</td>';

	$LineDueDate = $OrderLine->ItemDue;
	if (!Is_Date($OrderLine->ItemDue)){
		$LineDueDate = date($_SESSION['DefaultDateFormat']);
		$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
	}
	$i=0; // initialise the number of taxes iterated through
	$TaxLineTotal =0; //initialise tax total for the line

	foreach ($OrderLine->Taxes AS $Tax) {
		if (empty($TaxTotals[$Tax->TaxAuthID])) {
			$TaxTotals[$Tax->TaxAuthID]=0;
		}
		if ($Tax->TaxOnTax ==1){
			$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
			$TaxLineTotal += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
		} else {
			$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $SubTotal);
			$TaxLineTotal += ($Tax->TaxRate * $SubTotal);
		}
		$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
	}

	$TaxTotal += $TaxLineTotal;
	$_SESSION['Items'.$identifier]->TaxTotals=$TaxTotals;
	$_SESSION['Items'.$identifier]->TaxGLCodes=$TaxGLCodes;
	echo '<td class="number">' . number_format($SubTotal + $TaxLineTotal ,0) . '</td>';
	echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID .'&amp;identifier='.$identifier . '&amp;Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . _('Delete') . '</a></td></tr>';

	if ($_SESSION['AllowOrderLineItemNarrative'] == 1){
		echo $RowStarter;
		echo '<td valign="top" colspan="11">' . _('Narrative') . ':<textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="100" rows="1">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td></tr>';
	} else {
		echo '<input type="hidden" name="Narrative" value="" />';
	}

	$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $SubTotal;
	$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
	$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

} /* end of loop around items */

echo '<tr class="TotalTableRows">
			<td colspan="6" class="numberTotal"><b>' . _('Total') . '</b></td>
			<td colspan="2" class="numberTotal">' . number_format(($_SESSION['Items'.$identifier]->total+$TaxTotal),0) . '</td>
					</tr>
	</table>';
echo '<input type="hidden" name="TaxTotal" value="'.$TaxTotal.'" />';

?>
