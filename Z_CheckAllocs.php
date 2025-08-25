<?php
/*This page adds the total of allocation records and compares this to the recorded allocation total in DebtorTrans table */

include('includes/session.php');
$Title = __('Customer Allocations != DebtorTrans.Alloc');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

/*First off get the DebtorTransID of all invoices where allocations dont agree to the recorded allocation */
$SQL = "SELECT debtortrans.id,
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

$Result = DB_query($SQL);

if (DB_num_rows($Result)==0){
	prnMsg(__('There are no inconsistencies with allocations') . ' - ' . __('all is well'),'info');
}

while ($MyRow = DB_fetch_array($Result)){
	$AllocToID = $MyRow['id'];

	echo '<br />' . __('Allocations made against') . ' ' . $MyRow['debtorno'] . ' ' . __('Invoice Number') . ': ' . $MyRow['transno'];
	echo '<br />' . __('Original Invoice Total') . ': '. $MyRow['totamt'];
	echo '<br />' . __('Total amount recorded as allocated against it') . ': ' . $MyRow['alloc'];
	echo '<br />' . __('Total of allocation records') . ': ' . $MyRow['totalalloc'];

	$SQL = "SELECT type,
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

	$ErrMsg = __('The customer transactions for the selected criteria could not be retrieved because');
	$TransResult = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">';

	$Tableheader = '<tr>
				<th>' . __('Type') . '</th>
				<th>' . __('Number') . '</th>
				<th>' . __('Reference') . '</th>
				<th>' . __('Ex Rate') . '</th>
				<th>' . __('Amount') . '</th>
				<th>' . __('Alloc') . '</th></tr>';
	echo $Tableheader;

	$RowCounter = 1;
	$AllocsTotal = 0;

	while ($MyRow1=DB_fetch_array($TransResult)) {

		if ($MyRow1['type']==11){
			$TransType = __('Credit Note');
		} else {
			$TransType = __('Receipt');
		}
		$CurrDecimalPlaces = $MyRow1['currdecimalplaces'];

		echo '<tr class="striped_row">
				<td>', $TransType, '</td>
				<td>', $MyRow1['transno'], '</td>
				<td>', $MyRow1['reference'], '</td>
				<td>', locale_number_format($MyRow1['exrate'],4), '</td>
				<td class="number">', locale_number_format($MyRow1['totalamt'],$CurrDecimalPlaces), '</td>
				<td class="number">', locale_number_format($MyRow1['amt'],$CurrDecimalPlaces), '</td>
			</tr>';

		$RowCounter++;
		if ($RowCounter == 12){
			$RowCounter=1;
			echo $Tableheader;
		}
		//end of page full new headings if
		$AllocsTotal +=$MyRow1['amt'];
	}
	//end of while loop
	echo '<tr><td colspan="6" class="number">' . locale_number_format($AllocsTotal,$CurrDecimalPlaces) . '</td></tr>';
	echo '</table>
		<br />';
}

include('includes/footer.php');
