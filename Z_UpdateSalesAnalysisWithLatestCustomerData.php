<?php

include('includes/session.php');
$Title=__('Apply Current Customer and Branch Data to Sales Analysis');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />
		<input type="submit" name="UpdateSalesAnalysis" value="' . __('Update Sales Analysis Customer Data') .'" />
	</div>
	</form>';

if (isset($_POST['UpdateSalesAnalysis'])){

	/* Loop around each customer/branch combo */

	$SQL = "SELECT debtorsmaster.debtorno,
					branchcode,
					salestype,
					area,
					salesman
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno";

	$ErrMsg = __('Could not retrieve the customer records to be updated because');
	$Result = DB_query($SQL, $ErrMsg);

	while ($CustomerRow = DB_fetch_array($Result)){

		$SQL = "UPDATE salesanalysis SET area = '" . $CustomerRow['area'] . "',
										typeabbrev= '" . $CustomerRow['salestype'] . "',
										salesperson= '" . $CustomerRow['salesman'] . "'
				WHERE cust='" . $CustomerRow['debtorno'] . "'
				AND custbranch ='" . $CustomerRow['branchcode'] . "'";

		$ErrMsg = __('Could not update the sales analysis records for') . ' ' . $CustomerRow['debtorno'] . ' ' . __('because');
		$UpdResult = DB_query($SQL, $ErrMsg);

		prnMsg(__('Updated sales analysis for customer code') . ': ' . $CustomerRow['debtorno'] . ' ' . __('and branch code') . ': ' . $CustomerRow['branchcode'],'success');
	}


	prnMsg(__('Updated the sales analysis with all the latest sales areas, salesman and sales types as set up now'),'success');
}
include('includes/footer.php');
