<?php
/* $Revision: 1.1 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Check Period Sales Ledger Control Account');
include('includes/header.inc');

echo '<TABLE>';

$Header = "<tr>
		<td class='tableheader'>" . _('Type') . "</td>
		<td class='tableheader'>" . _('Number') . "</td>
		<td class='tableheader'>" . _('Period') . "</td>
		<td class='tableheader'>" . _('Difference') . "</td>
		</tr>";

echo $Header;

$sql = 'SELECT GLTrans.Type,
		SysTypes.TypeName,
		GLTrans.TypeNo,
		PeriodNo,
		Sum(Amount) AS NetTot
	FROM GLTrans,
		SysTypes
	WHERE GLTrans.Type = SysTypes.TypeID
	GROUP BY GLTrans.Type,
		SysTypes.TypeName,
		TypeNo,
		PeriodNo
	HAVING ABS(Sum(Amount))>0.01';

$OutOfWackResult = DB_query($sql,$db);


$RowCounter =0;

while ($OutOfWackRow = DB_fetch_array($OutOfWackResult)){

	if ($RowCounter==18){
		$RowCounter=0;
		echo $Header;
	} else {
		$RowCounter++;
	}
	echo "<TR><TD><A HREF='" . $rootpath . "/GLTransInquiry.php?" . SID . "&TypeID=" . $OutOfWackRow['Type'] . "&TransNo=" . $OutOfWackRow['TypeNo'] . "'>" . $OutOfWackRow['TypeName'] . '</A></TD><TD ALIGN=RIGHT>' . $OutOfWackRow['TypeNo'] . '</TD><TD ALIGN=RIGHT>' . $OutOfWackRow['PeriodNo'] . '</TD><TD ALIGN=RIGHT>' . number_format($OutOfWackRow['NetTot'],3) . '</TD></TR>';

}
echo '</TABLE>';

include('includes/footer.inc');
?>