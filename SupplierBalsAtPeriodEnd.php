<?php
include("includes/DateFunctions.inc");
$PageSecurity = 2;

/* $Revision: 1.2 $ */

if (!isset($_POST['FromSupplier'])  OR $_POST['FromSupplier']=="" OR !isset($_GET['FromSupplier'])) {
	$title="Creditor Balances";
}


If (isset($_POST['PrintPDF']) AND isset($_POST['FromCriteria']) AND strlen($_POST['FromCriteria'])>=1 AND isset($_POST['ToCriteria']) AND
strlen($_POST['ToCriteria'])>=1){

	include("config.php");
	include("includes/ConnectDB.inc");

	include("includes/PDFStarter_ros.inc");

	$FontSize=12;
	$pdf->addinfo('Title',"Supplier Balance Listing");
	$pdf->addinfo('Subject','Supplier Balances');

	$PageNumber=0;
	$line_height=12;


      /*Now figure out the aged analysis for the Supplier range under review */

	$SQL = "SELECT Suppliers.SupplierID,
			Suppliers.SuppName,
  			Currencies.Currency,
			Sum((SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc)/SuppTrans.Rate) AS Balance,
			Sum(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS FXBalance,
			Sum(CASE WHEN SuppTrans.TranDate > '" . $_POST['PeriodEnd'] . "' THEN
	(SuppTrans.OvAmount + SuppTrans.OvGST)/SuppTrans.Rate ELSE 0 END)
	 AS AfterDateTrans,
			Sum(CASE WHEN SuppTrans.TranDate > '" . $_POST['PeriodEnd'] . "' THEN
	SuppTrans.OvAmount + SuppTrans.OvGST ELSE 0 END
	) AS FXAfterDateTrans
	FROM Suppliers,
		Currencies,
		SuppTrans
	WHERE Suppliers.CurrCode = Currencies.CurrAbrev
		AND Suppliers.SupplierID = SuppTrans.SupplierNo
		AND Suppliers.SupplierID >= '" . $_POST['FromCriteria'] . "'
		AND Suppliers.SupplierID <= '" . $_POST['ToCriteria'] . "'
	GROUP BY Suppliers.SupplierID, Suppliers.SuppName, Currencies.Currency";

	$SupplierResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		$title = "Supplier Balances - Problem Report.... ";
		include("includes/header.inc");
		echo "The Supplier details could not be retrieved by the SQL because - " . DB_error_msg($db);
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include("includes/footer.inc");
		exit;
	}

	include ("includes/PDFSupplierBalsPageHeader.inc");

	$TotBal=0;

	While ($SupplierBalances = DB_fetch_array($SupplierResult,$db)){

		$Balance = $SupplierBalances["Balance"] - $SupplierBalances['AfterDateTrans'];
		$FXBalance = $SupplierBalances["FXBalance"] - $SupplierBalances['FXAfterDateTrans'];

		if (ABS($Balance)>0.009 OR ABS($FXBalance)>0.009) {

			$DisplayBalance = number_format($SupplierBalances["Balance"] - $SupplierBalances['AfterDateTrans'],2);
			$DisplayFXBalance = number_format($SupplierBalances["FXBalance"] - $SupplierBalances['FXAfterDateTrans'],2);

			$TotBal += $Balance;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$SupplierBalances["SupplierID"] . " - " . $SupplierBalances["SuppName"],'left');
			$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayFXBalance,'right');
			$LeftOvers = $pdf->addTextWrap(350,$YPos,100,$FontSize,$SupplierBalances['Currency'],'left');


			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
			include("includes/PDFSupplierBalsPageHeader.inc");
			}
		}
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include("includes/PDFSupplierBalsPageHeader.inc");
	}

	$DisplayTotBalance = number_format($TotBal,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');

	$buf = $pdf->output();
	$len = strlen($buf);

	header("Content-type: application/pdf");
	header("Content-Length: $len");
	header("Content-Disposition: inline; filename=SupplierBals.pdf");
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

		echo "<TR><TD>From Supplier Code:</FONT></TD><TD><input Type=text maxlength=6 size=7 name=FromCriteria value='1'></TD></TR>";
		echo "<TR><TD>To Supplier Code:</TD><TD><input Type=text maxlength=6 size=7 name=ToCriteria value='zzzzzz'></TD></TR>";

		echo "<TR><TD>Balances As At:</TD><TD><SELECT Name='PeriodEnd'>";

		$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";
		$Periods = DB_query($sql,$db,"Could not retrieve period data because","The SQL that failed to get the period data was:");

		while ($myrow = DB_fetch_array($Periods,$db)){

			echo "<OPTION VALUE=" . $myrow["LastDate_In_Period"] . ">" . MonthAndYearFromSQLDate($myrow["LastDate_In_Period"]);

		}
	}

	echo "</SELECT></TD></TR>";


	echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='Print PDF'></CENTER>";

	include("includes/footer.inc");
} /*end of else not PrintPDF */

?>
