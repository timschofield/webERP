<?php
/* $Revision: 1.9 $ */
$PageSecurity = 2;
include('includes/session.inc');
/*
include('config.php');
include('includes/ConnectDB.inc');
*/


 /*
 if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

session_start();
*/
if (!in_array($PageSecurity,$_SESSION['AllowedPageSecurityTokens'])){
	echo '<html><body><br><br><br><br><br><br><br><div class="centre"><font color=RED size=4><b>' . _('The security settings on your account do not permit you to access this function') . '</b></font></div></body></html>';
	exit;
}

include('includes/ConstructSQLForUserDefinedSalesReport.inc');

if (isset($_GET['ProducePDF'])){

	include ('includes/PDFSalesAnalysis.inc');

	if ($Counter >0){
 		$pdfcode = $pdf->output();
		$len = strlen($pdfcode);

        	if ($len<=20){
			$title = _('Printing Sales Analysis Error');
			include('includes/header.inc');
			echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
			include('includes/footer.inc');
			exit;
        	} else {
			header('Content-type: application/pdf');
			header('Content-Length: ' . $len);
			header('Content-Disposition: inline; filename=SalesAnalysis.pdf');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			$pdf->Stream();
		}
	} else {
		$title = _('User Defined Sales Analysis Problem');
		include('includes/header.inc');
		echo '<p>' . _('The report did not have any none zero lines of information to show and so it has not been created');
		echo "<br><a href='$rootpath/SalesAnalRepts.php?" . SID . "SelectedReport=" . $_GET['ReportID'] . "'>" . _('Look at the design of this report') . '</a>';
		echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}
} /* end if we wanted a PDF file */



if ($_GET['ProduceCVSFile']==True){

	include('includes/CSVSalesAnalysis.inc');

	$title = _('Sales Analysis Comma Separated File (CSV) Generation');
	include('includes/header.inc');

	 echo 'http://' . getenv(SERVER_NAME) . $rootpath . '/' . $_SESSION['reports_dir'] .  '/SalesAnalysis.csv';
	 echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/" . $_SESSION['reports_dir'] .  "/SalesAnalysis.csv'>";

	 echo '<p>' . _('You should automatically be forwarded to the CSV Sales Analysis file when it is ready') . '. ' . _('If this does not happen') . " <a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/SalesAnalysis.csv'>" . _('click here') . '</a> ' . _('to continue') . '<br>';
	 include('includes/footer.inc');
}

?>