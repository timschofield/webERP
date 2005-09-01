<?php
/* $Revision: 1.7 $ */
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
	echo '<html><body><BR><BR><BR><BR><BR><BR><BR><CENTER><FONT COLOR=RED SIZE=4><B>' . _('The security settings on your account do not permit you to access this function') . '</B></FONT></body></html>';
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
			echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
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
		echo '<P>' . _('The report did not have any none zero lines of information to show and so it has not been created');
		echo "<BR><A HREF='$rootpath/SalesAnalRepts.php?" . SID . "SelectedReport=" . $_GET['ReportID'] . "'>" . _('Look at the design of this report') . '</A>';
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
		include('includes/footer.inc');
		exit;
	}
} /* end if we wanted a PDF file */



if ($_GET['ProduceCVSFile']==True){

	include('includes/CSVSalesAnalysis.inc');

	$title = _('Sales Analysis Comma Seperated File (CSV) Generation');
	include('includes/header.inc');

	 echo 'http://' . getenv(SERVER_NAME) . $rootpath . '/' . $_SESSION['reports_dir'] .  '/SalesAnalysis.csv';
	 echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/" . $_SESSION['reports_dir'] .  "/SalesAnalysis.csv'>";

	 echo '<P>' . _('You should automatically be forwarded to the CSV Sales Analysis file when it is ready') . '. ' . _('If this does not happen') . " <a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/SalesAnalysis.csv'>" . _('click here') . '</a> ' . _('to continue') . '<br>';
	 include('includes/footer.inc');
}

?>