<?php
include("includes/DateFunctions.inc");
$PageSecurity = 2;

/* $Revision: 1.1 $ */

if (!isset($_POST['FromCust'])  OR $_POST['FromCust']=="" OR !isset($_GET['FromCust'])) {
	$title="Debtor Balances";
}


If (isset($_POST['PrintPDF']) AND isset($_POST['FromCriteria']) AND strlen($_POST['FromCriteria'])>=1 AND isset($_POST['ToCriteria']) AND
strlen($_POST['ToCriteria'])>=1){

	include("config.php");
	include("includes/ConnectDB.inc");

	include("includes/PDFStarter_ros.inc");

	$FontSize=12;
	$pdf->addinfo('Title',"Customer Balance Listing");
	$pdf->addinfo('Subject','Customer Balances');

	$PageNumber=0;
	$line_height=12;

	/*Get the date of the last day in the period selected */

	$SQL = "SELECT LastDate_In_Period FROM Periods WHERE PeriodNo = " . $_POST['PeriodEnd'];
	$PeriodEndResult = DB_query($SQL,$db,"Could not get the date of the last day in the period selected");
	$PeriodRow = DB_fetch_row($PeriodEndResult);
	$PeriodEndDate = ConvertSQLDate($PeriodRow[0]);

      /*Now figure out the aged analysis for the customer range under review */

	$SQL = "SELECT DebtorsMaster.DebtorNo,
			DebtorsMaster.Name,
  			Currencies.Currency,
			Sum((DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc)/DebtorTrans.Rate) AS Balance,
			Sum(DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc) AS FXBalance,
			Sum(CASE WHEN DebtorTrans.Prd > " . $_POST['PeriodEnd'] . " THEN
	(DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount)/DebtorTrans.Rate ELSE 0 END)
	 AS AfterDateTrans,
			Sum(CASE WHEN DebtorTrans.Prd > " . $_POST['PeriodEnd'] . " THEN
	DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount ELSE 0 END
	) AS FXAfterDateTrans
	FROM DebtorsMaster,
		Currencies,
		DebtorTrans
	WHERE DebtorsMaster.CurrCode = Currencies.CurrAbrev
		AND DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo
		AND DebtorsMaster.DebtorNo >= '" . $_POST['FromCriteria'] . "'
		AND DebtorsMaster.DebtorNo <= '" . $_POST['ToCriteria'] . "'
	GROUP BY DebtorsMaster.DebtorNo, DebtorsMaster.Name, Currencies.Currency";

	$CustomerResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		$title = "Customer Balances - Problem Report.... ";
		include("includes/header.inc");
		echo "The customer details could not be retrieved by the SQL because - " . DB_error_msg($db);
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include("includes/footer.inc");
		exit;
	}

	include ("includes/PDFDebtorBalsPageHeader.inc");

	$TotBal=0;

	While ($DebtorBalances = DB_fetch_array($CustomerResult,$db)){

		$Balance = $DebtorBalances["Balance"] - $DebtorBalances['AfterDateTrans'];
		$FXBalance = $DebtorBalances["FXBalance"] - $DebtorBalances['FXAfterDateTrans'];

		if (ABS($Balance)>0.009 OR ABS($FXBalance)>0.009) {

			$DisplayBalance = number_format($DebtorBalances["Balance"] - $DebtorBalances['AfterDateTrans'],2);
			$DisplayFXBalance = number_format($DebtorBalances["FXBalance"] - $DebtorBalances['FXAfterDateTrans'],2);

			$TotBal += $Balance;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$DebtorBalances["DebtorNo"] . " - " . $DebtorBalances["Name"],'left');
			$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayFXBalance,'right');
			$LeftOvers = $pdf->addTextWrap(350,$YPos,100,$FontSize,$DebtorBalances['Currency'],'left');


			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
			include("includes/PDFDebtorBalsPageHeader.inc");
			}
		}
	} /*end customer aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include("includes/PDFDebtorBalsPageHeader.inc");
	}

	$DisplayTotBalance = number_format($TotBal,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');

	$buf = $pdf->output();
	$len = strlen($buf);

	header("Content-type: application/pdf");
	header("Content-Length: $len");
	header("Content-Disposition: inline; filename=DebtorBals.pdf");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: public");

	$pdf->stream();

} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	include("includes/header.inc");
	include("includes/SQL_CommonFunctions.inc");

	$CompanyRecord = ReadInCompanyRecord($db);


	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

		echo "<TR><TD>From Customer Code:</FONT></TD><TD><input Type=text maxlength=6 size=7 name=FromCriteria value='1'></TD></TR>";
		echo "<TR><TD>To Customer Code:</TD><TD><input Type=text maxlength=6 size=7 name=ToCriteria value='zzzzzz'></TD></TR>";

		echo "<TR><TD>Balances As At:</TD><TD><SELECT Name='PeriodEnd'>";

		$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";
		$Periods = DB_query($sql,$db,"Could not retrieve period data because","The SQL that failed to get the period data was:");

		while ($myrow = DB_fetch_array($Periods,$db)){

			echo "<OPTION VALUE=" . $myrow["PeriodNo"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);

		}
	}

	echo "</SELECT></TD></TR>";


	echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='Print PDF'></CENTER>";

	include("includes/footer.inc");
} /*end of else not PrintPDF */

?>
