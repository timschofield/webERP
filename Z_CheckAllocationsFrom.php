<?php
/* $Revision: 1.5 $ */

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
	echo '<TABLE><TR>
		<TD>' . _('Type') . '</TD>
		<TD>' . _('Trans No') . '</TD>
		<TD>' . _('Ov Amt') . '</TD>
		<TD>' . _('Allocated') . '</TD>
		<TD>' . _('Tot Allcns') . '</TD></TR>';

	$RowCounter =0;
	while ($myrow=DB_fetch_array($result)){


		printf ('<TR>
			<TD>%s</TD>
			<TD>%s<TD ALIGN=RIGHT>%f.2</TD>
			<TD ALIGN=RIGHT>%f.2</TD>
			<TD ALIGN=RIGHT>%f.2</TD>
			</TR>',
			$myrow['type'],
			$myrow['transno'],
			$myrow['ovamount'],
			$myrow['alloc'],
			$myrow['totallocfrom']);
		$RowCounter++;
		if ($RowCounter==20){
			echo '<TR><TD>' . _('Type') . '</TD>
				<TD>' . _('Trans No') . '</TD>
				<TD>' . _('Ov Amt') . '</TD>
				<TD>' . _('Allocated') . '</TD>
				<TD>' . _('Tot Allcns') . '</TD></TR>';
			$RowCounter=0;
		}
	}
	echo '</TABLE>';
} else {
	prnMsg(_('There are no inconsistent allocations') . ' - ' . _('all is well'),'info');
}

include('includes/footer.inc');
?>