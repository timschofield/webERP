<?php
/*This page adds the total of allocation records and compares this to the recorded allocation total in DebtorTrans table */

include('includes/session.php');
$Title = _('Customer Allocations != DebtorTrans.Alloc');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
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
	prnMsg(_('There are no inconsistencies with allocations') . ' - ' . _('all is well'),'info');
}

while ($MyRow = DB_fetch_array($Result)){
	$AllocToID = $MyRow['id'];

	echo '<br />' . _('Allocations made against') . ' ' . $MyRow['debtorno'] . ' ' . _('Invoice Number') . ': ' . $MyRow['transno'];
	echo '<br />' . _('Original Invoice Total') . ': '. $MyRow['totamt'];
	echo '<br />' . _('Total amount recorded as allocated against it') . ': ' . $MyRow['alloc'];
	echo '<br />' . _('Total of allocation records') . ': ' . $MyRow['totalalloc'];

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

	$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
	$TransResult = DB_query($SQL,$ErrMsg);

	echo '<table class="selection">';

	$Tableheader = '<tr>
				<th>' . _('Type') . '</th>
				<th>' . _('Number') . '</th>
				<th>' . _('Reference') . '</th>
				<th>' . _('Ex Rate') . '</th>
				<th>' . _('Amount') . '</th>
				<th>' . _('Alloc') . '</th></tr>';
	echo $Tableheader;

	$RowCounter = 1;
	$AllocsTotal = 0;

	while ($MyRow1=DB_fetch_array($TransResult)) {

		if ($MyRow1['type']==11){
			$TransType = _('Credit Note');
		} else {
			$TransType = _('Receipt');
		}
		$CurrDecimalPlaces = $MyRow1['currdecimalplaces'];

		printf( '<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$TransType,
				$MyRow1['transno'],
				$MyRow1['reference'],
				locale_number_format($MyRow1['exrate'],4),
				locale_number_format($MyRow1['totalamt'],$CurrDecimalPlaces),
				locale_number_format($MyRow1['amt'],$CurrDecimalPlaces));

		$RowCounter++;
		If ($RowCounter == 12){
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

?>