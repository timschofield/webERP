<?php
/* $Revision: 1.2 $ */
$PageSecurity=15;

$title="Recalculate Sales Analysis With Current Cost Data";

include("includes/session.inc");
include("includes/header.inc");

$Period = 42;

echo "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

$SQL = "SELECT MonthName(LastDate_In_Period) AS Mnth,YEAR(LastDate_In_Period) AS Yr, PeriodNo FROM Periods";
echo "<P><CENTER>Select the Period to update the costs for:<SELECT NAME='PeriodNo'>";
$result = DB_query($SQL,$db);

echo "<OPTION SELECTED VALUE=0>No Period Selected";

while ($PeriodInfo=DB_fetch_array($result)){

	echo "<OPTION VALUE=" . $PeriodInfo['PeriodNo'] . ">" . $PeriodInfo['Mnth'] . " " . $PeriodInfo['Yr'];

}

echo "</SELECT>";

echo "<P><INPUT TYPE=SUBMIT NAME='UpdateSalesAnalysis' VALUE='Update Sales Analysis Costs'></CENTER>";
echo "</FORM";

if (isset($_POST['UpdateSalesAnalysis']) AND $_POST['PeriodNo']!=0){
	$sql = "SELECT StockMaster.StockID, MaterialCost+OverheadCost+LabourCost AS StandardCost, StockMaster.MBflag From SalesAnalysis INNER JOIN StockMaster ON SalesAnalysis.StockID=StockMaster.StockID WHERE PeriodNo=" . $_POST['PeriodNo']  . " AND MBflag<>'D' GROUP BY StockID";


	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>Error ". DB_error_msg($db) . " occurred.";
		exit;
	}

	while ($ItemsToUpdate = DB_fetch_array($result)){

		if ($ItemsToUpdate['MBflag']=='A'){
			$SQL = "SELECT Sum(MaterialCost + LabourCost + OverheadCost) AS StandardCost FROM StockMaster INNER JOIN BOM  on StockMaster.StockID = BOM.Component WHERE BOM.Parent = '" . $ItemsToUpdate['StockID'] . "' AND BOM.EffectiveTo > '" . Date("Y-m-d") . "' AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";
			$AssemblyCostResult = DB_query($SQL,$db);

			if (DB_error_no($db)!=0){
				echo "<BR>Error ". DB_error_msg($db) . " occurred ";
				echo "<BR>The SQL statement was : " . $SQL;
				exit;
			}
			$AssemblyCost = DB_fetch_row($AssemblyCostResult);
			$Cost = $AssemblyCost[0];
		} else {
			$Cost = $ItemsToUpdate['StandardCost'];
		}

		$SQL = "UPDATE SalesAnalysis SET Cost = (Qty * " . $Cost . ") WHERE StockID='" . $ItemsToUpdate['StockID'] . "' AND PeriodNo =" . $_POST['PeriodNo'];
		$UpdResult = DB_query($SQL,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>Error ". DB_error_msg($db) . " occurred ";
			echo "<BR>The SQL statement was : " . $SQL;
			exit;
		}

		echo "<BR>Updated sales analysis for period " . $_POST['PeriodNo'] . " and stock item " . $ItemsToUpdate['StockID'] . " using a cost of " . $Cost;
	}


	echo "<P>Updated sales analysis for period ". $_POST['PeriodNo'];
}
include("includes/footer.inc");
?>

