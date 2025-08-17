<?php

/////////////////////////////////////////////////////////////////////
//  ITEMS AND PRICES Table
/////////////////////////////////////////////////////////////////////

echo '<br />
	<table width="90%" cellpadding="2" colspan="9">';

echo '<thead bgcolor="#800000">
		<th>' . __('Item Code') . '</th>
		<th>' . __('Item Description') . '</th>
		<th>' . __('Quantity') . '</th>
		<th>' . __('QOH') . '</th>
		<th>' . __('Unit') . '</th>
		<th>' . __('Packaging') . '</th>
		<th>' . __('Price') . '</th>
		<th>' . __('Discount') . '</th>
		<th>' . __('Total') . '</th>
	</thead>
	<tbody>';
	  
$_SESSION['Items'.$identifier]->total = 0;
$_SESSION['Items'.$identifier]->totalVolume = 0;
$_SESSION['Items'.$identifier]->totalWeight = 0;
$TaxTotals = array();
$TaxGLCodes = array();
$TaxTotal = 0;
$TotalNumberOfItems = 0;

foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

	$SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
	$QtyOrdered = $OrderLine->Quantity;
	$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;
	$TotalNumberOfItems = $TotalNumberOfItems + $OrderLine->Quantity;
	$LineDueDate = $OrderLine->ItemDue;

	if (!Is_Date($OrderLine->ItemDue)){
		$LineDueDate = date($_SESSION['DefaultDateFormat']);
		$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue = $LineDueDate;
	}
	$i = 0; // initialise the number of taxes iterated through
	$TaxLineTotal = 0; //initialise tax total for the line

	foreach ($OrderLine->Taxes AS $Tax) {
		if (empty($TaxTotals[$Tax->TaxAuthID])) {
			$TaxTotals[$Tax->TaxAuthID] = 0;
		}
		if ($Tax->TaxOnTax == 1){
			$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
			$TaxLineTotal += ($Tax->TaxRate * ($SubTotal + $TaxLineTotal));
		} else {
			$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $SubTotal);
			$TaxLineTotal += ($Tax->TaxRate * $SubTotal);
		}
		$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
	}

	$TaxTotal += $TaxLineTotal;
	$_SESSION['Items'.$identifier]->TaxTotals = $TaxTotals;
	$_SESSION['Items'.$identifier]->TaxGLCodes = $TaxGLCodes;
	$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $SubTotal;
	$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
	$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

	if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag == 'B' OR $OrderLine->MBflag == 'M')) {
		/*There is a stock deficiency in the stock location selected, show it red */
		$RowStarter = '<tr bgcolor="#EEAABB">';
	} else {
		$RowStarter = '<tr class="striped_row">';
	}

	echo $RowStarter;
	echo '<input type="hidden" name="POLine_' . $OrderLine->LineNumber . '" value="" />';
	echo '<input type="hidden" name="ItemDue_' . $OrderLine->LineNumber . '" value="' . $OrderLine->ItemDue . '" />';
	echo '<input type="hidden" name="Price_' . $OrderLine->LineNumber . '" value="' . $OrderLine->Price . '" />';
	echo '<input type="hidden" name="Discount_' . $OrderLine->LineNumber . '" value="' . ($OrderLine->DiscountPercent * 100) . '" />';
	echo '<input type="hidden" name="GPPercent_' . $OrderLine->LineNumber . '" value="' . $OrderLine->GPPercent . '" />';
	echo '<input type="hidden" name="Narrative" value="" />';

	echo '<td>' . $OrderLine->StockID . '</td>
		<td>' . $OrderLine->ItemDescription . '</td>';

	echo '<td><input class="number" tabindex="2" type="text" name="Quantity_' . $OrderLine->LineNumber . '" size="6" maxlength="6" value="' . $OrderLine->Quantity . '" /></td>';

	echo '<td class="number">' . $OrderLine->QOHatLoc . '</td>
		<td>' . $OrderLine->Units . '</td>
		<td>' . GetItemPackagingDescription($OrderLine->StockID) . '</td>';

	echo '<td class="number">' . number_format($OrderLine->Price, 0) . '</td>';
	echo '<td class="number">' . number_format($OrderLine->DiscountPercent * 100, 0) . '</td>';

	echo '<td class="number">' . number_format($SubTotal + $TaxLineTotal, 0) . '</td>';
	echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&amp;identifier=' . $identifier . '&amp;Delete=' . 
		$OrderLine->LineNumber . '" onclick="return confirm(\'' . __('Are You Sure?') . '\');">' . __('Delete') . '</a></td></tr>';

} /* end of loop around items */

echo '</tbody>
	</tfooter>';
echo '<tr class="TotalTableRows">
			<td colspan="6"></td>
			<td colspan="2"><b>' . __('Total') . '</b></td>
			<td colspan="2" class="numberTotal">' . number_format(($_SESSION['Items'.$identifier]->total + $TaxTotal), 0) . '</td>
	</tr>';
echo '<tr class="TotalTableRows">
			<td colspan="6"></td>
			<td colspan="2"><b>' . __('Number of Items') . '</b></td>
			<td colspan="2" class="numberTotal">' . number_format($TotalNumberOfItems, 0) . '</td>
	</tr>
	</tfooter>
	</table>';

echo '<input type="hidden" name="TaxTotal" value="' . $TaxTotal . '" />';

