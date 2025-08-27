<?php

/// @todo move to after session.php inclusion, unless there are side effects
/* Functions to get the GL codes to post the transaction to */
include('includes/GetSalesTransGLCodes.php');
/* defines the structure of the data required to hold the transaction as a session variable */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'ARTransactions';
$BookMark = 'CreditNotes';
$Title = __('Credit An Invoice');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/CommissionFunctions.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other credit entry sessions on the same machine  */
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

if (!isset($_GET['InvoiceNumber']) and !$_SESSION['ProcessingCredit']) {
	/* This page can only be called with an invoice number for crediting*/
	prnMsg(__('This page can only be opened if an invoice has been selected for crediting') . '. ' . __('Please select an invoice first') . ' - ' . __('from the customer inquiry screen click the link to credit an invoice'), 'info');
	include('includes/footer.php');
	exit();

} elseif (isset($_GET['InvoiceNumber'])) {
	$_GET['InvoiceNumber'] = intval($_GET['InvoiceNumber']);
	unset($_SESSION['CreditItems' . $identifier]->LineItems);
	unset($_SESSION['CreditItems' . $identifier]);

	$_SESSION['ProcessingCredit'] = intval($_GET['InvoiceNumber']);
	$_SESSION['CreditItems' . $identifier] = new Cart;

	/*read in all the guff from the selected invoice into the Items cart	*/

	$InvoiceHeaderSQL = "SELECT DISTINCT
								debtortrans.id as transid,
								debtortrans.debtorno,
								debtorsmaster.name,
								debtortrans.branchcode,
								debtortrans.reference,
								debtortrans.invtext,
								debtortrans.order_,
								debtortrans.trandate,
								debtortrans.tpe,
								debtortrans.shipvia,
								debtortrans.ovfreight,
								debtortrans.rate AS currency_rate,
								debtorsmaster.currcode,
								custbranch.defaultlocation,
								custbranch.taxgroupid,
								salesorders.salesperson,
								stockmoves.loccode,
								locations.taxprovinceid,
								currencies.decimalplaces
							FROM debtortrans INNER JOIN debtorsmaster
							ON debtortrans.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
							ON debtortrans.branchcode = custbranch.branchcode
							AND debtortrans.debtorno = custbranch.debtorno
							INNER JOIN salesorders
							ON debtortrans.order_ = salesorders.orderno
							INNER JOIN currencies
							ON debtorsmaster.currcode = currencies.currabrev
							INNER JOIN stockmoves
							ON stockmoves.transno=debtortrans.transno
							AND stockmoves.type=debtortrans.type
							INNER JOIN locations ON
							stockmoves.loccode = locations.loccode
							INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
							WHERE debtortrans.transno = '" . intval($_GET['InvoiceNumber']) . "'
							AND stockmoves.type=10";

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$ErrMsg = __('A credit cannot be produced for the selected invoice') . '. ' . __('The invoice details cannot be retrieved because');
	$GetInvHdrResult = DB_query($InvoiceHeaderSQL, $ErrMsg);

	if (DB_num_rows($GetInvHdrResult) == 1) {

		$MyRow = DB_fetch_array($GetInvHdrResult);

		/*CustomerID variable registered by header.php */
		$_SESSION['CreditItems' . $identifier]->DebtorNo = $MyRow['debtorno'];
		$_SESSION['CreditItems' . $identifier]->TransID = $MyRow['transid'];
		$_SESSION['CreditItems' . $identifier]->Branch = $MyRow['branchcode'];
		$_SESSION['CreditItems' . $identifier]->CustomerName = $MyRow['name'];
		$_SESSION['CreditItems' . $identifier]->CustRef = $MyRow['reference'];
		$_SESSION['CreditItems' . $identifier]->Comments = $MyRow['invtext'];
		$_SESSION['CreditItems' . $identifier]->DefaultSalesType = $MyRow['tpe'];
		$_SESSION['CreditItems' . $identifier]->DefaultCurrency = $MyRow['currcode'];
		$_SESSION['CreditItems' . $identifier]->Location = $MyRow['loccode'];
		$_SESSION['Old_FreightCost'] = $MyRow['ovfreight'];
		$_SESSION['CurrencyRate'] = $MyRow['currency_rate'];
		$_SESSION['CreditItems' . $identifier]->OrderNo = $MyRow['order_'];
		$_SESSION['CreditItems' . $identifier]->ShipVia = $MyRow['shipvia'];
		$_SESSION['CreditItems' . $identifier]->TaxGroup = $MyRow['taxgroupid'];
		$_SESSION['CreditItems' . $identifier]->FreightCost = $MyRow['ovfreight'];
		$_SESSION['CreditItems' . $identifier]->DispatchTaxProvince = $MyRow['taxprovinceid'];
		$_SESSION['CreditItems' . $identifier]->GetFreightTaxes();
		$_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
		$_SESSION['CreditItems' . $identifier]->SalesPerson = $MyRow['salesperson'];

		DB_free_result($GetInvHdrResult);

		/*now populate the line items array with the stock movement records for the invoice*/

		$LineItemsSQL = "SELECT stockmoves.stkmoveno,
								stockmoves.stockid,
								stockmaster.description,
								stockmaster.longdescription,
								stockmaster.volume,
								stockmaster.grossweight,
								stockmaster.mbflag,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.decimalplaces,
								stockmaster.taxcatid,
								stockmaster.units,
								stockmaster.discountcategory,
								(stockmoves.price * " . $_SESSION['CurrencyRate'] . ") AS price, -
								stockmoves.qty as quantity,
								stockmoves.discountpercent,
								stockmoves.trandate,
								stockmaster.actualcost AS standardcost,
								stockmoves.narrative
							FROM stockmoves, stockmaster
							WHERE stockmoves.stockid = stockmaster.stockid
							AND stockmoves.transno ='" . $_GET['InvoiceNumber'] . "'
							AND stockmoves.type=10
							AND stockmoves.show_on_inv_crds=1";

		$ErrMsg = __('This invoice can not be credited using this program') . '. ' . __('A manual credit note will need to be prepared') . '. ' . __('The line items of the order cannot be retrieved because');

		$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);

		if (DB_num_rows($LineItemsResult) > 0) {

			while ($MyRow = DB_fetch_array($LineItemsResult)) {

				$LineNumber = $_SESSION['CreditItems' . $identifier]->LineCounter;

				$_SESSION['CreditItems' . $identifier]->add_to_cart($MyRow['stockid'],
																	$MyRow['quantity'],
																	$MyRow['description'],
																	$MyRow['longdescription'],
																	$MyRow['price'],
																	$MyRow['discountpercent'],
																	$MyRow['units'],
																	$MyRow['volume'],
																	$MyRow['grossweight'],
																	0,
																	$MyRow['mbflag'],
																	$MyRow['trandate'],
																	0,
																	$MyRow['discountcategory'],
																	$MyRow['controlled'],
																	$MyRow['serialised'],
																	$MyRow['decimalplaces'],
																	str_replace("\\r\\n", " ", $MyRow['narrative']),
																	'No',
																	-1,
																	$MyRow['taxcatid'],
																	'',
																	'',
																	'',
																	$MyRow['standardcost']);

				$_SESSION['CreditItems' . $identifier]->GetExistingTaxes($LineNumber, $MyRow['stkmoveno']);

				if ($MyRow['controlled'] == 1) { /* Populate the SerialItems array too*/

					$SQL = "SELECT 	serialno,
									moveqty
							FROM stockserialmoves
							WHERE stockmoveno='" . $MyRow['stkmoveno'] . "'
							AND stockid = '" . $MyRow['stockid'] . "'";

					$ErrMsg = __('This invoice can not be credited using this program') . '. ' . __('A manual credit note will need to be prepared') . '. ' . __('The line item') . ' ' . $MyRow['stockid'] . ' ' . __('is controlled but the serial numbers or batch numbers could not be retrieved because');
					$SerialItemsResult = DB_query($SQL, $ErrMsg);

					while ($SerialItemsRow = DB_fetch_array($SerialItemsResult)) {
						$_SESSION['CreditItems' . $identifier]->LineItems[$LineNumber]->SerialItems[$SerialItemsRow['serialno']] = new SerialItem($SerialItemsRow['serialno'], -$SerialItemsRow['moveqty']);
						$_SESSION['CreditItems' . $identifier]->LineItems[$LineNumber]->QtyDispatched-= $SerialItemsRow['moveqty'];
					}
				} /* end if the item is a controlled item */
			} /* loop thro line items from stock movement records */

		} else { /* there are no stock movement records created for that invoice */

			echo '<div class="centre"><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a></div>';
			prnMsg(__('There are no line items that were retrieved for this invoice') . '. ' . __('The automatic credit program can not create a credit note from this invoice'), 'warn');
			include('includes/footer.php');
			exit();
		} //end of checks on returned data set
		DB_free_result($LineItemsResult);
	} else {
		prnMsg(__('This invoice can not be credited using the automatic facility') . '<br />' . __('CRITICAL ERROR') . ': ' . __('Please report that a duplicate DebtorTrans header record was found for invoice') . ' ' . $SESSION['ProcessingCredit'], 'warn');
		include('includes/footer.php');
		exit();
	} //valid invoice record returned from the entered invoice number

}

