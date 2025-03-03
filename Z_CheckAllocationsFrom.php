<?php

include ('includes/session.php');
$Title = _('Identify Allocation Stuff Ups');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
include ('includes/header.php');

$SQL = "SELECT debtortrans.type,
		debtortrans.transno,
		debtortrans.ovamount,
		debtortrans.alloc,
		currencies.decimalplaces AS currdecimalplaces,
		SUM(custallocns.amt) AS totallocfrom
	FROM debtortrans INNER JOIN custallocns
	ON debtortrans.id=custallocns.transid_allocfrom
	INNER JOIN debtorsmaster ON
	debtortrans.debtorno=debtorsmaster.debtorno
	INNER JOIN currencies ON
	debtorsmaster.currcode=currencies.currabrev
	GROUP BY debtortrans.type,
		debtortrans.transno,
		debtortrans.ovamount,
		debtortrans.alloc,
		currencies.decimalplaces
	HAVING SUM(custallocns.amt) < -alloc";

$Result =DB_query($SQL);

if (DB_num_rows($Result)>0){
	echo '<table>
			<tr>
				<td>' . _('Type') . '</td>
				<td>' . _('Trans No') . '</td>
				<td>' . _('Ov Amt') . '</td>
				<td>' . _('Allocated') . '</td>
				<td>' . _('Tot Allcns') . '</td>
			</tr>';

	$RowCounter =0;
	while ($MyRow=DB_fetch_array($Result)){


		echo '<tr>
				<td>', $MyRow['type'], '</td>
				<td>', $MyRow['transno'], '<td class="number">', locale_number_format($MyRow['ovamount'],$MyRow['currdecimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['alloc'],$MyRow['currdecimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['totallocfrom'],$MyRow['currdecimalplaces']), '</td>
			</tr>';

		$RowCounter++;
		if ($RowCounter==20){
			echo '<tr><td>' . _('Type') . '</td>
				<td>' . _('Trans No') . '</td>
				<td>' . _('Ov Amt') . '</td>
				<td>' . _('Allocated') . '</td>
				<td>' . _('Tot Allcns') . '</td></tr>';
			$RowCounter=0;
		}
	}
	echo '</table>';
} else {
	prnMsg(_('There are no inconsistent allocations') . ' - ' . _('all is well'),'info');
}

include('includes/footer.php');
?>