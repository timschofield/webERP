<?php
/* $Revision: 1.3 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Apply Current Cost to Sales Analysis');
include('includes/header.inc');

$Period = 42;

echo "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

$SQL = 'SELECT MonthName(LastDate_In_Period) AS Mnth,
		YEAR(LastDate_In_Period) AS Yr,
		PeriodNo
	FROM Periods';
echo '<P><CENTER>' . _('Select the Period to update the costs for') . ":<SELECT NAME='PeriodNo'>";
$result = DB_query($SQL,$db);

echo '<OPTION SELECTED VALUE=0>' . _('No Period Selected');

while ($PeriodInfo=DB_fetch_array($result)){

	echo '<OPTION VALUE=' . $PeriodInfo['PeriodNo'] . '>' . $PeriodInfo['Mnth'] . ' ' . $PeriodInfo['Yr'];

}

echo '</SELECT>';

echo "<P><INPUT TYPE=SUBMIT NAME='UpdateSalesAnalysis' VALUE='" . _('Update Sales Analysis Costs') ."'></CENTER>";
echo '</FORM';

if (isset($_POST['UpdateSalesAnalysis']) AND $_POST['PeriodNo']!=0){
	$sql = 'SELECT StockMaster.StockID,
			MaterialCost+OverheadCost+LabourCost AS StandardCost,
			StockMaster.MBflag
		FROM SalesAnalysis INNER JOIN StockMaster
			ON SalesAnalysis.StockID=StockMaster.StockID
		WHERE PeriodNo=' . $_POST['PeriodNo']  . "
		AND MBflag<>'D' GROUP BY StockID";


	$ErrMsg = _('Could not retrieve the sales analysis records to be updated because');
	$result = DB_query($sql,$db,$ErrMsg);

	while ($ItemsToUpdate = DB_fetch_array($result)){

		if ($ItemsToUpdate['MBflag']=='A'){
			$SQL = "SELECT Sum(MaterialCost + LabourCost + OverheadCost) AS StandardCost
					FROM StockMaster INNER JOIN BOM
						ON StockMaster.StockID = BOM.Component
					WHERE BOM.Parent = '" . $ItemsToUpdate['StockID'] . "'
					AND BOM.EffectiveTo > '" . Date('Y-m-d') . "'
					AND BOM.EffectiveAfter < '" . Date('Y-m-d') . "'";

			$ErrMsg = _('Could not recalculate the current cost of the assembly item') . $ItemsToUpdate['StockID'] . ' ' . _('because');
			$AssemblyCostResult = DB_query($SQL,$db,$ErrMsg);
			$AssemblyCost = DB_fetch_row($AssemblyCostResult);
			$Cost = $AssemblyCost[0];
		} else {
			$Cost = $ItemsToUpdate['StandardCost'];
		}

		$SQL = 'UPDATE SalesAnalysis SET Cost = (Qty * ' . $Cost . ")
				WHERE StockID='" . $ItemsToUpdate['StockID'] . "'
				AND PeriodNo =" . $_POST['PeriodNo'];

		$ErrMsg = _('Could not update the sales analysis records for') . ' ' . $ItemsToUpdate['StockID'] . ' ' . _('beacuse');
		$UpdResult = DB_query($SQL,$db,$ErrMsg);


		prnMsg(_('Updated sales analysis for period') . ' ' . $_POST['PeriodNo'] . ' ' . _('and stock item') . ' ' . $ItemsToUpdate['StockID'] . ' ' . _('using a cost of') . ' ' . $Cost,'success');
	}


	prnMsg(_('Updated the sales analysis cost data for period') . ' '. $_POST['PeriodNo'],'success');
}
include('includes/footer.inc');
?>

