<?php
/* $Revision: 1.5 $ */

$PageSecurity = 2;

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){

	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/PDFStarter_ros.inc');
	include('includes/DateFunctions.inc');

	$FontSize=12;
	$pdf->addinfo('Title',_('Aged Supplier Listing'));
	$pdf->addinfo('Subject',_('Aged Suppliers'));

	$PageNumber=0;
	$line_height=12;

      /*Now figure out the aged analysis for the Supplier range under review */

	if ($_POST['All_Or_Overdues']=='All'){
		$SQL = "SELECT Suppliers.SupplierID, Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms,
	Sum(SuppTrans.OvAmount + SuppTrans.OvGST  - SuppTrans.Alloc) AS Balance,
	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate)) >= PaymentTerms.DaysBeforeDue  THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= 0 THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
	)) AS Due,
	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
	)) AS Overdue1,
	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
	)) AS Overdue2
	FROM Suppliers, PaymentTerms, Currencies, SuppTrans WHERE Suppliers.PaymentTerms = PaymentTerms.TermsIndicator AND Suppliers.CurrCode = Currencies.CurrAbrev AND Suppliers.SupplierID = SuppTrans.SupplierNo
	AND Suppliers.SupplierID >= '" . $_POST['FromCriteria'] . "' AND Suppliers.SupplierID <= '" . $_POST['ToCriteria'] . "' AND
	Suppliers.CurrCode ='" . $_POST['Currency'] . "'
	GROUP BY Suppliers.SupplierID, Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms, PaymentTerms.DaysBeforeDue, PaymentTerms.DayInFollowingMonth
	HAVING Sum(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) <>0";

	} else {

	      $SQL = "SELECT Suppliers.SupplierID, Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms,
	Sum(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS Balance,
	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate)) >= PaymentTerms.DaysBeforeDue  THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= 0 THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
	)) AS Due,
	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
	)) AS Overdue1,
	Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
	)) AS Overdue2
	FROM Suppliers, PaymentTerms, Currencies, SuppTrans WHERE Suppliers.PaymentTerms = PaymentTerms.TermsIndicator AND Suppliers.CurrCode = Currencies.CurrAbrev AND Suppliers.SupplierID = SuppTrans.SupplierNo
	AND Suppliers.SupplierID >= '" . $_POST['FromCriteria'] . "' AND Suppliers.SupplierID <= '" . $_POST['ToCriteria'] . "' AND
	Suppliers.CurrCode ='" . $_POST['Currency'] . "'

	GROUP BY Suppliers.SupplierID, Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms, PaymentTerms.DaysBeforeDue, PaymentTerms.DayInFollowingMonth

	HAVING Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END)) > 0";

	}

	$SupplierResult = DB_query($SQL,$db,'','',False,False); /*dont trap errors */

	if (DB_error_no($db) !=0) {
	  $title = _('Aged Supplier Account Analysis') . ' - ' . _('Problem Report') ;
	  include("includes/header.inc");
	  prnMsg(_('The Supplier details could not be retrieved by the SQL because') .  ' ' . DB_error_msg($db),'error');
	   echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	   if ($debug==1){
		echo "<BR>$SQL";
	   }
	   include('includes/footer.inc');
	   exit;
	}

	include ('includes/PDFAgedSuppliersPageHeader.inc');
	$TotBal = 0;
	$TotDue = 0;
	$TotCurr = 0;
	$TotOD1 = 0;
	$TotOD2 = 0;

	While ($AgedAnalysis = DB_fetch_array($SupplierResult,$db)){

		$DisplayDue = number_format($AgedAnalysis['Due']-$AgedAnalysis['Overdue1'],2);
		$DisplayCurrent = number_format($AgedAnalysis['Balance']-$AgedAnalysis['Due'],2);
		$DisplayBalance = number_format($AgedAnalysis['Balance'],2);
		$DisplayOverdue1 = number_format($AgedAnalysis['Overdue1']-$AgedAnalysis['Overdue2'],2);
		$DisplayOverdue2 = number_format($AgedAnalysis['Overdue2'],2);

		$TotBal += $AgedAnalysis['Balance'];
		$TotDue += ($AgedAnalysis['Due']-$AgedAnalysis['Overdue1']);
		$TotCurr += ($AgedAnalysis['Balance']-$AgedAnalysis['Due']);
		$TotOD1 += ($AgedAnalysis['Overdue1']-$AgedAnalysis['Overdue2']);
		$TotOD2 += $AgedAnalysis['Overdue2'];

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$AgedAnalysis['SupplierID'] . ' - ' . $AgedAnalysis['SuppName'],'left');
		$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
		$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
		$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
		$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

		$YPos -=$line_height;
		if ($YPos < $Bottom_Margin + $line_height){
		      include('includes/PDFAgedSuppliersPageHeader.inc');
		}

		if ($_POST['DetailedReport']=='Yes'){

		   $FontSize=6;
		   /*draw a line under the Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);

		   $sql = "SELECT SysTypes.TypeName, SuppTrans.SuppReference, SuppTrans.TranDate,
			   (SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS Balance,
			   IF (PaymentTerms.DaysBeforeDue > 0,
			   CASE WHEN (TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate)) >= PaymentTerms.DaysBeforeDue  THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
			   CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= 0 THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
			   ) AS Due,
			   IF (PaymentTerms.DaysBeforeDue > 0,
			   CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue	   AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
			   CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1	MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
			   ) AS Overdue1,
			   IF (PaymentTerms.DaysBeforeDue > 0,
			   CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue	   AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " . $PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,
			   CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate, INTERVAL 1	MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth - DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END
			   ) AS Overdue2
			   FROM Suppliers, PaymentTerms, SuppTrans, SysTypes
			   WHERE SysTypes.TypeID = SuppTrans.Type AND Suppliers.PaymentTerms = PaymentTerms.TermsIndicator AND Suppliers.SupplierID = SuppTrans.SupplierNo AND ABS(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) >0.009 AND SuppTrans.Settled = 0 AND SuppTrans.SupplierNo = '" . $AgedAnalysis["SupplierID"] . "'";

		    $DetailResult = DB_query($sql,$db,'','',False,False); /*dont trap errors - trapped below*/
		    if (DB_error_no($db) !=0) {
			$title = _('Aged Supplier Account Analysis - Problem Report');
			include('includes/header.inc');
			echo '<BR>' . _('The details of outstanding transactions for Supplier') . ' - ' . $AgedAnalysis['SupplierID'] . ' ' . _('could not be retrieved because') . ' - ' . DB_error_msg($db);
			echo "<BR><A HREF='$rootpath/index.php'>" . _('Back to the menu') . '</A>';
			if ($debug==1){
			   echo '<BR>' . _('The SQL that failed was') . '<BR>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		    }

		    while ($DetailTrans = DB_fetch_array($DetailResult)){

			    $LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos,60,$FontSize,$DetailTrans['TypeName'],'left');
			    $LeftOvers = $pdf->addTextWrap($Left_Margin+65,$YPos,50,$FontSize,$DetailTrans['SuppReference'],'left');
			    $DisplayTranDate = ConvertSQLDate($DetailTrans['TranDate']);
			    $LeftOvers = $pdf->addTextWrap($Left_Margin+105,$YPos,70,$FontSize,$DisplayTranDate,'left');

			    $DisplayDue = number_format($DetailTrans['Due']-$DetailTrans['Overdue1'],2);
			    $DisplayCurrent = number_format($DetailTrans['Balance']-$DetailTrans['Due'],2);
			    $DisplayBalance = number_format($DetailTrans['Balance'],2);
			    $DisplayOverdue1 = number_format($DetailTrans['Overdue1']-$DetailTrans['Overdue2'],2);
			    $DisplayOverdue2 = number_format($DetailTrans['Overdue2'],2);

			    $LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			    $LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayCurrent,'right');
			    $LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayDue,'right');
			    $LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayOverdue1,'right');
			    $LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayOverdue2,'right');

			    $YPos -=$line_height;
			    if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				include('includes/PDFAgedSuppliersPageHeader.inc');
				$FontSize=6;
			    }
		    } /*end while there are detail transactions to show */
		    /*draw a line under the detailed transactions before the next Supplier aged analysis*/
		   $pdf->line($Page_Width-$Right_Margin, $YPos+10,$Left_Margin, $YPos+10);
		   $FontSize=8;
		} /*Its a detailed report */
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFAgedSuppliersPageHeader.inc');
	} elseif ($_POST['DetailedReport']=='Yes') {
		//dont do a line if the totals have to go on a new page
		$pdf->line($Page_Width-$Right_Margin, $YPos+10 ,220, $YPos+10);
	}

	$DisplayTotBalance = number_format($TotBal,2);
	$DisplayTotDue = number_format($TotDue,2);
	$DisplayTotCurrent = number_format($TotCurr,2);
	$DisplayTotOverdue1 = number_format($TotOD1,2);
	$DisplayTotOverdue2 = number_format($TotOD2,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');
	$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayTotCurrent,'right');
	$LeftOvers = $pdf->addTextWrap(340,$YPos,60,$FontSize,$DisplayTotDue,'right');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,$DisplayTotOverdue1,'right');
	$LeftOvers = $pdf->addTextWrap(460,$YPos,60,$FontSize,$DisplayTotOverdue2,'right');

	$YPos -=$line_height;
	$pdf->line($Page_Width-$Right_Margin, $YPos ,220, $YPos);

	$buf = $pdf->output();
	$len = strlen($buf);
	header('Content-type: application/pdf');
	header("Content-Length: $len");
	header('Content-Disposition: inline; filename=AgedSuppliers.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title = _('Aged Supplier Analysis');
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	$CompanyRecord = ReadInCompanyRecord($db);

	if (strlen($_POST['FromCriteria'])<1 OR strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD='POST'><CENTER><TABLE>";

		echo '<TR><TD>' . _('From Supplier Code') . ":</FONT></TD>
			<TD><input Type=text maxlength=6 size=7 name=FromCriteria value='1'></TD>
		</TR>";
		echo '<TR><TD>' . _('To Supplier Code') . ":</TD>
			<TD><input Type=text maxlength=6 size=7 name=ToCriteria value='zzzzzz'></TD>
		</TR>";

		echo '<TR><TD>' . _('All balances or overdues only') . ':' . "</TD>
			<TD><SELECT name='All_Or_Overdues'>";
		echo "<OPTION SELECTED Value='All'>" . _('All suppliers with balances');
		echo "<OPTION Value='OverduesOnly'>" . _('Overdue accounts only');
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('For suppliers trading in') . ':' . "</TD>
			<TD><SELECT name='Currency'>";

		$sql = 'SELECT Currency, CurrAbrev FROM Currencies';
		$result=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($result)){
		      if ($myrow['CurrAbrev'] == $CompanyRecord['CurrencyDefault']){
				echo "<OPTION SELECTED Value='" . $myrow["CurrAbrev"] . "'>" . $myrow['Currency'];
		      } else {
			      echo "<OPTION Value='" . $myrow['CurrAbrev'] . "'>" . $myrow['Currency'];
		      }
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('Summary or Detailed Report') . ':' . "</TD>
			<TD><SELECT name='DetailedReport'>";
		echo "<OPTION SELECTED Value='No'>" . _('Summary Report');
		echo "<OPTION Value='Yes'>" . _('Detailed Report');
		echo '</SELECT></TD></TR>';

		echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";
	}
	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>
