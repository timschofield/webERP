<?php
/* $Revision: 1.6 $ */

$PageSecurity = 15;
include ('includes/session.inc');
$title = _('Identify Allocation Stuff Ups');
include ('includes/header.inc');

$sql = 'SELECT debtortrans.type,
		debtortrans.transno,
		debtortrans.ovamount,
		debtortrans.alloc,
		SUM(custallocns.amt) AS totallocfrom
	FROM debtortrans,
		custallocns
	WHERE transid_allocfrom=debtortrans.id
	GROUP BY debtortrans.type,
		debtortrans.transno,
		debtortrans.ovamount,
		debtortrans.alloc
	HAVING SUM(custallocns.amt) < -alloc';

$result =DB_query($sql,$db);

if (DB_num_rows($result)>0){
	echo '<table><tr>
		<td>' . _('Type') . '</td>
		<td>' . _('Trans No') . '</td>
		<td>' . _('Ov Amt') . '</td>
		<td>' . _('Allocated') . '</td>
		<td>' . _('Tot Allcns') . '</td></tr>';

	$RowCounter =0;
	while ($myrow=DB_fetch_array($result)){


		printf ('<tr>
			<td>%s</td>
			<td>%s<td align=right>%f.2</td>
			<td align=right>%f.2</td>
			<td align=right>%f.2</td>
			</tr>',
			$myrow['type'],
			$myrow['transno'],
			$myrow['ovamount'],
			$myrow['alloc'],
			$myrow['totallocfrom']);
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