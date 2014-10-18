<?php
/* $Id: Z_CheckAllocationsFrom.php 5784 2012-12-29 04:00:43Z daintree $*/

include ('includes/session.inc');
$Title = _('Identify Allocation Stuff Ups');
include ('includes/header.inc');

$sql = "SELECT debtortrans.type,
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

$result =DB_query($sql,$db);

if (DB_num_rows($result)>0){
	echo '<table>
		<tr>
			<td>' . _('Type') . '</td>
			<td>' . _('Trans No') . '</td>
			<td>' . _('Ov Amt') . '</td>
			<td>' . _('Allocated') . '</td>
			<td>' . _('Tot Allcns') . '</td>
		</tr>';

	$RowCounter =0;
	while ($myrow=DB_fetch_array($result)){


		printf ('<tr>
				<td>%s</td>
				<td>%s<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$myrow['type'],
				$myrow['transno'],
				locale_number_format($myrow['ovamount'],$myrow['currdecimalplaces']),
				locale_number_format($myrow['alloc'],$myrow['currdecimalplaces']),
				locale_number_format($myrow['totallocfrom'],$myrow['currdecimalplaces']));

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

include('includes/footer.inc');
?>