<?php
/* $Revision: 1.3 $ */

$PageSecurity = 15;
include ('includes/session.inc');
$title = _('Identify Allocation Stuff Ups');
include ('includes/header.inc');

$sql = 'SELECT DebtorTrans.Type,
		DebtorTrans.TransNo,
		DebtorTrans.OvAmount,
		DebtorTrans.Alloc,
		Sum(CustAllocns.Amt) As TotAllocFrom
	FROM DebtorTrans,
		CustAllocns
	WHERE TransID_AllocFrom=DebtorTrans.ID
	GROUP BY DebtorTrans.Type,
		DebtorTrans.TransNo,
		DebtorTrans.OvAmount,
		DebtorTrans.Alloc
	HAVING Sum(CustAllocns.Amt) < -Alloc';

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
			$myrow['Type'],
			$myrow['TransNo'],
			$myrow['OvAmount'],
			$myrow['Alloc'],
			$myrow['TotAllocFrom']);
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
	prnMsg(_('There are no inconsistent allocations - all is well!'),'info');
}

include('includes/footer.inc');
?>
