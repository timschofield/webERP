<?php
/* $Id: SuppWhereAlloc.php 7449 2016-01-14 10:08:51Z exsonqu $*/
/* Suppliers Where allocated */

include('includes/session.php');
$Title = _('Supplier How Paid Inquiry');
$ViewTopic = 'APInquiries';
$BookMark = 'WhereAllocated';
include('includes/header.php');

if(isset($_GET['TransNo']) AND isset($_GET['TransType'])) {
	$_POST['TransNo'] = (int)$_GET['TransNo'];
	$_POST['TransType'] = (int)$_GET['TransType'];
	$_POST['ShowResults'] = true;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<p class="page_title_text noprint"><img alt="" src="'. $RootPath. '/css/'. $Theme.
	'/images/money_add.png" title="',// Icon image.
	_('Supplier Where Allocated'), '" /> ',// Icon title.
	$Title. '</p>',// Page title.
	'<table class="selection noprint">
	<tr>
		<td>' . _('Type') . ':</td>
		<td><select tabindex="1" name="TransType"> ';

if(!isset($_POST['TransType'])) {
	$_POST['TransType']='20';
}
if($_POST['TransType']==20) {
	 echo '<option selected="selected" value="20">' . _('Purchase Invoice') . '</option>
			<option value="22">' . _('Payment') . '</option>
			<option value="21">' . _('Debit Note') . '</option>';
} elseif($_POST['TransType'] == 22) {
	echo '<option selected="selected" value="22">' . _('Payment') . '</option>
			<option value="20">' . _('Purchase Invoice') . '</option>
			<option value="21">' . _('Debit Note') . '</option>';
} elseif($_POST['TransType'] == 21) {
	echo '<option selected="selected" value="21">' . _('Debit Note') . '</option>
		<option value="20">' . _('Purchase Invoice') . '</option>
		<option value="22">' . _('Payment') . '</option>';
}

echo '</select></td>';

if(!isset($_POST['TransNo'])) {$_POST['TransNo']='';}
echo '<td>' . _('Transaction Number').':</td>
		<td><input tabindex="2" type="text" class="number" name="TransNo"  required="required" maxlength="20" size="20" value="'. $_POST['TransNo'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre noprint">
		<input tabindex="3" type="submit" name="ShowResults" value="' . _('Show How Allocated') . '" />
	</div>';

if(isset($_POST['ShowResults']) AND  $_POST['TransNo']=='') {
	echo '<br />';
	prnMsg(_('The transaction number to be queried must be entered first'),'warn');
}

if(isset($_POST['ShowResults']) AND $_POST['TransNo']!='') {

/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$sql = "SELECT supptrans.id,
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
			$sql .= " AND supptrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$result = DB_query($sql);

	if(DB_num_rows($result) > 0) {
		$myrow = DB_fetch_array($result);
		$AllocToID = $myrow['id'];
		$CurrCode = $myrow['currcode'];
		$CurrDecimalPlaces = $myrow['currdecimalplaces'];
		$sql = "SELECT type,
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

			$TitleInfo = ($_POST['TransType'] == 22)?_('Payment'):_('Debit Note');
			$sql .= "ON supptrans.id = suppallocs.transid_allocto
				WHERE suppallocs.transid_allocfrom = '" . $AllocToID . "'";
		} else {
			$TitleInfo = _('invoice');
			$sql .= "ON supptrans.id = suppallocs.transid_allocfrom
				WHERE suppallocs.transid_allocto = '" . $AllocToID . "'";
		}
		$sql .= " ORDER BY transno ";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($sql, $ErrMsg);

		if(DB_num_rows($TransResult)==0) {

			if($myrow['totamt']>0 AND ($_POST['TransType']==22 OR $_POST['TransType'] == 21)) {
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
						<b>' . _('Allocations made against') . ' ' . $TitleInfo . ' ' . _('number') . ' ' . $_POST['TransNo'] . '<br />' . _('Transaction Total').': '. locale_number_format($myrow['totamt'],$CurrDecimalPlaces) . ' ' . $CurrCode . '</b>
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
			$k = 0; //row colour counter
			$AllocsTotal = 0;

			while($myrow=DB_fetch_array($TransResult)) {
				if($k==1) {
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}

				if($myrow['type']==21) {
					$TransType = _('Debit Note');
				} elseif($myrow['type'] == 20) {
					$TransType = _('Purchase Invoice');
				} else {
					$TransType = _('Payment');
				}
				echo '	<td class="centre">', ConvertSQLDate($myrow['trandate']), '</td>
						<td class="text">' . $TransType . '</td>
						<td class="number">' . $myrow['transno'] . '</td>
						<td class="text">' . $myrow['suppreference'] . '</td>
						<td class="number">' . $myrow['rate'] . '</td>
						<td class="number">' . locale_number_format($myrow['totalamt'], $CurrDecimalPlaces) . '</td>
						<td class="number">' . locale_number_format($myrow['amt'], $CurrDecimalPlaces) . '</td>
					</tr>';

				$RowCounter++;
				if($RowCounter == 22) {
					$RowCounter=1;
					echo $TableHeader;
				}
				//end of page full new headings if
				$AllocsTotal += $myrow['amt'];
			}
			//end of while loop
			echo '<tr>
					<td class="number" colspan="6">' . _('Total allocated') . '</td>
					<td class="number">' . locale_number_format($AllocsTotal, $CurrDecimalPlaces) . '</td>
				</tr>
				</tbody></table>
				</div>';
		} // end if there are allocations against the transaction
	} //got the ID of the transaction to find allocations for
}
echo '</div>';
echo '</form>';
if(isset($Printer)) {
	echo '<div class="centre noprint">
			<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', _('Print'), '</button>', // "Print" button.
		'</div>';
}
include('includes/footer.php');
?>
