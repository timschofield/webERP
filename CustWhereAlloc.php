<?php
/* Shows to which invoices a receipt was allocated to */

include('includes/session.php');
$Title = _('Customer How Paid Inquiry');
$ViewTopic = 'ARInquiries';
$BookMark = 'WhereAllocated';
include('includes/header.php');

if(isset($_GET['TransNo']) AND isset($_GET['TransType'])) {
	$_POST['TransNo'] = (int)$_GET['TransNo'];
	$_POST['TransType'] = (int)$_GET['TransType'];
	$_POST['ShowResults'] = true;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint">
		<img alt="" src="'. $RootPath. '/css/'. $Theme.'/images/money_add.png" title="',_('Customer Where Allocated'), '" /> ',$Title. '
	</p>';// Page title.

echo '<fieldset>
		<field>
			<label for="TransType">' . _('Type') . ':</label>
			<select tabindex="1" name="TransType">';

if(!isset($_POST['TransType'])) {
	$_POST['TransType']='10';
}
if($_POST['TransType']==10) {
	 echo '<option selected="selected" value="10">' . _('Invoice') . '</option>
			<option value="12">' . _('Receipt') . '</option>
			<option value="11">' . _('Credit Note') . '</option>';
} elseif($_POST['TransType'] == 12) {
	echo '<option selected="selected" value="12">' . _('Receipt') . '</option>
			<option value="10">' . _('Invoice') . '</option>
			<option value="11">' . _('Credit Note') . '</option>';
} elseif($_POST['TransType'] == 11) {
	echo '<option selected="selected" value="11">' . _('Credit Note') . '</option>
		<option value="10">' . _('Invoice') . '</option>
		<option value="12">' . _('Receipt') . '</option>';
}

echo '</select>
	</field>';

if(!isset($_POST['TransNo'])) {$_POST['TransNo']='';}
echo '<field>
		<label for="TransNo">' . _('Transaction Number').':</label>
		<input tabindex="2" type="text" class="number" name="TransNo"  required="required" maxlength="10" size="10" value="'. $_POST['TransNo'] . '" />
	</field>
	</fieldset>
	<div class="centre noPrint">
		<input tabindex="3" type="submit" name="ShowResults" value="' . _('Show How Allocated') . '" />
	</div>';

if(isset($_POST['ShowResults']) AND  $_POST['TransNo']=='') {
	echo '<br />';
	prnMsg(_('The transaction number to be queried must be entered first'),'warn');
}

if(isset($_POST['ShowResults']) AND $_POST['TransNo']!='') {

/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$SQL = "SELECT debtortrans.id,
				ovamount+ovgst AS totamt,
				currencies.decimalplaces AS currdecimalplaces,
				debtorsmaster.currcode,
				debtortrans.rate
			FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE type='" . $_POST['TransType'] . "'
			AND transno = '" . $_POST['TransNo']."'";

	if($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$Result = DB_query($SQL );
	$GrandTotal = 0;
	$Rows = DB_num_rows($Result);
	if($Rows>=1) {
		while($MyRow = DB_fetch_array($Result)) {
		$GrandTotal +=$MyRow['totamt'];
		$Rate = $MyRow['rate'];
		$AllocToID = $MyRow['id'];
		$CurrCode = $MyRow['currcode'];
		$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
		$SQL = "SELECT type,
					transno,
					trandate,
					debtortrans.debtorno,
					reference,
					debtortrans.rate,
					ovamount+ovgst+ovfreight+ovdiscount as totalamt,
					custallocns.amt
				FROM debtortrans
				INNER JOIN custallocns ";
		if($_POST['TransType']==12 OR $_POST['TransType'] == 11) {

			$TitleInfo = ($_POST['TransType'] == 12)?_('Receipt'):_('Credit Note');
			if($MyRow['totamt']<0) {
				$SQL .= "ON debtortrans.id = custallocns.transid_allocto
					WHERE custallocns.transid_allocfrom = '" . $AllocToID . "'";
			} else {
				$SQL .= "ON debtortrans.id = custallocns.transid_allocfrom
					WHERE custallocns.transid_allocto = '" . $AllocToID . "'";

			}

		} else {
			$TitleInfo = _('invoice');
			$SQL .= "ON debtortrans.id = custallocns.transid_allocfrom
				WHERE custallocns.transid_allocto = '" . $AllocToID . "'";
		}
		$SQL .= " ORDER BY transno ";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);

		if(DB_num_rows($TransResult)==0) {

			if($MyRow['totamt']<0 AND ($_POST['TransType']==12 OR $_POST['TransType'] == 11)) {
					prnMsg(_('This transaction was a receipt of funds and there can be no allocations of receipts or credits to a receipt. This inquiry is meant to be used to see how a payment which is entered as a negative receipt is settled against credit notes or receipts'),'info');
			} else {
				prnMsg(_('There are no allocations made against this transaction'),'info');
			}
		} else {
			$Printer = true;
			echo '<br />
				<div id="Report">
				<table class="selection">
				<thead>
				<tr>
					<th class="centre" colspan="7">
						<b>' . _('Allocations made against') . ' ' . $TitleInfo . ' ' . _('number') . ' ' . $_POST['TransNo'] . '<br />' .
						_('Transaction Total').': '. locale_number_format($MyRow['totamt'],$CurrDecimalPlaces) . ' ' . $CurrCode . '</b>
					</th>
				</tr>';

			$TableHeader = '<tr>
					<th class="centre">' . _('Date') . '</th>
					<th class="text">' . _('Type') . '</th>
					<th class="number">' . _('Number') . '</th>
					<th class="text">' . _('Reference') . '</th>
					<th class="number">' . _('Ex Rate') . '</th>
					<th class="number">' . _('Amount') . '</th>
					<th class="number">' . _('Alloc') . '</th>
				</tr>';
			echo $TableHeader,
				'</thead>
				<tbody>';

			$RowCounter = 1;
			$AllocsTotal = 0;

			while($MyRow=DB_fetch_array($TransResult)) {

				if($MyRow['type']==11) {
					$TransType = _('Credit Note');
				} elseif($MyRow['type'] == 10) {
					$TransType = _('Invoice');
				} else {
					$TransType = _('Receipt');
				}
				echo '<tr class="striped_row">
						<td class="centre">', ConvertSQLDate($MyRow['trandate']), '</td>
						<td class="text">' . $TransType . '</td>
						<td class="number">' . $MyRow['transno'] . '</td>
						<td class="text">' . $MyRow['reference'] . '</td>
						<td class="number">' . $MyRow['rate'] . '</td>
						<td class="number">' . locale_number_format($MyRow['totalamt'], $CurrDecimalPlaces) . '</td>
						<td class="number">' . locale_number_format($MyRow['amt'], $CurrDecimalPlaces) . '</td>
					</tr>';

				$RowCounter++;
				if($RowCounter == 12) {
					$RowCounter=1;
					echo $TableHeader;
				}
				//end of page full new headings if
				$AllocsTotal += $MyRow['amt'];
			}
			//end of while loop
			echo '<tr>
					<td class="number" colspan="6">' . _('Total allocated') . '</td>
					<td class="number">' . locale_number_format($AllocsTotal,$CurrDecimalPlaces) . '</td>
				</tr>

</tbody></table>
			</div>';
		} // end if there are allocations against the transaction
	} //got the ID of the transaction to find allocations for
} //end of while loop;
if ($Rows>1) {
	echo '<div class="centre"><b>' . _('Transaction Total'). '</b> ' .locale_number_format($GrandTotal,$CurrDecimalPlaces) . '</div>';
}
if ($_POST['TransType']== 12) {
	//retrieve transaction to see if there are any transaction fee,
	$SQL = "SELECT account,
						amount
					FROM gltrans LEFT JOIN bankaccounts ON account=accountcode
					WHERE type=12 AND typeno='".$_POST['TransNo']."' AND account !='". $_SESSION['CompanyRecord']['debtorsact'] ."' AND accountcode IS NULL";
	$ErrMsg = _('Failed to retrieve charge data');
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result)>0) {
		while ($MyRow = DB_fetch_array($Result)){
			echo '<div class="centre">
							<strong>'._('GL Account') .' ' . $MyRow['account'] . '</strong> '. _('Amount') . locale_number_format($MyRow['amount'],$CurrDecimalPlaces).'<br/> '. _('To local currency'). ' ' . locale_number_format($MyRow['amount']*$Rate,$CurrDecimalPlaces).' ' . _('at rate') . ' ' . $Rate .

					'</div>';
					$GrandTotal += $MyRow['amount'] * $Rate;
		}
		echo '<div class="centre">
					<strong>' . _('Grand Total') . '</strong>' . ' ' . locale_number_format($GrandTotal,$CurrDecimalPlaces).'
		</div>';
	}
}
}
echo '</div>';
echo '</form>';
if(isset($Printer)) {
	echo '<div class="centre noPrint">
			<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
		'</div>';// "Print This" button.
}
include('includes/footer.php');
