<?php
/* $Id$*/
$PageSecurity=15;

include('includes/session.inc');
$title=_('Check Period Sales Ledger Control Account');
include('includes/header.inc');

echo '<table>';

$Header = "<tr>
		<th>" . _('Type') . "</th>
		<th>" . _('Number') . "</th>
		<th>" . _('Period') . "</th>
		<th>" . _('Difference') . "</th>
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
	echo "<tr><td><a href='" . $rootpath . "/GLTransInquiry.php?" . SID . "&TypeID=" . $OutOfWackRow['type'] . "&TransNo=" . $OutOfWackRow['typeno'] . "'>" . $OutOfWackRow['typename'] . '</a></td><td class=number>' . $OutOfWackRow['typeno'] . '</td><td class=number>' . $OutOfWackRow['periodno'] . '</td><td class=number>' . number_format($OutOfWackRow['nettot'],3) . '</td></tr>';

}
echo '</table>';

include('includes/footer.inc');
?>
