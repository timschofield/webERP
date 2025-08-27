<?php

/* Suppliers Where allocated */

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier How Paid Inquiry');
$ViewTopic = 'APInquiries';
$BookMark = 'WhereAllocated';
include('includes/header.php');

if(isset($_GET['TransNo']) AND isset($_GET['TransType'])) {
	$_POST['TransNo'] = (int)$_GET['TransNo'];
	$_POST['TransType'] = (int)$_GET['TransType'];
	$_POST['ShowResults'] = true;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<p class="page_title_text noPrint">
		<img alt="" src="'. $RootPath. '/css/'. $Theme.'/images/money_add.png" title="',__('Supplier Where Allocated'), '" /> ',$Title. '
	</p>';

echo '<fieldset>
		<legend>', __('Inquiry Critertia'), '</legend>
		<field>
			<label for="TransType">' . __('Type') . ':</label>
			<select tabindex="1" name="TransType"> ';

if(!isset($_POST['TransType'])) {
	$_POST['TransType']='20';
}
if($_POST['TransType']==20) {
	 echo '<option selected="selected" value="20">' . __('Purchase Invoice') . '</option>
			<option value="22">' . __('Payment') . '</option>
			<option value="21">' . __('Debit Note') . '</option>';
} elseif($_POST['TransType'] == 22) {
	echo '<option selected="selected" value="22">' . __('Payment') . '</option>
			<option value="20">' . __('Purchase Invoice') . '</option>
			<option value="21">' . __('Debit Note') . '</option>';
} elseif($_POST['TransType'] == 21) {
	echo '<option selected="selected" value="21">' . __('Debit Note') . '</option>
		<option value="20">' . __('Purchase Invoice') . '</option>
		<option value="22">' . __('Payment') . '</option>';
}

echo '</select>
	</field>';

if(!isset($_POST['TransNo'])) {$_POST['TransNo']='';}
echo '<field>
		<label for="TransNo">' . __('Transaction Number').':</label>
		<input tabindex="2" type="text" class="number" name="TransNo"  required="required" maxlength="20" size="20" value="'. $_POST['TransNo'] . '" />
	</field>
	</fieldset>
	<div class="centre noPrint">
		<input tabindex="3" type="submit" name="ShowResults" value="' . __('Show How Allocated') . '" />
	</div>';

if(isset($_POST['ShowResults']) AND  $_POST['TransNo']=='') {
	echo '<br />';
	prnMsg(__('The transaction number to be queried must be entered first'),'warn');
}

if(isset($_POST['ShowResults']) AND $_POST['TransNo']!='') {

/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$SQL = "SELECT supptrans.id,
				ovamount+ovgst AS totamt,
				currencies.decimalplaces AS currdecimalplaces,
				suppliers.currcode
			FROM supptrans INNER JOIN suppliers
			ON supptrans.supplierno=suppliers.supplierid
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE type='" . $_POST['TransType'] . "'
			AND transno = '" . $_POST['TransNo']."'";

	if($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND supptrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$Result = DB_query($SQL);

	if(DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		$AllocToID = $MyRow['id'];
		$CurrCode = $MyRow['currcode'];
		$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
		$SQL = "SELECT type,
					transno,
					trandate,
					supptrans.supplierno,
					suppreference,
					supptrans.rate,
					ovamount+ovgst as totalamt,
					suppallocs.amt
				FROM supptrans
				INNER JOIN suppallocs ";
		if($_POST['TransType']==22 OR $_POST['TransType'] == 21) {

			$TitleInfo = ($_POST['TransType'] == 22)?__('Payment'):__('Debit Note');
			$SQL .= "ON supptrans.id = suppallocs.transid_allocto
				WHERE suppallocs.transid_allocfrom = '" . $AllocToID . "'";
		} else {
			$TitleInfo = __('invoice');
			$SQL .= "ON supptrans.id = suppallocs.transid_allocfrom
				WHERE suppallocs.transid_allocto = '" . $AllocToID . "'";
		}
		$SQL .= " ORDER BY transno ";

		$ErrMsg = __('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);

		if(DB_num_rows($TransResult)==0) {

			if($MyRow['totamt']>0 AND ($_POST['TransType']==22 OR $_POST['TransType'] == 21)) {
					prnMsg(__('This transaction was a receipt of funds and there can be no allocations of receipts or credits to a receipt. This inquiry is meant to be used to see how a payment which is entered as a negative receipt is settled against credit notes or receipts'),'info');
			} else {
				prnMsg(__('There are no allocations made against this transaction'),'info');
			}
		} else {
			$Printer = true;
			echo '<br />
				<div id="Report">
				<table class="selection">
				<thead>
				<tr>
					<th class="centre" colspan="7">
						<b>' . __('Allocations made against') . ' ' . $TitleInfo . ' ' . __('number') . ' ' . $_POST['TransNo'] . '<br />' . __('Transaction Total').': '. locale_number_format($MyRow['totamt'],$CurrDecimalPlaces) . ' ' . $CurrCode . '</b>
					</th>
				</tr>';

			$TableHeader = '<tr>
					<th class="centre">' . __('Date') . '</th>
					<th class="text">' . __('Type') . '</th>
					<th class="number">' . __('Number') . '</th>
					<th class="text">' . __('Reference') . '</th>
					<th class="number">' . __('Ex Rate') . '</th>
					<th class="number">' . __('Amount') . '</th>
					<th class="number">' . __('Alloc') . '</th>
				</tr>';
			echo $TableHeader,
				'</thead>
				<tbody>';

			$RowCounter = 1;
			$AllocsTotal = 0;

			while($MyRow=DB_fetch_array($TransResult)) {
				if($MyRow['type']==21) {
					$TransType = __('Debit Note');
				} elseif($MyRow['type'] == 20) {
					$TransType = __('Purchase Invoice');
				} else {
					$TransType = __('Payment');
				}
				echo '<tr class="striped_row">
						<td class="centre">', ConvertSQLDate($MyRow['trandate']), '</td>
						<td class="text">' . $TransType . '</td>
						<td class="number">' . $MyRow['transno'] . '</td>
						<td class="text">' . $MyRow['suppreference'] . '</td>
						<td class="number">' . $MyRow['rate'] . '</td>
						<td class="number">' . locale_number_format($MyRow['totalamt'], $CurrDecimalPlaces) . '</td>
						<td class="number">' . locale_number_format($MyRow['amt'], $CurrDecimalPlaces) . '</td>
					</tr>';

				$RowCounter++;
				if($RowCounter == 22) {
					$RowCounter=1;
					echo $TableHeader;
				}
				//end of page full new headings if
				$AllocsTotal += $MyRow['amt'];
			}
			//end of while loop
			echo '<tr>
					<td class="number" colspan="6">' . __('Total allocated') . '</td>
					<td class="number">' . locale_number_format($AllocsTotal, $CurrDecimalPlaces) . '</td>
				</tr>
				</tbody></table>
				</div>';
		} // end if there are allocations against the transaction
	} //got the ID of the transaction to find allocations for
}
echo '</form>';
if(isset($Printer)) {
	echo '<div class="centre noPrint">
			<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', __('Print'), '</button>', // "Print" button.
		'</div>';
}
include('includes/footer.php');
