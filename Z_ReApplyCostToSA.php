<?php

include('includes/session.php');
$Title=__('Apply Current Cost to Sales Analysis');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

$Period = 42;

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT MonthName(lastdate_in_period) AS mnth,
		YEAR(lastdate_in_period) AS yr,
		periodno
		FROM periods";
echo '<br /><div class="centre">' . __('Select the Period to update the costs for') . ':<select name="PeriodNo">';
$Result = DB_query($SQL);

echo '<option selected="selected" value="0">' . __('No Period Selected') . '</option>';

while ($PeriodInfo=DB_fetch_array($Result)){

	echo '<option value="' . $PeriodInfo['periodno'] . '">' . $PeriodInfo['mnth'] . ' ' . $PeriodInfo['Yr'] . '</option>';

}

echo '</select>';

echo '<br /><input type="submit" name="UpdateSalesAnalysis" value="' . __('Update Sales Analysis Costs') .'" /></div>';
echo '</div></form>';

if (isset($_POST['UpdateSalesAnalysis']) AND $_POST['PeriodNo']!=0){
	$SQL = "SELECT stockmaster.stockid,
			actualcost AS standardcost,
			stockmaster.mbflag
		FROM salesanalysis INNER JOIN stockmaster
			ON salesanalysis.stockid=stockmaster.stockid
		WHERE periodno='" . $_POST['PeriodNo']  . "'
		AND stockmaster.mbflag<>'D'
		GROUP BY stockmaster.stockid,
			stockmaster.actualcost,
			stockmaster.mbflag";


	$ErrMsg = __('Could not retrieve the sales analysis records to be updated because');
	$Result = DB_query($SQL, $ErrMsg);

	while ($ItemsToUpdate = DB_fetch_array($Result)){

		if ($ItemsToUpdate['mbflag']=='A'){
			$SQL = "SELECT SUM(actualcost) AS standardcost
					FROM stockmaster INNER JOIN BOM
						ON stockmaster.stockid = bom.component
					WHERE bom.parent = '" . $ItemsToUpdate['stockid'] . "'
					AND bom.effectiveto > CURRENT_DATE
					AND bom.effectiveafter < CURRENT_DATE";

			$ErrMsg = __('Could not recalculate the current cost of the assembly item') . $ItemsToUpdate['stockid'] . ' ' . __('because');
			$AssemblyCostResult = DB_query($SQL, $ErrMsg);
			$AssemblyCost = DB_fetch_row($AssemblyCostResult);
			$Cost = $AssemblyCost[0];
		} else {
			$Cost = $ItemsToUpdate['standardcost'];
		}

		$SQL = "UPDATE salesanalysis SET cost = (qty * " . $Cost . ")
				WHERE stockid='" . $ItemsToUpdate['stockid'] . "'
				AND periodno ='" . $_POST['PeriodNo'] . "'";

		$ErrMsg = __('Could not update the sales analysis records for') . ' ' . $ItemsToUpdate['stockid'] . ' ' . __('because');
		$UpdResult = DB_query($SQL, $ErrMsg);


		prnMsg(__('Updated sales analysis for period') . ' ' . $_POST['PeriodNo'] . ' ' . __('and stock item') . ' ' . $ItemsToUpdate['stockid'] . ' ' . __('using a cost of') . ' ' . $Cost,'success');
	}


	prnMsg(__('Updated the sales analysis cost data for period') . ' '. $_POST['PeriodNo'],'success');
}
include('includes/footer.php');
