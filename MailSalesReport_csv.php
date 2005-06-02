<?php
/* $Revision: 1.6 $ */
/*Now this is not secure so a malicious user could send multiple emails of the report to the intended receipients

The intention is that this script is called from cron at intervals defined with a command like:

/usr/bin/wget http://localhost/web-erp/MailSalesReport.php

The configuration of this script requires the id of the sales analysis report to send
and an array of the receipients */

/*The Sales report to send */
$ReportID = 4;
$AllowAnyone = true;

/*The people to receive the emailed report */
$Recipients = array('"Root" <root@localhost>','"someone else" <someoneelese@sowhere.com>');

/* ----------------------------------------------------------------------------------------------*/


include('includes/session.inc');
include('includes/ConstructSQLForUserDefinedSalesReport.inc');
include('includes/CSVSalesAnalysis.inc');


include('includes/htmlMimeMail.php');

$mail = new htmlMimeMail();
$attachment = $mail->getFile( $_SESSION['reports_dir'] . '/SalesAnalysis.csv');
$mail->setText(_('Please find herewith the comma seperated values sales report'));
$mail->addAttachment($attachment, 'SalesAnalysis.csv', 'application/csv');
$mail->setSubject(_('Sales Analysis') . ' - ' . _('CSV Format'));
$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
$result = $mail->send($Recipients);
?>
