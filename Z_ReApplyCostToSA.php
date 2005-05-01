<?php

/* $Revision: 1.6 $ */

$PageSecurity=15;

include('includes/session.inc');
$title=_('Apply Current Cost to Sales Analysis');
include('includes/header.inc');

$Period = 42;

echo "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

$SQL = 'SELECT MonthName(lastdate_in_period) AS mnth,
		YEAR(lastdate_in_period) AS yr,
		periodno
	FROM periods';
echo '<P><CENTER>' . _('Select the Period to update the costs for') . ":<SELECT NAME='PeriodNo'>";
$result = DB_query($SQL,$db);

echo '<OPTION SELECTED VALUE=0>' . _('No Period Selected');

while ($PeriodInfo=DB_fetch_array($result)){

	echo '<OPTION VALUE=' . $PeriodInfo['periodno'] . '>' . $PeriodInfo['mnth'] . ' ' . $PeriodInfo['Yr'];

}

echo '</SELECT>';

echo "<P><INPUT TYPE=SUBMIT NAME='UpdateSalesAnalysis' VALUE='" . _('Update Sales Analysis Costs') ."'></CENTER>";
echo '</FORM';

if (isset($_POST['UpdateSalesAnalysis']) AND $_POST['PeriodNo']!=0){
	$sql = 'SELECT stockmaster.stockid,
			materialcost+overheadcost+labourcost AS standardcost,
			stockmaster.mbflag
		FROM salesanalysis INNER JOIN stockmaster
			ON salesanalysis.stockid=stockmaster.stockid
		WHERE periodno=' . $_POST['PeriodNo']  . "
		AND stockmaster.mbflag<>'D' 
		GROUP BY stockmaster.stockid,
			stockmaster.materialcost,
			stockmaster.overheadcost,
			stockmaster.labourcost,
			stockmaster.mbflag";


	$ErrMsg = _('Could not retrieve the sales analysis records to be updated because');
	$result = DB_query($sql,$db,$ErrMsg);

	while ($ItemsToUpdate = DB_fetch_array($result)){

		if ($ItemsToUpdate['mbflag']=='A'){
			$SQL = "SELECT SUM(materialcost + labourcost + overheadcost) AS standardcost
					FROM stockmaster INNER JOIN BOM
						ON stockmaster.stockid = bom.component
					WHERE bom.parent = '" . $ItemsToUpdate['stockid'] . "'
					AND bom.effectiveto > '" . Date('Y-m-d') . "'
					AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

			$ErrMsg = _('Could not recalculate the current cost of the assembly item') . $ItemsToUpdate['stockid'] . ' ' . _('because');
			$AssemblyCostResult = DB_query($SQL,$db,$ErrMsg);
			$AssemblyCost = DB_fetch_row($AssemblyCostResult);
			$Cost = $AssemblyCost[0];
		} else {
			$Cost = $ItemsToUpdate['standardcost'];
		}

		$SQL = 'UPDATE salesanalysis SET cost = (qty * ' . $Cost . ")
				WHERE stockid='" . $ItemsToUpdate['stockid'] . "'
				AND periodno =" . $_POST['PeriodNo'];

		$ErrMsg = _('Could not update the sales analysis records for') . ' ' . $ItemsToUpdate['stockid'] . ' ' . _('beacuse');
		$UpdResult = DB_query($SQL,$db,$ErrMsg);


		prnMsg(_('Updated sales analysis for period') . ' ' . $_POST['PeriodNo'] . ' ' . _('and stock item') . ' ' . $ItemsToUpdate['stockid'] . ' ' . _('using a cost of') . ' ' . $Cost,'success');
	}


	prnMsg(_('Updated the sales analysis cost data for period') . ' '. $_POST['PeriodNo'],'success');
}
include('includes/footer.inc');
?>