if (isset($_POST['Location'])) {
	$_SESSION['CreditItems' . $identifier]->Location = $_POST['Location'];

	$NewDispatchTaxProvResult = DB_query("SELECT taxprovinceid FROM locations WHERE loccode='" . $_POST['Location'] . "'");
	$MyRow = DB_fetch_array($NewDispatchTaxProvResult);

	$_SESSION['CreditItems' . $identifier]->DispatchTaxProvince = $MyRow['taxprovinceid'];

	$TotalQtyCredited = 0;

	foreach ($_SESSION['CreditItems' . $identifier]->LineItems as $LineItem) {
		$_SESSION['CreditItems' . $identifier]->GetTaxes($LineItem->LineNumber);
		$TotalQtyCredited+= $LineItem->QtyDispatched;
	}
}

if (isset($_POST['ChargeFreightCost'])) {
	$_SESSION['CreditItems' . $identifier]->FreightCost = abs(filter_number_format($_POST['ChargeFreightCost']));
	if (($TotalQtyCredited + abs($_POST['ChargeFreightCost'])) <= 0) {
		prnMsg(__('There are no item quantity or freight charge input'), 'error');
		if (isset($_POST['ProcessCredit'])) {
			unset($_POST['ProcessCredit']);
		}
	}

}
if ($_SESSION['SalesmanLogin'] == '') {
	if (isset($_POST['SalesPerson'])) {
		$_SESSION['CreditItems' . $identifier]->SalesPerson = $_POST['SalesPerson'];
	}
}
foreach ($_SESSION['CreditItems' . $identifier]->FreightTaxes as $FreightTaxKey => $FreightTaxLine) {
	if (is_numeric(filter_number_format($_POST['FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder]))) {
		$_SESSION['CreditItems' . $identifier]->FreightTaxes[$FreightTaxKey]->TaxRate = filter_number_format($_POST['FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder]) / 100;
	}
}

if ($_SESSION['CreditItems' . $identifier]->ItemsOrdered > 0 or isset($_POST['NewItem'])) {

	if (isset($_GET['Delete'])) {
		$_SESSION['CreditItems' . $identifier]->remove_from_cart($_GET['Delete']);
	}

	foreach ($_SESSION['CreditItems' . $identifier]->LineItems as $LineItem) {

		if (isset($_POST['Quantity_' . $LineItem->LineNumber])) {

			$Narrative = $_POST['Narrative_' . $LineItem->LineNumber];
			$Quantity = filter_number_format($_POST['Quantity_' . $LineItem->LineNumber]);
			$Price = filter_number_format($_POST['Price_' . $LineItem->LineNumber]);
			$DiscountPercentage = filter_number_format($_POST['Discount_' . $LineItem->LineNumber]);

			if ($Quantity < 0 or $Price < 0 or $DiscountPercentage > 100 or $DiscountPercentage < 0) {
				prnMsg(__('The item could not be updated because you are attempting to set the quantity credited to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'), 'error');
			} else {
				$_SESSION['CreditItems' . $identifier]->LineItems[$LineItem->LineNumber]->QtyDispatched = $Quantity;
				$_SESSION['CreditItems' . $identifier]->LineItems[$LineItem->LineNumber]->Price = $Price;
				$_SESSION['CreditItems' . $identifier]->LineItems[$LineItem->LineNumber]->DiscountPercent = ($DiscountPercentage / 100);
				$_SESSION['CreditItems' . $identifier]->LineItems[$LineItem->LineNumber]->Narrative = $Narrative;
			}
			foreach ($LineItem->Taxes as $TaxKey => $TaxLine) {
				if (is_numeric(filter_number_format($_POST[$LineItem->LineNumber . $TaxLine->TaxCalculationOrder . '_TaxRate']))) {
					$_SESSION['CreditItems' . $identifier]->LineItems[$LineItem->LineNumber]->Taxes[$TaxKey]->TaxRate = filter_number_format($_POST[$LineItem->LineNumber . $TaxLine->TaxCalculationOrder . '_TaxRate']) / 100;
				}
			}
		}
	}
}

/* Always display credit quantities
 NB QtyDispatched in the LineItems array is used for the quantity to credit */
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/credit.png" title="' . __('Search') . '" alt="" />' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['ProcessCredit'])) {

	echo '<table cellpadding="2" class="selection">';
	echo '<tr><th colspan="13">';
	echo '<div class="centre"><b>' . __('Credit Invoice') . ' ' . $_SESSION['ProcessingCredit'] . '</b>
		<b>' . ' - ' . $_SESSION['CreditItems' . $identifier]->CustomerName . '</b>
		 - ' . __('Credit Note amounts stated in') . ' ' . $_SESSION['CreditItems' . $identifier]->DefaultCurrency . '</div>';
	echo '</th></tr>';
	echo '<tr>
			<th>' . __('Item Code') . '</th>
			<th>' . __('Item Description') . '</th>
			<th>' . __('Invoiced') . '</th>
			<th>' . __('Units') . '</th>
			<th>' . __('Credit') . '<br />' . __('Quantity') . '</th>
			<th>' . __('Price') . '</th>
			<th>' . __('Discount Rate') . '</th>
			<th>' . __('Total') . '<br />' . __('Excl Tax') . '</th>
			<th>' . __('Tax Authority') . '</th>
			<th>' . __('Tax Rate') . '</th>
			<th>' . __('Tax Amount') . '</th>
			<th>' . __('Total') . '<br />' . __('Incl Tax') . '</th>
			<th>&nbsp;</th>
		</tr>';
}
$_SESSION['CreditItems' . $identifier]->total = 0;
$_SESSION['CreditItems' . $identifier]->totalVolume = 0;
$_SESSION['CreditItems' . $identifier]->totalWeight = 0;

$TaxTotals = array();
$TaxGLCodes = array();
$TaxTotal = 0;

/*show the line items on the invoice with the quantity to credit and price being available for modification */

$j = 0; //row counter
$TabIndex = 1; // Tab order of an element when the "tab" button is used for navigating.
foreach ($_SESSION['CreditItems' . $identifier]->LineItems as $LnItm) {

	$LineTotal = $LnItm->QtyDispatched * $LnItm->Price * (1 - $LnItm->DiscountPercent);
	$_SESSION['CreditItems' . $identifier]->total+= $LineTotal;
	$_SESSION['CreditItems' . $identifier]->totalVolume+= ($LnItm->QtyDispatched * $LnItm->Volume);
	$_SESSION['CreditItems' . $identifier]->totalWeight+= ($LnItm->QtyDispatched * $LnItm->Weight);

	if (!isset($_POST['ProcessCredit'])) {

		$j++;
		// Use of htmlspecialchars to convert ampersand, double_quote, single_quote, less_than, and greater_than to html entities to to preserve their meanings.
		echo '<tr class="striped_row">
			<td>' . $LnItm->StockID . '</td>
			<td title="' . htmlspecialchars($LnItm->LongDescription) . '">' . $LnItm->ItemDescription . '</td>
			<td class="number">' . locale_number_format($LnItm->Quantity, $LnItm->DecimalPlaces) . '</td>
			<td>' . $LnItm->Units . '</td>';

		if ($LnItm->Controlled == 1) {

			echo '<td><input class="number" type="hidden" name="Quantity_' . $LnItm->LineNumber . '" value="' . locale_number_format($LnItm->QtyDispatched, $LnItm->DecimalPlaces) . '" /><a ' . ($j == 1 ? 'autofocus="autofocus" ' : '') . ' href="' . $RootPath . '/CreditItemsControlled.php?LineNo=' . $LnItm->LineNumber . '&amp;CreditInvoice=Yes&amp;identifier=' . $identifier . '" tabindex="' . $TabIndex++ . '">' . locale_number_format($LnItm->QtyDispatched, $LnItm->DecimalPlaces) . '</a></td>';

		} else {

			echo '<td><input class="number" ' . ($j == 1 ? 'autofocus="autofocus" ' : '') . ' maxlength="6" name="Quantity_' . $LnItm->LineNumber . '" required="required" size="6" tabindex="' . $TabIndex++ . '" title="' . __('Enter the quantity of this item to credit') . '" type="text" value="' . locale_number_format($LnItm->QtyDispatched, $LnItm->DecimalPlaces) . '" /></td>';

		}

		$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);

		$j++;
		echo '<td><input class="number" maxlength="12" name="Price_' . $LnItm->LineNumber . '" required="required" size="6" tabindex="' . $TabIndex++ . '" title="' . __('Enter the price at which to credit this item') . '" type="text" value="' . locale_number_format($LnItm->Price, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '" /></td>
			<td><input class="number" maxlength="3" name="Discount_' . $LnItm->LineNumber . '" required="required" size="3" tabindex="' . $TabIndex++ . '" type="text" value="' . locale_number_format(($LnItm->DiscountPercent * 100), 2) . '" />%</td>
			<td class="number">' . $DisplayLineTotal . '</td>';

		/*Need to list the taxes applicable to this line */
		echo '<td>';
		$i = 0;
		if (is_array($_SESSION['CreditItems' . $identifier]->LineItems[$LnItm->LineNumber]->Taxes)) {
			foreach ($_SESSION['CreditItems' . $identifier]->LineItems[$LnItm->LineNumber]->Taxes AS $Tax) {
				if ($i > 0) {
					echo '<br />';
				}
				echo $Tax->TaxAuthDescription;
				$i++;
			}
		}
		echo '</td>
			<td class="number">';

	}
	$i = 0; // initialise the number of taxes iterated through
	$TaxLineTotal = 0; //initialise tax total for the line
	if (is_array($LnItm->Taxes)) {
		foreach ($LnItm->Taxes as $Tax) {
			if ($i > 0) {
				echo '<br />';
			}
			if (!isset($_POST['ProcessCredit'])) {
				echo '<input class="number" maxlength="4" name="' . $LnItm->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" required="required" size="4" tabindex="' . $TabIndex++ . '" type="text" value="' . locale_number_format($Tax->TaxRate * 100, 2) . '" />%';
			}
			$i++;
			if ($Tax->TaxOnTax == 1) {
				if (!isset($TaxTotals[$Tax->TaxAuthID])) {
					$TaxTotals[$Tax->TaxAuthID] = ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));

				} else {
					$TaxTotals[$Tax->TaxAuthID]+= ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
				}
				$TaxLineTotal+= ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
			} else {
				if (!isset($TaxTotals[$Tax->TaxAuthID])) {
					$TaxTotals[$Tax->TaxAuthID] = ($Tax->TaxRate * $LineTotal);
				} else {

					$TaxTotals[$Tax->TaxAuthID]+= ($Tax->TaxRate * $LineTotal);
				}
				$TaxLineTotal+= ($Tax->TaxRate * $LineTotal);
			}
			$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
		}
	}
	$TaxTotal+= $TaxLineTotal;

	$DisplayTaxAmount = locale_number_format($TaxLineTotal, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);
	$DisplayGrossLineTotal = locale_number_format($LineTotal + $TaxLineTotal, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);

	if (!isset($_POST['ProcessCredit'])) {
		echo '</td>';

		echo '<td class="number">' . $DisplayTaxAmount . '</td>
				<td class="number">' . $DisplayGrossLineTotal . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '&Delete=' . $LnItm->LineNumber . '" onclick="return confirm(\'' . __('Are you sure you wish to delete this item from the credit?') . '\');" tabindex="' . $TabIndex++ . '">' . __('Delete') . '</a></td>
			</tr>';

		echo '<tr class="' . $RowClass . '">
	<td colspan="13"><textarea name="Narrative_' . $LnItm->LineNumber . '" cols="100%" rows="1" tabindex="' . $TabIndex++ . '">' . $LnItm->Narrative . '</textarea>
				<br />
				<hr /></td>
			</tr>';
		$j++;
	}
} /*end foreach loop displaying the invoice lines to credit */

if (!isset($_POST['ChargeFreightCost']) and !isset($_SESSION['CreditItems' . $identifier]->FreightCost)) {
	$_POST['ChargeFreightCost'] = 0;
}

if (!isset($_POST['ProcessCredit'])) {
	echo '<tr>
		<td class="number" colspan="3">' . __('Freight cost charged on invoice') . '</td>
		<td class="number">' . locale_number_format($_SESSION['Old_FreightCost'], $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '</td>
		<td></td>
		<td class="number" colspan="2">' . __('Credit Freight Cost') . '</td>
		<td><input class="number" maxlength="6" name="ChargeFreightCost" required="required" size="6" tabindex="' . $TabIndex++ . '" type="text" value="' . locale_number_format($_SESSION['CreditItems' . $identifier]->FreightCost, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '" /></td>
		<td>&nbsp;</td>
		<td>';

	$i = 0; // initialise the number of taxes iterated through
	foreach ($_SESSION['CreditItems' . $identifier]->FreightTaxes as $FreightTaxLine) {
		if ($i > 0) {
			echo '<br />';
		}
		echo $FreightTaxLine->TaxAuthDescription;
		$i++;
	}
}
$FreightTaxTotal = 0; //initialise tax total
$i = 0;
foreach ($_SESSION['CreditItems' . $identifier]->FreightTaxes as $FreightTaxLine) {
	if ($i > 0) {
		echo '<br />';
	}

	if (!isset($_POST['ProcessCredit'])) {
		echo '<input class="number" maxlength="4" name="FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder . '" required="required" size="4" type="text" value="' . locale_number_format(($FreightTaxLine->TaxRate * 100), 2) . '" tabindex="' . $TabIndex++ . '" />';
	}
	if ($FreightTaxLine->TaxOnTax == 1) {
		$TaxTotals[$FreightTaxLine->TaxAuthID]+= ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems' . $identifier]->FreightCost + $FreightTaxTotal));
		$FreightTaxTotal+= ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems' . $identifier]->FreightCost + $FreightTaxTotal));
	} else {
		$TaxTotals[$FreightTaxLine->TaxAuthID]+= ($FreightTaxLine->TaxRate * $_SESSION['CreditItems' . $identifier]->FreightCost);
		$FreightTaxTotal+= ($FreightTaxLine->TaxRate * $_SESSION['CreditItems' . $identifier]->FreightCost);
	}
	$i++;
	$TaxGLCodes[$FreightTaxLine->TaxAuthID] = $FreightTaxLine->TaxGLCode;
}
if (!isset($_POST['ProcessCredit'])) {
	echo '</td>';
	echo '<td class="number">' . locale_number_format($FreightTaxTotal, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '</td>
		<td class="number">' . locale_number_format($FreightTaxTotal + $_SESSION['CreditItems' . $identifier]->FreightCost, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '</td>
	<td>&nbsp;</td>
		</tr>';
}

$TaxTotal+= $FreightTaxTotal;
$DisplayTotal = locale_number_format($_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);

if (!isset($_POST['ProcessCredit'])) {
	echo '<tr>
			<td class="number" colspan="7">' . __('Credit Totals') . '</td>
			<td class="number"><hr /><b>' . $DisplayTotal . '</b><hr /></td>
			<td colspan="2">&nbsp;</td>
			<td class="number"><hr /><b>' . locale_number_format($TaxTotal, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '</b><hr /></td>
			<td class="number"><hr /><b>' . locale_number_format($TaxTotal + ($_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost), $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) . '</b>
			<hr /></td>
			<td>&nbsp;</td>
		</tr>
		</table>';
}
$DefaultDispatchDate = Date($_SESSION['DefaultDateFormat']);

$OKToProcess = true;
//Here we just validate if there is no credit qty available since the credit items not retrieve from salesorders, so following method is not 100% correct.
if (isset($_POST['CreditType']) and ($_POST['CreditType'] == 'WriteOff' or $_POST['CreditType'] == 'Return' or $_POST['CreditType'] == 'ReverseOverCharge')) {
	foreach ($_SESSION['CreditItems' . $identifier]->LineItems as $CreditLine) {
		$SQL = "SELECT count(*) FROM salesorderdetails WHERE orderno = '" . $_SESSION['CreditItems' . $identifier]->OrderNo . "'
									AND stkcode = '" . $CreditLine->StockID . "'
									AND qtyinvoiced >='" . $CreditLine->QtyDispatched . "'";
		$ErrMsg = __('Failed to retrieve salesoderdetails to compare if the order has been invoiced and that it is possible that the credit note may not already have been done');
		$DuplicateCreditResult = DB_query($SQL, $ErrMsg);
		$MyRow1 = DB_fetch_array($DuplicateCreditResult);
		if ($MyRow1[0] == 0) {
			prnMsg(__('The credit quantity for the line for') . ' ' . $CreditLine->StockID . ' ' . __('is more than the quantity invoiced. This check is made to ensure that the credit note is not duplicated.'), 'error');
			$OKToProcess = false;
			include('includes/footer.php');
			exit();
		}
	}
}

if ((isset($_POST['CreditType']) and $_POST['CreditType'] == 'WriteOff') and !isset($_POST['WriteOffGLCode'])) {
	prnMsg(__('The GL code to write off the credit value to must be specified. Please select the appropriate GL code for the selection box'), 'info');
	$OKToProcess = false;
}

if (isset($_POST['ProcessCredit']) and $OKToProcess == true) {

	/* SQL to process the postings for sales credit notes... First Get the area where the credit note is to from the branches table */

	$SQL = "SELECT area
				FROM custbranch
			WHERE custbranch.debtorno ='" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "'
			AND custbranch.branchcode = '" . $_SESSION['CreditItems' . $identifier]->Branch . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$Area = $MyRow[0];
	DB_free_result($Result);

	/*company record is read in on login and has information on GL Links and debtors GL account*/

	if ($_SESSION['CompanyRecord'] == 0) {
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg(__('The company information and preferences could not be retrieved') . ' - ' . __('see your system administrator'), 'error');
		include('includes/footer.php');
		exit();
	}

	/*Now Get the next credit note number - function in SQL_CommonFunctions*/

	$CreditNo = GetNextTransNo(11);
	$PeriodNo = GetPeriod($DefaultDispatchDate);

	/*Start an SQL transaction */

	DB_Txn_Begin();

	$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);

	/*Calculate the allocation and see if it is possible to allocate to the invoice being credited */

	$SQL = "SELECT(ovamount+ovgst+ovfreight-ovdiscount-alloc) AS baltoallocate
			FROM debtortrans
			WHERE transno=" . $_SESSION['ProcessingCredit'] . "
			AND type=10";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	/*Do some rounding */

	$_SESSION['CreditItems' . $identifier]->FreightCost = round($_SESSION['CreditItems' . $identifier]->FreightCost, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);
	$_SESSION['CreditItems' . $identifier]->total = round($_SESSION['CreditItems' . $identifier]->total, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);
	$TaxTotal = round($TaxTotal, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);

	$Allocate_amount = 0;
	$Settled = 0;
	$SettledInvoice = 0;
	$MyRow[0] = round($MyRow[0], $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces);
	if ($MyRow[0] > 0) { /*the invoice is not already fully allocated */

		if ($MyRow[0] > ($_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost + $TaxTotal)) {

			$Allocate_amount = $_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost + $TaxTotal;
			$SettledInvoice = 0;
			$Settled = 1;
		} else if ($MyRow[0] < ($_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost + $TaxTotal)) {
			/*the balance left to allocate is less than the credit note value */
			$Allocate_amount = $MyRow[0];
			$SettledInvoice = 1;
			$Settled = 0;
		} else {
			$Allocate_amount = $MyRow[0];
			$SettledInvoice = 1;
			$Settled = 1;
		}

		/*Now need to update the invoice DebtorTrans record for the amount to be allocated and if the invoice is now settled*/

		$SQL = "UPDATE debtortrans
				SET alloc = alloc + " . $Allocate_amount . ",
					settled='" . $SettledInvoice . "'
				WHERE transno = '" . $_SESSION['ProcessingCredit'] . "'
				AND type=10";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The alteration to the invoice record to reflect the allocation of the credit note to the invoice could not be done because');
		$Result = DB_query($SQL, $ErrMsg, '', true);
	}

	/*Now insert the Credit Note into the DebtorTrans table with the allocations as calculated above*/
	$SQL = "INSERT INTO debtortrans(transno,
									type,
									debtorno,
									branchcode,
									trandate,
									inputdate,
									prd,
									reference,
									tpe,
									order_,
									ovamount,
									ovgst,
									ovfreight,
									rate,
									invtext,
									alloc,
									settled,
									salesperson)
						VALUES(" . $CreditNo . ",
							11,
							'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
							'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
							'" . $DefaultDispatchDate . "',
							'" . date('Y-m-d H-i-s') . "',
							'" . $PeriodNo . "',
							'Inv-" . $_SESSION['ProcessingCredit'] . "',
							'" . $_SESSION['CreditItems' . $identifier]->DefaultSalesType . "',
							'" . $_SESSION['CreditItems' . $identifier]->OrderNo . "',
							'" . -$_SESSION['CreditItems' . $identifier]->total . "',
							'" . -$TaxTotal . "',
							'" . -$_SESSION['CreditItems' . $identifier]->FreightCost . "',
							'" . $_SESSION['CurrencyRate'] . "',
							'" . $_POST['CreditText'] . "',
							'" . -$Allocate_amount . "',
							'" . $Settled . "',
							'" . $_SESSION['CreditItems' . $identifier]->SalesPerson . "')";

	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The customer credit note transaction could not be added to the database because');
	$Result = DB_query($SQL, $ErrMsg, '', true);

	$CreditTransID = DB_Last_Insert_ID('debtortrans', 'id');

	/* Insert the tax totals for each tax authority where tax was charged on the invoice */
	foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {

		$SQL = "INSERT INTO debtortranstaxes(
							debtortransid,
							taxauthid,
							taxamount)
				VALUES('" . $CreditTransID . "',
					'" . $TaxAuthID . "',
					'" . (-$TaxAmount / $_SESSION['CurrencyRate']) . "')";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The debtor transaction taxes records could not be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);
	}

	/*Now insert the allocation record if > 0 */
	if ($Allocate_amount != 0) {
		$SQL = "INSERT INTO custallocns(amt,
						transid_allocfrom,
						transid_allocto,
						datealloc)
			VALUES('" . $Allocate_amount . "',
				'" . $CreditTransID . "',
				'" . $_SESSION['CreditItems' . $identifier]->TransID . "',
				CURRENT_DATE)";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The allocation record for the credit note could not be added to the database because');
		$Result = DB_query($SQL, $ErrMsg, '', true);
	}

	/* Update sales order details quantity invoiced less this credit quantity. */

	foreach ($_SESSION['CreditItems' . $identifier]->LineItems as $CreditLine) {

		if ($CreditLine->QtyDispatched > 0) {

			$LocalCurrencyPrice = round(($CreditLine->Price / $_SESSION['CurrencyRate']), $_SESSION['CompanyRecord']['decimalplaces']);

			if ($CreditLine->MBflag == 'M' or $CreditLine->MBflag == 'B') {
				/*Need to get the current location quantity will need it later for the stock movements */
				$SQL = "SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $CreditLine->StockID . "'
					AND loccode= '" . $_SESSION['CreditItems' . $identifier]->Location . "'";

				$Result = DB_query($SQL);
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}
			} else {
				$QtyOnHandPrior = 0; //because its a dummy/assembly/kitset part

			}

			if ($_POST['CreditType'] == 'Return') {

				/* some want this some do not
				 * We cannot use the orderlineno to update with as it could be different when added to the credit note than it was when the order was created
				 * Also there could potentially be the same item on the order multiple times with different delivery dates
				 * So all up the SQL below is a bit hit and miss !!
				 * Probably right 99% of time with the item on the order only once */

				$SQL = "UPDATE salesorderdetails
							SET qtyinvoiced = qtyinvoiced - " . $CreditLine->QtyDispatched . ",
								completed=0
						WHERE orderno = '" . $_SESSION['CreditItems' . $identifier]->OrderNo . "'
						AND stkcode = '" . $CreditLine->StockID . "'
						AND quantity >=" . $CreditLine->QtyDispatched;

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The sales order detail record could not be updated for the reduced quantity invoiced because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				/* Update location stock records if not a dummy stock item */

				if ($CreditLine->MBflag == 'M' or $CreditLine->MBflag == 'B') {

					$SQL = "UPDATE locstock
								SET locstock.quantity = locstock.quantity + " . $CreditLine->QtyDispatched . "
								WHERE locstock.stockid = '" . $CreditLine->StockID . "'
								AND loccode = '" . $_SESSION['CreditItems' . $identifier]->Location . "'";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Location stock record could not be updated because');
					$Result = DB_query($SQL, $ErrMsg, '', true);

				} else if ($CreditLine->MBflag == 'A') { /* its an assembly */
					/*Need to get the BOM for this part and make stock moves for the components
					 and of course update the Location stock balances */

					$StandardCost = 0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
					$SQL = "SELECT	bom.component,
									bom.quantity,
								stockmaster.actualcost AS standard
								FROM bom,
									stockmaster
								WHERE bom.component=stockmaster.stockid
								AND bom.parent='" . $CreditLine->StockID . "'
                                AND bom.effectiveafter <= CURRENT_DATE
                                AND bom.effectiveto > CURRENT_DATE";

					$ErrMsg = __('Could not retrieve assembly components from the database for') . ' ' . $CreditLine->StockID . ' ' . __('because');
					$AssResult = DB_query($SQL, $ErrMsg, '', true);

					while ($AssParts = DB_fetch_array($AssResult)) {

						$StandardCost+= $AssParts['standard'] * $AssParts['quantity'];
						/*Determine the type of stock item being credited */
						$SQL = "SELECT mbflag
								FROM stockmaster
								WHERE stockid = '" . $AssParts['component'] . "'";
						$Result = DB_query($SQL);
						$MBFlagRow = DB_fetch_row($Result);
						$Component_MBFlag = $MBFlagRow[0];

						/* Insert stock movements for the stock coming back in - with unit cost */
						if ($Component_MBFlag == 'M' or $Component_MBFlag == 'B') {
							/*Need to get the current location quantity will need it later for the stock movement */
							$SQL = "SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $AssParts['component'] . "'
									AND loccode= '" . $_SESSION['CreditItems' . $identifier]->Location . "'";
							$Result = DB_query($SQL, __('Could not get the current location stock of the assembly component') . ' ' . $AssParts['component'], __('The SQL that failed was'), true);
							if (DB_num_rows($Result) == 1) {
								$LocQtyRow = DB_fetch_row($Result);
								$QtyOnHandPrior = $LocQtyRow[0];
							} else {
								/*There must actually be some error this should never happen */
								$QtyOnHandPrior = 0;
							}
						} else {
							$QtyOnHandPrior = 0; //because its a dummy/assembly/kitset part

						}

						if ($Component_MBFlag == 'M' or $Component_MBFlag == 'B') {

							$SQL = "INSERT INTO stockmoves(stockid,
															type,
															transno,
															loccode,
															trandate,
															userid,
															debtorno,
															branchcode,
															prd,
															reference,
															qty,
															standardcost,
															show_on_inv_crds,
															newqoh )
												VALUES('" . $AssParts['component'] . "',
														11,
														'" . $CreditNo . "',
														'" . $_SESSION['CreditItems' . $identifier]->Location . "',
														'" . $DefaultDispatchDate . "',
														'" . $_SESSION['UserID'] . "',
														'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
														'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
														'" . $PeriodNo . "',
														'" . __('Ex Inv') . ': ' . $_SESSION['ProcessingCredit'] . ' ' . __('Assembly') . ': ' . $CreditLine->StockID . "',
														'" . ($AssParts['quantity'] * $CreditLine->QtyDispatched) . "',
														'" . $AssParts['standard'] . "',
														0,
														'" . ($QtyOnHandPrior + ($AssParts['quantity'] * $CreditLine->QtyDispatched)) . "'
														)";
						} else {

							$SQL = "INSERT INTO stockmoves(stockid,
															type,
															transno,
															loccode,
															trandate,
															userid,
															debtorno,
															branchcode,
															prd,
															reference,
															qty,
															standardcost,
															show_on_inv_crds)
												VALUES('" . $AssParts['component'] . "',
														11,
														'" . $CreditNo . "',
														'" . $_SESSION['CreditItems' . $identifier]->Location . "',
														'" . $DefaultDispatchDate . "',
														'" . $_SESSION['UserID'] . "',
														'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
														'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
														'" . $PeriodNo . "',
														'" . __('Ex Inv') . ': ' . $_SESSION['ProcessingCredit'] . ' ' . __('Assembly') . ': ' . $CreditLine->StockID . "',
														'" . ($AssParts['quantity'] * $CreditLine->QtyDispatched) . "',
														'" . $AssParts['standard'] . "',
														0)";
						}

						$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Stock movement records for the assembly components of') . ' ' . $CreditLine->StockID . ' ' . __('could not be inserted because');
						$Result = DB_query($SQL, $ErrMsg, '', true);

						if ($Component_MBFlag == 'M' or $Component_MBFlag == 'B') {
							$SQL = "UPDATE locstock
									SET locstock.quantity = locstock.quantity + " . ($AssParts['quantity'] * $CreditLine->QtyDispatched) . "
									WHERE locstock.stockid = '" . $AssParts['component'] . "'
									AND loccode = '" . $_SESSION['CreditItems' . $identifier]->Location . "'";

							$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Location stock record could not be updated for an assembly component because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						}
					} /* end of assembly explosion and updates */
					/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
					$_SESSION['CreditItems' . $identifier]->LineItems[$CreditLine->LineNumber]->StandardCost = $StandardCost;
					$CreditLine->StandardCost = $StandardCost;
				}

				/* Insert stock movements for the stock coming back in - with unit cost */

				if ($CreditLine->MBflag == 'M' or $CreditLine->MBflag == 'B') {
					$SQL = "INSERT INTO stockmoves(stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													price,
													prd,
													reference,
													qty,
													discountpercent,
													standardcost,
													newqoh,
													narrative)
											VALUES('" . $CreditLine->StockID . "',
													11,
													'" . $CreditNo . "',
													'" . $_SESSION['CreditItems' . $identifier]->Location . "',
													'" . $DefaultDispatchDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
													'" . $LocalCurrencyPrice . "',
													'" . $PeriodNo . "',
													'" . __('Ex Inv') . ' - ' . $_SESSION['ProcessingCredit'] . "',
													'" . $CreditLine->QtyDispatched . "',
													'" . $CreditLine->DiscountPercent . "',
													'" . $CreditLine->StandardCost . "',
													'" . ($QtyOnHandPrior + $CreditLine->QtyDispatched) . "',
													'" . $CreditLine->Narrative . "')";
				} else {

					$SQL = "INSERT INTO stockmoves(stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													price,
													prd,
													reference,
													qty,
													discountpercent,
													standardcost,
													narrative)
											VALUES('" . $CreditLine->StockID . "',
													11,
													'" . $CreditNo . "',
													'" . $_SESSION['CreditItems' . $identifier]->Location . "',
													'" . $DefaultDispatchDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
													'" . $LocalCurrencyPrice . "',
													'" . $PeriodNo . "',
													'" . __('Ex Inv') . " - " . $_SESSION['ProcessingCredit'] . "',
													'" . $CreditLine->QtyDispatched . "',
													'" . $CreditLine->DiscountPercent . "',
													'" . $CreditLine->StandardCost . "',
													'" . $CreditLine->Narrative . "')";
				}

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Stock movement records could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');
				/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
				//echo "<div align="left"><pre>"; var_dump($CreditLine); echo "</pre> </div>";
				if ($CreditLine->Controlled == 1) {
					foreach ($CreditLine->SerialItems as $Item) {
						/*We need to add the StockSerialItem record and The StockSerialMoves as well */
						$SQL = "SELECT quantity from stockserialitems
								WHERE stockid='" . $CreditLine->StockID . "'
								AND loccode='" . $_SESSION['CreditItems' . $identifier]->Location . "'
								AND serialno='" . $Item->BundleRef . "'";

						$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be selected because');
						$Result = DB_query($SQL, $ErrMsg, '', true);

						if (DB_num_rows($Result) == 0) {
							$SQL = "INSERT INTO stockserialitems(stockid,
																	loccode,
																	serialno,
																	quantity,
																	qualitytext)
											VALUES
														('" . $CreditLine->StockID . "',
														 '" . $_SESSION['CreditItems' . $identifier]->Location . "',
														 '" . $Item->BundleRef . "',
														 '" . $Item->BundleQty . "',
													 	 '')";

							$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						} else {

							$SQL = "UPDATE stockserialitems
								SET quantity= quantity + " . $Item->BundleQty . "
								WHERE stockid='" . $CreditLine->StockID . "'
								AND loccode='" . $_SESSION['CreditItems' . $identifier]->Location . "'
								AND serialno='" . $Item->BundleRef . "'";
							$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						}

						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves(stockmoveno,
																stockid,
																serialno,
																moveqty)
													VALUES('" . $StkMoveNo . "',
															'" . $CreditLine->StockID . "',
															'" . $Item->BundleRef . "',
															'" . $Item->BundleQty . "')";
						$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
						$Result = DB_query($SQL, $ErrMsg, '', true);

					} /* foreach controlled item in the serialitems array */
				} /*end if the orderline is a controlled item */

			} elseif ($_POST['CreditType'] == 'WriteOff') {
				/*Insert a stock movement coming back in to show the credit note and
				a reversing stock movement to show the write off
				no mods to location stock records*/

				$SQL = "INSERT INTO stockmoves(stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh,
												narrative )
									VALUES('" . $CreditLine->StockID . "',
											11,
											'" . $CreditNo . "',
											'" . $_SESSION['CreditItems' . $identifier]->Location . "',
											'" . $DefaultDispatchDate . "',
											'" . $_SESSION['UserID'] . "',
											'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
											'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
											'" . $LocalCurrencyPrice . "',
											'" . $PeriodNo . "',
											'" . __('Ex Inv') . ' - ' . $_SESSION['ProcessingCredit'] . "',
											'" . $CreditLine->QtyDispatched . "',
											'" . $CreditLine->DiscountPercent . "',
											'" . $CreditLine->StandardCost . "',
											'" . ($QtyOnHandPrior + $CreditLine->QtyDispatched) . "',
											'" . $CreditLine->Narrative . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Stock movement records could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

				$SQL = "INSERT INTO stockmoves(stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												show_on_inv_crds,
												newqoh,
												narrative
												)
									VALUES('" . $CreditLine->StockID . "',
											11,
											'" . $CreditNo . "',
											'" . $_SESSION['CreditItems' . $identifier]->Location . "',
											'" . $DefaultDispatchDate . "',
											'" . $_SESSION['UserID'] . "',
											'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
											'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
											'" . $LocalCurrencyPrice . "',
											'" . $PeriodNo . "',
											'" . __('Written off ex Inv') . ' - ' . $_SESSION['ProcessingCredit'] . "',
											'" . -$CreditLine->QtyDispatched . "',
											'" . $CreditLine->DiscountPercent . "',
											'" . $CreditLine->StandardCost . "',
											0,
											'" . $QtyOnHandPrior . "',
											'" . $CreditLine->Narrative . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Stock movement records could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			} elseif ($_POST['CreditType'] == 'ReverseOverCharge') {
				/*Insert a stock movement coming back in to show the credit note  - flag the stockmovement not to show on stock movement enquiries - its is not a real stock movement only for invoice line - also no mods to location stock records*/
				$SQL = "INSERT INTO stockmoves(stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh,
												hidemovt,
												narrative)
									VALUES('" . $CreditLine->StockID . "',
											11,
											'" . $CreditNo . "',
											'" . $_SESSION['CreditItems' . $identifier]->Location . "',
											'" . $DefaultDispatchDate . "',
											'" . $_SESSION['UserID'] . "',
											'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
											'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
											'" . $LocalCurrencyPrice . "',
											'" . $PeriodNo . "',
											'" . __('Ex Inv') . ' - ' . $_SESSION['ProcessingCredit'] . "',
											'" . $CreditLine->QtyDispatched . "',
											'" . $CreditLine->DiscountPercent . "',
											'" . $CreditLine->StandardCost . "',
											'" . $QtyOnHandPrior . "',
											1,
											'" . $CreditLine->Narrative . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Stock movement records could not be inserted because');

				$Result = DB_query($SQL, $ErrMsg, '', true);
				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');
			}

			$Commission = CalculateCommission($_SESSION['CreditItems' . $Identifier]->SalesPerson, $_SESSION['CreditItems' . $Identifier]->DebtorNo, $_SESSION['CreditItems' . $Identifier]->Branch, $CreditLine->StockID, $_SESSION['CreditItems' . $Identifier]->DefaultCurrency, ($CreditLine->QtyDispatched * $CreditLine->Price), $PeriodNo);
			if ($Commission != 0) {

				$TransNo = GetNextTransNo(39);
				$SQL = "INSERT INTO salescommissions (commissionno,
													  type,
													  transno,
													  stkmoveno,
													  salespersoncode,
													  paid,
													  amount,
													  currency,
													  exrate
													) VALUES (
													  '" . $TransNo . "',
													  10,
													  '" . $CreditNo . "',
													  '" . $StkMoveNo . "',
													  '" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "',
													  0,
													  '" . round(-$Commission, $_SESSION['CompanyRecord']['decimalplaces']) . "',
													  '" . $_SESSION['CreditItems' . $Identifier]->DefaultCurrency . "',
													  '" . $_SESSION['CurrencyRate'] . "'
													)";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The sales commission accrual record could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$SalesPersonSQL = "SELECT salesmanname, glaccount FROM salesman WHERE salesmancode='" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "'";
				$SalesPersonResult = DB_query($SalesPersonSQL);
				$SalesPersonRow = DB_fetch_array($SalesPersonResult);

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										39,
										'" . $TransNo . "',
										'" . $DefaultDispatchDate . "',
										'" . $PeriodNo . "',
										'" . $SalesPersonRow['glaccount'] . "',
										'" . mb_substr(__('Sales Commission') . " - " . $SalesPersonRow['salesmanname'] . " - " . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . __('Invoice No') . $CreditNo, 0, 200) . "',
										'" . round(-$Commission / $_SESSION['CurrencyRate'], $_SESSION['CompanyRecord']['decimalplaces']) . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The expenses side of the sales commission posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										39,
										'" . $TransNo . "',
										'" . $DefaultDispatchDate . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['commissionsact'] . "',
										'" . mb_substr(__('Sales Commission') . " - " . $SalesPersonRow['salesmanname'] . " - " . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . __('Invoice No') . $CreditNo, 0, 200) . "',
										'" . round($Commission / $_SESSION['CurrencyRate'], $_SESSION['CompanyRecord']['decimalplaces']) . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The accruals side of the sales commission posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			}

			/*Insert the taxes that applied to this line */
			foreach ($CreditLine->Taxes as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes(stkmoveno,
													taxauthid,
													taxrate,
													taxcalculationorder,
													taxontax)
					VALUES('" . $StkMoveNo . "',
						'" . $Tax->TaxAuthID . "',
						'" . $Tax->TaxRate . "',
						'" . $Tax->TaxCalculationOrder . "',
						'" . $Tax->TaxOnTax . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Taxes and rates applicable to this credit note line item could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			}

			/*Insert Sales Analysis records */
			$SalesValue = 0;
			if ($_SESSION['CurrencyRate'] > 0) {
				$SalesValue = $CreditLine->Price * $CreditLine->QtyDispatched / $_SESSION['CurrencyRate'];
			}

			$SQL = "SELECT COUNT(*),
						stkcategory,
						salesanalysis.area
					FROM salesanalysis INNER JOIN custbranch
					ON salesanalysis.cust=custbranch.debtorno
						AND salesanalysis.custbranch=custbranch.branchcode
						AND salesanalysis.area=custbranch.area
					INNER JOIN stockmaster
					ON salesanalysis.stkcategory=stockmaster.categoryid
						AND salesanalysis.stockid=stockmaster.stockid
					WHERE typeabbrev ='" . $_SESSION['CreditItems' . $identifier]->DefaultSalesType . "'
					AND periodno='" . $PeriodNo . "'
					AND cust = '" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "'
					AND custbranch = '" . $_SESSION['CreditItems' . $identifier]->Branch . "'
					AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
					AND budgetoractual=1
					AND salesanalysis.salesperson='" . $_SESSION['CreditItems' . $identifier]->SalesPerson . "'
					GROUP BY stkcategory,
							salesanalysis.area";

			$ErrMsg = __('The count to check for existing Sales analysis records could not run because');

			$Result = DB_query($SQL, $ErrMsg, '', true);

			$MyRow = DB_fetch_row($Result);

			if ($MyRow[0] > 0) { /*Update the existing record that already exists */

				if ($_POST['CreditType'] == 'ReverseOverCharge') {

					$SQL = "UPDATE salesanalysis
							SET amt=amt-" . $SalesValue . ",
							disc=disc-" . ($CreditLine->DiscountPercent * $CreditLine->Price * $CreditLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
							WHERE salesanalysis.area='" . $MyRow[2] . "'
							AND salesanalysis.salesperson='" . $_SESSION['CreditItems' . $identifier]->SalesPerson . "'
							AND typeabbrev ='" . $_SESSION['CreditItems' . $identifier]->DefaultSalesType . "'
							AND periodno = '" . $PeriodNo . "'
							AND cust = '" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "'
							AND custbranch = '" . $_SESSION['CreditItems' . $identifier]->Branch . "'
							AND stockid = '" . $CreditLine->StockID . "'
							AND salesanalysis.stkcategory ='" . $MyRow[1] . "'
							AND budgetoractual=1";

				} else {

					$SQL = "UPDATE salesanalysis
							SET amt=amt-" . $SalesValue . ",
							cost=cost-" . $CreditLine->StandardCost * $CreditLine->QtyDispatched . ",
							qty=qty-" . $CreditLine->QtyDispatched . ",
							disc=disc-" . $CreditLine->DiscountPercent * $SalesValue . "
							WHERE salesanalysis.area='" . $MyRow[2] . "'
							AND salesanalysis.salesperson='" . $_SESSION['CreditItems' . $identifier]->SalesPerson . "'
							AND typeabbrev ='" . $_SESSION['CreditItems' . $identifier]->DefaultSalesType . "'
							AND periodno = '" . $PeriodNo . "'
							AND cust = '" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "'
							AND custbranch = '" . $_SESSION['CreditItems' . $identifier]->Branch . "'
							AND stockid = '" . $CreditLine->StockID . "'
							AND salesanalysis.stkcategory ='" . $MyRow[1] . "'
							AND budgetoractual=1";
				}

			} else { /* insert a new sales analysis record */

				if ($_POST['CreditType'] == 'ReverseOverCharge') {

					$SQL = "INSERT INTO salesanalysis(typeabbrev,
														periodno,
														amt,
														cust,
														custbranch,
														qty,
														disc,
														stockid,
														area,
														budgetoractual,
														salesperson,
														stkcategory)
							SELECT '" . $_SESSION['CreditItems' . $identifier]->DefaultSalesType . "',
							'" . $PeriodNo . "',
							'" . -$CreditLine->Price * $CreditLine->QtyDispatched / $_SESSION['CurrencyRate'] . "',
							'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
							'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
							0,
							'" . -$CreditLine->DiscountPercent * $SalesValue . "',
							'" . $CreditLine->StockID . "',
							custbranch.area,
							1,
							'" . $_SESSION['CreditItems' . $identifier]->SalesPerson . "',
							stockmaster.categoryid
							FROM stockmaster,
								custbranch
							WHERE stockmaster.stockid = '" . $CreditLine->StockID . "'
							AND custbranch.debtorno = '" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "'
							AND custbranch.branchcode='" . $_SESSION['CreditItems' . $identifier]->Branch . "'";
				} else {

					$SQL = "INSERT INTO salesanalysis(typeabbrev,
								periodno,
								amt,
								cost,
								cust,
								custbranch,
								qty,
								disc,
								stockid,
								area,
								budgetoractual,
								salesperson,
								stkcategory)
						SELECT '" . $_SESSION['CreditItems' . $identifier]->DefaultSalesType . "',
							'" . $PeriodNo . "',
							'" . -$SalesValue . "',
							'" . -$CreditLine->StandardCost * $CreditLine->QtyDispatched . "',
							'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
							'" . $_SESSION['CreditItems' . $identifier]->Branch . "',
							'" . -$CreditLine->QtyDispatched . "',
							'" . -$CreditLine->DiscountPercent * $SalesValue . "',
							'" . $CreditLine->StockID . "',
							custbranch.area,
							1,
							'" . $_SESSION['CreditItems' . $identifier]->SalesPerson . "',
							stockmaster.categoryid
						FROM stockmaster,
							custbranch
						WHERE stockmaster.stockid = '" . $CreditLine->StockID . "'
						AND custbranch.debtorno = '" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "'
						AND custbranch.branchcode='" . $_SESSION['CreditItems' . $identifier]->Branch . "'";

				}
			}

			$ErrMsg = __('The sales analysis record for this credit note could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and ($CreditLine->StandardCost != 0 or (isset($StandardCost) and $StandardCost != 0)) and $_POST['CreditType'] != 'ReverseOverCharge') {

				/*first the cost of sales entry*/

				$COGSAccount = GetCOGSGLAccount($Area, $CreditLine->StockID, $_SESSION['CreditItems' . $identifier]->DefaultSalesType);

				$SQL = "INSERT INTO gltrans(type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
						VALUES(11,
							'" . $CreditNo . "',
							'" . $DefaultDispatchDate . "',
							'" . $PeriodNo . "',
							'" . $COGSAccount . "',
							'" . mb_substr($_SESSION['CreditItems' . $identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->QtyDispatched . " @ " . $CreditLine->StandardCost, 0, 200) . "',
							'" . -round($CreditLine->StandardCost * $CreditLine->QtyDispatched, $_SESSION['CompanyRecord']['decimalplaces']) . "'
							)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The cost of sales GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				/*now the stock entry*/

				if ($_POST['CreditType'] == 'WriteOff') {
					$SQL = "INSERT INTO gltrans(type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
								VALUES(11,
									'" . $CreditNo . "',
									'" . $DefaultDispatchDate . "',
									'" . $PeriodNo . "',
									'" . $_POST['WriteOffGLCode'] . "',
									'" . mb_substr($_SESSION['CreditItems' . $identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->QtyDispatched . " @ " . $CreditLine->StandardCost, 0, 200) . "',
									'" . round($CreditLine->StandardCost * $CreditLine->QtyDispatched, $_SESSION['CompanyRecord']['decimalplaces']) . "')";
				} else {
					$StockGLCode = GetStockGLCode($CreditLine->StockID);
					$SQL = "INSERT INTO gltrans(type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
								VALUES(11,
									'" . $CreditNo . "',
									'" . $DefaultDispatchDate . "',
									'" . $PeriodNo . "',
									'" . $StockGLCode['stockact'] . "',
									'" . mb_substr($_SESSION['CreditItems' . $identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->QtyDispatched . " @ " . $CreditLine->StandardCost, 0, 200) . "',
									'" . round($CreditLine->StandardCost * $CreditLine->QtyDispatched, $_SESSION['CompanyRecord']['decimalplaces']) . "')";
				}

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock side or write off of the cost of sales GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and $CreditLine->Price != 0) {

				//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $CreditLine->StockID, $_SESSION['CreditItems' . $identifier]->DefaultSalesType);

				$SQL = "INSERT INTO gltrans(type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES(11,
						'" . $CreditNo . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $SalesGLAccounts['salesglcode'] . "',
						'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->QtyDispatched . " @ " . $CreditLine->Price . "',
						'" . round($CreditLine->Price * $CreditLine->QtyDispatched / $_SESSION['CurrencyRate'], $_SESSION['CompanyRecord']['decimalplaces']) . "'
						)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The credit note GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				if ($CreditLine->DiscountPercent != 0) {

					$SQL = "INSERT INTO gltrans(type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES(11,
							'" . $CreditNo . "',
							'" . $DefaultDispatchDate . "',
							'" . $PeriodNo . "',
							'" . $SalesGLAccounts['discountglcode'] . "',
							'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . " - " . $CreditLine->StockID . " @ " . ($CreditLine->DiscountPercent * 100) . "%',
							'" . -round($CreditLine->Price * $CreditLine->QtyDispatched * $CreditLine->DiscountPercent / $_SESSION['CurrencyRate'], $_SESSION['CompanyRecord']['decimalplaces']) . "'
							)";
					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The credit note discount GL posting could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */

	if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1) {

		/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		if (($_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost + $TaxTotal) != 0) {
			$SQL = "INSERT INTO gltrans(type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES(11,
						'" . $CreditNo . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
						'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
						'" . -round(($_SESSION['CreditItems' . $identifier]->total + $_SESSION['CreditItems' . $identifier]->FreightCost + $TaxTotal) / $_SESSION['CurrencyRate'], $_SESSION['CompanyRecord']['decimalplaces']) . "'
					)";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The total debtor GL posting for the credit note could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}

		/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */

		if (round($_SESSION['CreditItems' . $identifier]->FreightCost, $_SESSION['CreditItems' . $identifier]->CurrDecimalPlaces) != 0) {
			$SQL = "INSERT INTO gltrans(type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
				VALUES(11,
					'" . $CreditNo . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['freightact'] . "',
					'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
					'" . round($_SESSION['CreditItems' . $identifier]->FreightCost / $_SESSION['CurrencyRate'], $_SESSION['CompanyRecord']['decimalplaces']) . "'
					)";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The freight GL posting for this credit note could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}

		foreach ($TaxTotals as $TaxAuthID => $TaxAmount) {
			if ($TaxAmount != 0) {
				$SQL = "INSERT INTO gltrans(
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount
						)
					VALUES(
						11,
						'" . $CreditNo . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $TaxGLCodes[$TaxAuthID] . "',
						'" . $_SESSION['CreditItems' . $identifier]->DebtorNo . "',
						'" . $TaxAmount / $_SESSION['CurrencyRate'] . "'
					)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The tax GL posting could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);
			}
		}

		EnsureGLEntriesBalance(11, $CreditNo);

	} /*end of if Sales and GL integrated */

	DB_Txn_Commit();

	unset($_SESSION['CreditItems' . $identifier]->LineItems);
	unset($_SESSION['CreditItems' . $identifier]);
	unset($_SESSION['ProcessingCredit']);

	echo '<div class="centre">' . __('Credit Note number') . ' ' . $CreditNo . ' ' . __('has been processed');
	if ($_SESSION['InvoicePortraitFormat'] == 0) {
		echo '<br /><a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $CreditNo . '&InvOrCredit=Credit&PrintPDF=True">' . __('Print this credit note') . '</a>';
	} else {
		echo '<br /><a href="' . $RootPath . '/PrintCustTransPortrait.php?FromTransNo=' . $CreditNo . '&InvOrCredit=Credit&PrintPDF=True">' . __('Print this credit note') . '</a>';
	}
	echo '</div>';
	/*end of process credit note */

} else { /*Process Credit NOT set so allow inputs to set up the credit note */

	echo '<br />
		<table class="selection">
		<tr>
			<td>' . __('Credit Note Type') . '</td>
			<td><select name="CreditType" tabindex="' . $TabIndex++ . '" onchange="ReloadForm(Update)">';

	if (!isset($_POST['CreditType']) or $_POST['CreditType'] == 'Return') {
		echo '<option selected="selected" value="Return">' . __('Goods returned to store') . '</option>';
		echo '<option value="WriteOff">' . __('Goods written off') . '</option>';
		echo '<option value="ReverseOverCharge">' . __('Reverse overcharge') . '</option>';
	} elseif ($_POST['CreditType'] == 'WriteOff') {
		echo '<option selected="selected" value="WriteOff">' . __('Goods written off') . '</option>';
		echo '<option value="Return">' . __('Goods returned to store') . '</option>';
		echo '<option value="ReverseOverCharge">' . __('Reverse overcharge') . '</option>';
	} else {
		echo '<option value="WriteOff">' . __('Goods written off') . '</option>';
		echo '<option value="Return">' . __('Goods returned to store') . '</option>';
		echo '<option selected="selected" value="ReverseOverCharge">' . __('Reverse overcharge') . '</option>';
	}
	echo '</select></td>
		</tr>';
	$j++;

	if (!isset($_POST['CreditType']) or $_POST['CreditType'] == 'Return') {

		/*if the credit note is a return of goods then need to know which location to receive them into */

		echo '<tr>
				<td>' . __('Goods returned to location') . '</td>
				<td><select name="Location" tabindex="' . $TabIndex++ . '">';

		$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1";
		$Result = DB_query($SQL);

		if (!isset($_POST['Location'])) {
			$_POST['Location'] = $_SESSION['CreditItems' . $identifier]->Location;
		}
		while ($MyRow = DB_fetch_array($Result)) {

			if ($_POST['Location'] == $MyRow['loccode']) {
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		}
		echo '</select></td></tr>';
		$j++;

	} elseif ($_POST['CreditType'] == 'WriteOff') { /* the goods are to be written off to somewhere */

		echo '<tr>
			<td>' . __('Write off the cost of the goods to') . '</td>
			<td><select name="WriteOffGLCode" tabindex="' . $TabIndex++ . '">';

		$SQL = "SELECT accountcode,
					accountname
				FROM chartmaster INNER JOIN accountgroups
				ON chartmaster.group_=accountgroups.groupname
				WHERE accountgroups.pandl=1
				ORDER BY chartmaster.accountcode";

		$Result = DB_query($SQL);

		while ($MyRow = DB_fetch_array($Result)) {

			if ($_POST['WriteOffGLCode'] == $MyRow['accountcode']) {
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountname'] . '</option>';
			}
		}
		echo '</select></td>
			</tr>';
	}

	$j++;
	echo '<tr>
			<td>' . __('Sales person') . '</td>';

	if ($_SESSION['SalesmanLogin'] != '') {
		echo '<td>';
		echo $_SESSION['UsersRealName'];
		echo '</td>';
	} else {
		echo '<td><select name="SalesPerson" tabindex="' . $TabIndex++ . '">';
		$SalesPeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current=1");
		/* SalesPerson will be set because it is an invoice being credited and the order salesperson would/should have been retrieved */
		while ($SalesPersonRow = DB_fetch_array($SalesPeopleResult)) {
			if ($SalesPersonRow['salesmancode'] == $_SESSION['CreditItems' . $identifier]->SalesPerson) {
				echo '<option selected="selected" value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			}
		}
		echo '</select></td>';
	}
	echo '</tr>';
	if (!isset($_POST['CreditText'])) {
		$_POST['CreditText'] = '';
	}
	echo '<tr>
			<td>' . __('Credit note text') . '</td>
			<td><textarea name="CreditText" cols="31" rows="5" tabindex="' . $TabIndex++ . '">' . $_POST['CreditText'] . '</textarea></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input name="Update" tabindex="' . $TabIndex++ . '" type="submit" value="' . __('Update') . '" />
			<br />';
	$j++;
	if (!isset($_POST['CreditText'])) {
		$_POST['CreditText'] = '';
	}
	echo '<input name="ProcessCredit" type="submit" tabindex="' . $TabIndex++ . '" value="' . __('Process Credit') . '" />
		</div>';
}
echo '</div>';
echo '</form>';
include('includes/footer.php');
