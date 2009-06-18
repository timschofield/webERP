<?php
$PageSecurity = 2;
include('includes/session.inc');
$result = DB_query("SELECT description FROM stockmaster WHERE stockid='" . trim(strtoupper($_GET['StockID'])) . "'",$db);
$myrow = DB_fetch_row($result);

include('includes/phplot/phplot.php');
$graph =& new phplot(1000,500);
$graph->SetTitle($myrow[0] . ' ' . _('Usage'));
$graph->SetXTitle(_('Month'));
$graph->SetYTitle(_('Quantity'));
$graph->SetBackgroundColor("wheat");
$graph->SetTitleColor("blue");
$graph->SetPlotType="bars";
$graph->SetShading(5);
$graph->SetDrawYGrid(TRUE);
$graph->SetMarginsPixels(40,40,40,40);
$graph->SetDataType('text-data');

if($_GET['StockLocation']=='All'){
	$sql = "SELECT periods.periodno, 
			periods.lastdate_in_period, 
			SUM(-stockmoves.qty) AS qtyused 
		FROM stockmoves INNER JOIN periods 
			ON stockmoves.prd=periods.periodno 
		WHERE (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=28) 
		AND stockmoves.hidemovt=0 
		AND stockmoves.stockid = '" . trim(strtoupper($_GET['StockID'])) . "' 
		GROUP BY periods.periodno, 
			periods.lastdate_in_period 
		ORDER BY periodno  LIMIT 24";
} else {
	$sql = "SELECT periods.periodno, 
			periods.lastdate_in_period, 
			SUM(-stockmoves.qty) AS qtyused 
		FROM stockmoves INNER JOIN periods 
			ON stockmoves.prd=periods.periodno 
		WHERE (stockmoves.type=10 Or stockmoves.type=11 OR stockmoves.type=28) 
		AND stockmoves.hidemovt=0 
		AND stockmoves.loccode='" . $_GET['StockLocation'] . "' 
		AND stockmoves.stockid = '" . trim(strtoupper($_GET['StockID'])) . "' 
		GROUP BY periods.periodno, 
			periods.lastdate_in_period 
		ORDER BY periodno  LIMIT 24";
}
$MovtsResult = DB_query($sql, $db);
if (DB_error_no($db) !=0) {
	$title = _('Stock Usage Graph Problem');
	include ('includes/header.inc');
	echo _('The stock usage for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
	if ($debug==1){
	echo '<br>' . _('The SQL that failed was') . $sql;
	}
	include('includes/footer.inc');
	exit;
}
if (DB_num_rows($MovtsResult)==0){
	$title = _('Stock Usage Graph Problem');
	include ('includes/header.inc');
	prnMsg(_('There are no movements of this item from the selected location to graph'),'info');
	include('includes/footer.inc');
	exit;
}

$UsageArray = array();
$NumberOfPeriodsUsage = DB_num_rows($MovtsResult);
if ($NumberOfPeriodsUsage!=24){
	$graph->SetDataColors(
		array("blue"),  //Data Colors
		array("black")	//Border Colors
		);  
	for ($i=1;$i++;$i<=$NumberOfPeriodsUsage){
		$UsageRow = DB_fetch_array($MovtsResult);
		if (!$UsageRow){
			break;
		} else {
			$UsageArray[] = array(MonthAndYearFromSQLDate($UsageRow['lastdate_in_period']),$UsageRow['qtyused']);
		}
	}
}else {
	$graph->SetDataColors(
		array("blue","red"),  //Data Colors
		array("black")	//Border Colors
	);  
	for ($i=1;$i++;$i<=12){
		$UsageRow = DB_fetch_array($MovtsResult);
		if (!$UsageRow){
			break;
		}
		$UsageArray[] = array(MonthAndYearFromSQLDate($UsageRow['lastdate_in_period']),$UsageRow['qtyused']);
	}
	for ($i=0,$i++;$i<=11;){
		$UsageRow = DB_fetch_array($MovtsResult);
		if (!$UsageRow){
			break;
		}
		$UsageArray[$i][0] = MonthAndYearFromSQLDate($UsageRow['lastdate_in_period']);
		$UsageArray[$i][2] = $UsageRow['qtyused'];
	}
}
//$graph->SetDrawXGrid(TRUE);
$graph->SetDataValues($UsageArray);
$graph->SetDataColors(
	array("blue","red"),  //Data Colors
	array("black")	//Border Colors
);  


//Draw it
$graph->DrawGraph();
?>