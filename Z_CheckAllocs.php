<?php
/* $Id: Z_CheckAllocs.php 5784 2012-12-29 04:00:43Z daintree $*/
/*This page adds the total of allocation records and compares this to the recorded allocation total in DebtorTrans table */

include('includes/session.inc');
$Title = _('Customer Allocations != DebtorTrans.Alloc');
include('includes/header.inc');

/*First off get the DebtorTransID of all invoices where allocations dont agree to the recorded allocation */
$sql = "SELECT debtortrans.id,
		debtortrans.debtorno,
		debtortrans.transno,
		ovamount+ovgst AS totamt,
		SUM(custallocns.Amt) AS totalalloc,
		debtortrans.alloc
	FROM debtortrans INNER JOIN custallocns
	ON debtortrans.id=custallocns.transid_allocto
	WHERE debtortrans.type=10
	GROUP BY debtortrans.ID,
		debtortrans.type,
		ovamount+ovgst,
		debtortrans.alloc
	HAVING SUM(custallocns.amt) < debtortrans.alloc - 1";

$result = DB_query($sql,$db);

if (DB_num_rows($result)==0){
	prnMsg(_('There are no inconsistencies with allocations') . ' - ' . _('all is well'),'info');
}

while ($myrow = DB_fetch_array($result)){
	$AllocToID = $myrow['id'];

	echo '<br />' . _('Allocations made against') . ' ' . $myrow['debtorno'] . ' ' . _('Invoice Number') . ': ' . $myrow['transno'];
	echo '<br />' . _('Original Invoice Total') . ': '. $myrow['totamt'];
	echo '<br />' . _('Total amount recorded as allocated against it') . ': ' . $myrow['alloc'];
	echo '<br />' . _('Total of allocation records') . ': ' . $myrow['totalalloc'];

	$sql = "SELECT type,
				transno,
				trandate,
				debtortrans.debtorno,
				reference,
				debtortrans.rate,
				ovamount+ovgst+ovfreight+ovdiscount AS totalamt,
				custallocns.amt,
				decimalplaces AS currdecimalplaces
			FROM debtortrans INNER JOIN custallocns
			ON debtortrans.id=custallocns.transid_allocfrom
			INNER JOIN debtorsmaster ON
			debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies ON
			debtorsmaster.currcode=currencies.currabrev
			WHERE custallocns.transid_allocto='" . $AllocToID . "'";

	$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">';

	$tableheader = '<tr>
				<th>' . _('Type') . '</th>
				<th>' . _('Number') . '</th>
				<th>' . _('Reference') . '</th>
				<th>' . _('Ex Rate') . '</th>
				<th>' . _('Amount') . '</th>
				<th>' . _('Alloc') . '</th></tr>';
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter
	$AllocsTotal = 0;

	while ($myrow1=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		if ($myrow1['type']==11){
			$TransType = _('Credit Note');
		} else {
			$TransType = _('Receipt');
		}
		$CurrDecimalPlaces = $myrow1['currdecimalplaces'];

		printf( '<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$TransType,
				$myrow1['transno'],
				$myrow1['reference'],
				locale_number_format($myrow1['exrate'],4),
				locale_number_format($myrow1['totalamt'],$CurrDecimalPlaces),
				locale_number_format($myrow1['amt'],$CurrDecimalPlaces));

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
		//end of page full new headings if
		$AllocsTotal +=$myrow1['amt'];
	}
	//end of while loop
	echo '<tr><td colspan="6" class="number">' . locale_number_format($AllocsTotal,$CurrDecimalPlaces) . '</td></tr>';
	echo '</table>
		<br />';
}

include('includes/footer.inc');

?>