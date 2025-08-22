<?php
// Z_RebuildSalesAnalysis.php
// Script to rebuild sales analysis records from stock movements
$PageSecurity = 15;
include('includes/session.php');
$Title = __('Rebuild sales analysis Records');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

echo '<br /><br />' . __('This script rebuilds sales analysis records. NB: all sales budget figures will be lost!');

$Result = DB_query("TRUNCATE TABLE salesanalysis");

$SQL = "INSERT INTO salesanalysis (typeabbrev,
									periodno,
									amt,
									cost,
									cust,
									custbranch,
									qty,
									disc,
									stockid,
									area,
									budgetoractual,
									salesperson,
									stkcategory)
		SELECT salestype,
		(SELECT periodno FROM periods WHERE MONTH(lastdate_in_period)=MONTH(trandate) AND YEAR(lastdate_in_period)=YEAR(trandate)) as prd,
				SUM(price*-qty) as salesvalue,
				SUM(standardcost*-qty) as cost,
				stockmoves.debtorno,
				stockmoves.branchcode,
				SUM(-qty),
				SUM(-qty*price*discountpercent) AS discountvalue,
				stockmoves.stockid,
				custbranch.area,
				1,
				custbranch.salesman,
				stockmaster.categoryid
		FROM stockmoves
		INNER JOIN debtorsmaster
		ON stockmoves.debtorno=debtorsmaster.debtorno
		INNER JOIN custbranch
		ON stockmoves.debtorno=custbranch.debtorno
		AND stockmoves.branchcode=custbranch.branchcode
		INNER JOIN stockmaster
		ON stockmoves.stockid=stockmaster.stockid
        WHERE show_on_inv_crds=1
		GROUP BY salestype,
				debtorno,
				prd,
				branchcode,
				stockid,
				area,
				salesman,
				categoryid
		ORDER BY prd";

$ErrMsg = __('The sales analysis data could not be recreated because');
$Result = DB_query($SQL, $ErrMsg);

echo '<p />';
prnMsg(__('The sales analsysis data has been recreated based on current stock master and customer master information'),'info');

include('includes/footer.php');
