<?php

/* $Id: CustWhereAlloc.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Customer How Paid Inquiry');

$ViewTopic = 'ARInquiries';
$BookMark = 'WhereAllocated';

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .	_('Customer Where Allocated'). '" alt="" />' . $Title . '
	</p>
	<table class="selection">
	<tr>
		<td>' . _('Type') . ':</td>
		<td><select tabindex="1" name="TransType"> ';

if (!isset($_POST['TransType'])){
	$_POST['TransType']='10';
}
if ($_POST['TransType']==10){
	 echo '<option selected="selected" value="10">' . _('Invoices') . '</option>
			<option value="12">' . _('Negative Receipts (Payments)') . '</option>';
} else {
	 echo '<option selected="selected" value="12">' . _('Negative Receipts (Payments)') . '</option>
			<option selected="selected" value="10">' . _('Invoices') . '</option>';
}

echo '</select></td>';

if (!isset($_POST['TransNo'])) {$_POST['TransNo']='';}
echo '<td>' . _('Transaction Number').':</td>
		<td><input tabindex="2" type="text" class="number" name="TransNo"  required="required" maxlength="10" size="10" value="'. $_POST['TransNo'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="3" type="submit" name="ShowResults" value="' . _('Show How Allocated') . '" />
	</div>';

if (isset($_POST['ShowResults']) AND  $_POST['TransNo']==''){
	echo '<br />';
	prnMsg(_('The transaction number to be queried must be entered first'),'warn');
}

if (isset($_POST['ShowResults']) AND $_POST['TransNo']!=''){


/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$sql = "SELECT debtortrans.id,
				ovamount+ovgst AS totamt,
				currencies.decimalplaces AS currdecimalplaces,
				debtorsmaster.currcode
			FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE type='" . $_POST['TransType'] . "'
			AND transno = '" . $_POST['TransNo']."'";

	$result = DB_query($sql , $db);

	if (DB_num_rows($result)==1){
		$myrow = DB_fetch_array($result);
		$AllocToID = $myrow['id'];
		$CurrCode = $myrow['currcode'];
		$CurrDecimalPlaces = $myrow['currdecimalplaces'];

		$sql = "SELECT type,
					transno,
					trandate,
					debtortrans.debtorno,
					reference,
					debtortrans.rate,
					ovamount+ovgst+ovfreight+ovdiscount as totalamt,
					custallocns.amt
				FROM debtortrans
				INNER JOIN custallocns
				ON debtortrans.id=custallocns.transid_allocfrom
				WHERE custallocns.transid_allocto='". $AllocToID."'";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($sql, $db, $ErrMsg);

		if (DB_num_rows($TransResult)==0){
			
			if ($myrow['totamt']<0 AND $_POST['TransType']==12){
					prnMsg(_('This transaction was a receipt of funds and there can be no allocations of receipts or credits to a receipt. This inquiry is meant to be used to see how a payment which is entered as a negative receipt is settled against credit notes or receipts'),'info');
			} else {
				prnMsg(_('There are no allocations made against this transaction'),'info');
			}
		} else {
			echo '<br />
				<table class="selection">';

			echo '<tr>
					<th colspan="6">
					<div class="centre">
						<b>' . _('Allocations made against invoice number') . ' ' . $_POST['TransNo'] . '<br />' . _('Transaction Total').': '. locale_number_format($myrow['totamt'],$CurrDecimalPlaces) . ' ' . $CurrCode . '</b>
					</div>
					</th>
				</tr>';

			$TableHeader = '<tr>
								<th>' . _('Type') . '</th>
								<th>' . _('Number') . '</th>
								<th>' . _('Reference') . '</th>
								<th>' . _('Ex Rate') . '</th>
								<th>' . _('Amount') . '</th>
								<th>' . _('Alloc') . '</th>
							</tr>';
			echo $TableHeader;

			$RowCounter = 1;
			$k = 0; //row colour counter
			$AllocsTotal = 0;

			while ($myrow=DB_fetch_array($TransResult)) {
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}

				if ($myrow['type']==11){
					$TransType = _('Credit Note');
				} else {
					$TransType = _('Receipt');
				}
				echo '<td>' . $TransType . '</td>
					<td>' . $myrow['transno'] . '</td>
					<td>' . $myrow['reference'] . '</td>
					<td>' . $myrow['rate'] . '</td>
					<td class="number">' . locale_number_format($myrow['totalamt'],$CurrDecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($myrow['amt'],$CurrDecimalPlaces) . '</td>
					</tr>';

				$RowCounter++;
				If ($RowCounter == 12){
					$RowCounter=1;
					echo $TableHeader;
				}
				//end of page full new headings if
				$AllocsTotal +=$myrow['amt'];
			}
			//end of while loop
			echo '<tr>
					<td colspan="5" class="number">' . _('Total allocated') . '</td>
					<td class="number">' . locale_number_format($AllocsTotal,$CurrDecimalPlaces) . '</td>
				</tr>
				</table>';
		} // end if there are allocations against the transaction
	} //got the ID of the transaction to find allocations for
}
echo '</div>';
echo '</form>';
include('includes/footer.inc');

?>