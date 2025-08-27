<?php

/* Now this is not secure so a malicious user could send multiple emails of the report to the intended recipients

The intention is that this script is called from cron at intervals defined with a command like:

/usr/bin/wget http://localhost/web-erp/MailSalesReport.php

The configuration of this script requires the id of the sales analysis report to send
and an array of the recipients */

/*The following three variables need to be modified for the report - the company database to use and the recipients */

$AllowAnyone = true;

require(__DIR__ . '/includes/session.php');

/*The Sales report to send */
$_GET['ReportID'] = 2;

/*The company database to use */
$DatabaseName = $_SESSION['DatabaseName'];

/*The people to receive the emailed report */
$Recipients = GetMailList('SalesAnalysisReportRecipients');
if (sizeOf($Recipients) == 0) {
	$Title = __('Inventory Valuation') . ' - ' . __('Problem Report');
	include('includes/header.php');
	prnMsg(__('There are no members of the Sales Analysis Report Recipients email group'), 'warn');
	include('includes/footer.php');
	exit();
}

include('includes/ConstructSQLForUserDefinedSalesReport.php');
include('includes/PDFSalesAnalysis.php');

$From = $_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>';
$Subject = __('Sales Analysis Report');

if ($Counter > 0) { /* the number of lines of the sales report is more than 0  ie there is a report to send! */
	$pdf->Output($_SESSION['reports_dir'] . '/SalesAnalysis_' . date('Y-m-d') . '.pdf', 'F'); //save to file
	$pdf->__destruct();

	$Body = __('Please find herewith sales report');
	$AttachmentPath = $_SESSION['reports_dir'] . '/SalesAnalysis_' . date('Y-m-d') . '.pdf';

	$Result = SendEmailFromWebERP($From, $Recipients, $Subject, $Body, $AttachmentPath, true);
} else {
	$Body = __('Error running automated sales report number') . ' ' . $ReportID;
	$Result = SendEmailFromWebERP($From, $Recipients, $Subject, $Body);
}
