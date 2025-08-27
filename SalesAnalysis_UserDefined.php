<?php

include('includes/session.php');

if (!in_array($PageSecurity,$_SESSION['AllowedPageSecurityTokens'])){
	echo '<html><head></head><body><br /><br /><br /><br /><br /><br /><br /><div class="centre"><font color="red" size="4"><b>' . __('The security settings on your account do not permit you to access this function') . '</b></font></div></body></html>';
	exit();
}

include('includes/ConstructSQLForUserDefinedSalesReport.php');

if (isset($_GET['ProducePDF'])) {

	include('includes/PDFSalesAnalysis.php');

	if ($Counter >0) {
		$PDF->OutputD('SalesAnalysis_' . date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	} else {
		$PDF->__destruct();
		$Title = __('User Defined Sales Analysis Problem');
		include('includes/header.php');
		echo '<p>' . __('The report did not have any none zero lines of information to show and so it has not been created');
		echo '<br /><a href="' . $RootPath . '/SalesAnalRepts.php?SelectedReport=' . $_GET['ReportID'] . '">' . __('Look at the design of this report') . '</a>';
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}
} /* end if we wanted a PDF file */

if ($_GET['ProduceCVSFile']==True) {

	include('includes/CSVSalesAnalysis.php');

	$Title = __('Sales Analysis Comma Separated File (CSV) Generation');
	include('includes/header.php');

	// gg: what was this line supposed to do ?
	//echo '//' . getenv('SERVER_NAME') . $RootPath . '/' . $_SESSION['reports_dir'] .  '/SalesAnalysis.csv';
	/// @todo this meta tag should be moved into the HTML HEAD, and thus be outputted within `header.php`
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/' . $_SESSION['reports_dir'] .  '/SalesAnalysis.csv">';

	echo '<p>' . __('You should automatically be forwarded to the CSV Sales Analysis file when it is ready') . '. ' . __('If this does not happen') . ' <a href="' . $RootPath . '/' . $_SESSION['reports_dir'] . '/SalesAnalysis.csv">' . __('click here') . '</a> ' . __('to continue') . '<br />';
	include('includes/footer.php');
}
