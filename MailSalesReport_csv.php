<?php

/* $Id: MailSalesReport_csv.php 5963 2013-05-12 14:42:40Z exsonqu $*/
/*Now this is not secure so a malicious user could send multiple emails of the report to the intended receipients

The intention is that this script is called from cron at intervals defined with a command like:

/usr/bin/wget http://localhost/web-erp/MailSalesReport.php

The configuration of this script requires the id of the sales analysis report to send
and an array of the receipients and the company database to use*/

/*The Sales report to send */
$ReportID = 4;





/* ----------------------------------------------------------------------------------------------*/

$AllowAnyone = true;
include('includes/session.inc');
/*The company database to use */
$DatabaseName = $_SESSION['DatabaseName'];
/*The people to receive the emailed report, This mail list now can be maintained in Mailing List Maintenance of Set Up */

$Recipients = GetMailList('SalesAnalysisReportRecipients');
if (sizeOf($Recipients) == 0) {
	$Title = _('Inventory Valuation') . ' - ' . _('Problem Report');
      	include('includes/header.inc');
	prnMsg( _('There are no members of the Sales Analysis Report Recipients email group'), 'warn');
	include('includes/footer.inc');
	exit;
}
include('includes/ConstructSQLForUserDefinedSalesReport.inc');
include('includes/CSVSalesAnalysis.inc');


include('includes/htmlMimeMail.php');

$mail = new htmlMimeMail();
$attachment = $mail->getFile( $_SESSION['reports_dir'] . '/SalesAnalysis.csv');
$mail->setText(_('Please find herewith the comma separated values sales report'));
$mail->addAttachment($attachment, 'SalesAnalysis.csv', 'application/csv');
$mail->setSubject(_('Sales Analysis') . ' - ' . _('CSV Format'));
if($_SESSION['SmtpSetting']==0){
	$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
	$result = $mail->send($Recipients);
}else{
	$result = SendmailBySmtp($mail,$Recipients);
}
?>
