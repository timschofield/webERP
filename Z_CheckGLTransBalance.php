<?php
/* $Revision: 1.3 $ */
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

$sql = 'SELECT gltrans.type,
		systypes.typename,
		gltrans.typeno,
		periodno,
		SUM(amount) AS nettot
	FROM gltrans,
		systypes
	WHERE gltrans.type = systypes.typeid
	GROUP BY gltrans.type,
		systypes.typename,
		typeno,
		periodno
	HAVING ABS(SUM(amount))>0.01';

$OutOfWackResult = DB_query($sql,$db);


$RowCounter =0;

while ($OutOfWackRow = DB_fetch_array($OutOfWackResult)){

	if ($RowCounter==18){
		$RowCounter=0;
		echo $Header;
	} else {
		$RowCounter++;
	}
	echo "<TR><TD><A HREF='" . $rootpath . "/GLTransInquiry.php?" . SID . "&TypeID=" . $OutOfWackRow['type'] . "&TransNo=" . $OutOfWackRow['typeno'] . "'>" . $OutOfWackRow['typename'] . '</A></TD><TD ALIGN=RIGHT>' . $OutOfWackRow['typeno'] . '</TD><TD ALIGN=RIGHT>' . $OutOfWackRow['periodno'] . '</TD><TD ALIGN=RIGHT>' . number_format($OutOfWackRow['nettot'],3) . '</TD></TR>';

}
echo '</TABLE>';

include('includes/footer.inc');
?>
