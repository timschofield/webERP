<?php
/* $Revision: 1.5 $ */
/*This page adds the total of allocation records and compares this to the recorded allocation total in DebtorTrans table */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Customer Allocations != DebtorTrans.Alloc');
include('includes/header.inc');

/*First off get the DebtorTransID of all invoices where allocations dont agree to the recorded allocation */
$sql = "SELECT debtortrans.id,
		debtortrans.debtorno,
		debtortrans.transno,
		ovamount+ovgst AS totamt,
		SUM(custallocns.Amt) AS totalalloc,
		debtorTrans.alloc
	FROM debtortrans
		INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
	WHERE debtortrans.type=10
	GROUP BY debtortrans.ID,
		debtortrans.type=10,
		ovamount+ovgst,
		debtortrans.alloc
	HAVING SUM(custallocns.amt) < debtortrans.alloc - 1";

$result = DB_query($sql,$db);

if (DB_num_rows($result)==0){
	prnMsg(_('There are no inconsistencies with allocations') . ' - ' . _('all is well'),'info');
}

while ($myrow = DB_fetch_array($result)){
	$AllocToID = $myrow['id'];

	echo '<BR>' . _('Allocations made against') . ' ' . $myrow['debtorno'] . ' ' . _('Invoice Number') . ': ' . $myrow['transno'];
	echo '<BR>' . _('Orginal Invoice Total') . ': '. $myrow['totamt'];
	echo '<BR>' . _('Total amount recorded as allocated against it') . ': ' . $myrow['alloc'];
	echo '<BR>' . _('Total of allocation records') . ': ' . $myrow['totalalloc'];

	$sql = 'SELECT type,
			transno,
			trandate,
			debtortrans.debtorno,
			reference,
			rate,
			ovamount+ovgst+ovfreight+ovdiscount AS totalamt,
			custallocns.amt
		FROM debtortrans
			INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocfrom
		WHERE custallocns.transid_allocto='. $AllocToID;

	$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<TABLE CELLPADDING=2 BORDER=2>';

	$tableheader = "<TR>
				<TD class='tableheader'>" . _('Type') . "</TD>
				<TD class='tableheader'>" . _('Number') . "</TD>
				<TD class='tableheader'>" . _('Reference') . "</TD>
				<TD class='tableheader'>" . _('Ex Rate') . "</TD>
				<TD class='tableheader'>" . _('Amount') . "</TD>
				<TD class='tableheader'>" . _('Alloc') . "</TD></TR>";
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter
	$AllocsTotal = 0;

	while ($myrow1=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		if ($myrow1['type']==11){
			$TransType = _('Credit Note');
		} else {
			$TransType = _('Receipt');
		}
		printf( "<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>",
			$TransType,
			$myrow1['transno'],
			$myrow1['reference'],
			$myrow1['exrate'],
			$myrow1['totalamt'],
			$myrow1['amt']);

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
		//end of page full new headings if
		$AllocsTotal +=$myrow1['amt'];
	}
	//end of while loop
	echo "<TR><TD COLSPAN = 6 ALIGN=RIGHT>" . number_format($AllocsTotal,2) . '</TD></TR>';
	echo '</TABLE><HR>';
}

echo '</FORM></CENTER>';

include('includes/footer.inc');

?>