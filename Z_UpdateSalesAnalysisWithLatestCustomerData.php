<?php

/* $Id: Z_UpdateSalesAnalysisWithLatestCustomerData.php 5784 2012-12-29 04:00:43Z daintree $*/

include('includes/session.php');
$Title=_('Apply Current Customer and Branch Data to Sales Analysis');
include('includes/header.php');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />
		<input type="submit" name="UpdateSalesAnalysis" value="' . _('Update Sales Analysis Customer Data') .'" />
	</div>
	</form>';

if (isset($_POST['UpdateSalesAnalysis'])){

	/* Loop around each customer/branch combo */

	$sql = "SELECT debtorsmaster.debtorno,
					branchcode,
					salestype,
					area,
					salesman
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno";

	$ErrMsg = _('Could not retrieve the customer records to be updated because');
	$result = DB_query($sql,$ErrMsg);

	while ($CustomerRow = DB_fetch_array($result)){

		$SQL = "UPDATE salesanalysis SET area = '" . $CustomerRow['area'] . "',
										typeabbrev= '" . $CustomerRow['salestype'] . "',
										salesperson= '" . $CustomerRow['salesman'] . "'
				WHERE cust='" . $CustomerRow['debtorno'] . "'
				AND custbranch ='" . $CustomerRow['branchcode'] . "'";

		$ErrMsg = _('Could not update the sales analysis records for') . ' ' . $CustomerRow['debtorno'] . ' ' . _('because');
		$UpdResult = DB_query($SQL,$ErrMsg);

		prnMsg(_('Updated sales analysis for customer code') . ': ' . $CustomerRow['debtorno'] . ' ' . _('and branch code') . ': ' . $CustomerRow['branchcode'],'success');
	}


	prnMsg(_('Updated the sales analysis with all the latest sales areas, salesman and sales types as set up now'),'success');
}
include('includes/footer.php');
?>
