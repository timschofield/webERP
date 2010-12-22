<?php
//$PageSecurity = 11;

include('includes/session.inc');
$title = _('Fixed Asset Properties List');
include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' .
	 _('Search') . '" alt="" />' . ' ' . $title .'</p>';

$sql='SELECT stockmaster.stockid,
			assetmanager.serialno,
			stockmaster.description,
			stockcatproperties.label,
			stockitemproperties.value
		FROM assetmanager
		LEFT JOIN stockmaster
			ON assetmanager.stockid=stockmaster.stockid
		LEFT JOIN stockcatproperties
			ON stockmaster.categoryid=stockcatproperties.categoryid
		LEFT JOIN stockitemproperties
			ON stockcatproperties.stkcatpropid=stockitemproperties.stkcatpropid
		WHERE stockmaster.stockid=stockitemproperties.stockid
		ORDER BY assetmanager.serialno,stockmaster.stockid';

$result=DB_query($sql, $db);
echo '<table class=selection>';
echo '<tr>';
echo '<th>'._('Asset Type').'</th>';
echo '<th>'._('Asset Reference').'</th>';
echo '<th>'._('Description').'</th>';
echo '<th>'._('Depreciation %').'</th>';
echo '</tr>';
while ($myrow=DB_fetch_array($result)) {
	if ($myrow['value']!='Straight Line') {
		echo '<tr>';
		echo '<td>'.$myrow['description'].'</td>
			<td>'.$myrow['serialno'].'</td>
			<td>'.$myrow['label'].'</td>
			<td class=number>'.$myrow['value'].'%</td>';
		echo '</tr>';
	}
}
echo '</table>';

include('includes/footer.inc');
?>