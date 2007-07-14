<?php

$PageSecurity = 2;
include('includes/session.inc');


/* $Revision: 1.12 $ */

If (isset($_POST['PrintPDF']) 
	AND isset($_POST['FromCriteria']) 
	AND strlen($_POST['FromCriteria'])>=1 
	AND isset($_POST['ToCriteria']) 
	AND strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');

	$FontSize=12;
	$pdf->addinfo('Title',_('Supplier Balance Listing'));
	$pdf->addinfo('Subject',_('Supplier Balances'));

	$PageNumber=0;
	$line_height=12;


      /*Now figure out the aged analysis for the Supplier range under review */

	$SQL = "SELECT suppliers.supplierid,
			suppliers.suppname,
  			currencies.currency,
			SUM((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/supptrans.rate) AS balance,
			SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS fxbalance,
			SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
	(supptrans.ovamount + supptrans.ovgst)/supptrans.rate ELSE 0 END)
	 AS afterdatetrans,
	 	Sum(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' 
				AND (supptrans.type=22 OR supptrans.type=21) THEN
	       supptrans.diffonexch ELSE 0 END)
	 AS afterdatediffonexch,
			Sum(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
	supptrans.ovamount + supptrans.ovgst ELSE 0 END
	) AS fxafterdatetrans
	FROM suppliers,
		currencies,
		supptrans
	WHERE suppliers.currcode = currencies.currabrev
		AND suppliers.supplierid = supptrans.supplierno
		AND suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
		AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
	GROUP BY suppliers.supplierid, 
		suppliers.suppname, 
		currencies.currency";

	$SupplierResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		$title = _('Supplier Balances') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg(_('The Supplier details could not be retrieved by the SQL because') . ' ' . DB_error_msg($db),'error');
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFSupplierBalsPageHeader.inc');

	$TotBal=0;

	While ($SupplierBalances = DB_fetch_array($SupplierResult,$db)){

		$Balance = $SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'];
		$FXBalance = $SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'];

		if (ABS($Balance)>0.009 OR ABS($FXBalance)>0.009) {

			$DisplayBalance = number_format($SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'],2);
			$DisplayFXBalance = number_format($SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'],2);

			$TotBal += $Balance;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,220-$Left_Margin,$FontSize,$SupplierBalances['supplierid'] . ' - ' . $SupplierBalances['suppname'],'left');
			$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayBalance,'right');
			$LeftOvers = $pdf->addTextWrap(280,$YPos,60,$FontSize,$DisplayFXBalance,'right');
			$LeftOvers = $pdf->addTextWrap(350,$YPos,100,$FontSize,$SupplierBalances['currency'],'left');


			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFSupplierBalsPageHeader.inc');
			}
		}
	} /*end Supplier aged analysis while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include('includes/PDFSupplierBalsPageHeader.inc');
	}

	$DisplayTotBalance = number_format($TotBal,2);

	$LeftOvers = $pdf->addTextWrap(220,$YPos,60,$FontSize,$DisplayTotBalance,'right');

	$buf = $pdf->output();
	$len = strlen($buf);

	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=SupplierBals.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

} else { /*The option to print PDF was not hit */

	$title=_('Creditor Balances At A Period End');
	include('includes/header.inc');
	
	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

		echo '<TR><TD>' . _('From Supplier Code') . ":</FONT></TD>
			<TD><input Type=text maxlength=6 size=7 name=FromCriteria value='1'></TD></TR>";
		echo '<TR><TD>' . _('To Supplier Code') . ":</TD>
			<TD><input Type=text maxlength=6 size=7 name=ToCriteria value='zzzzzz'></TD></TR>";

		echo '<TR><TD>' . _('Balances As At') . ":</TD>
			<TD><SELECT Name='PeriodEnd'>";

		$sql = 'SELECT periodno, lastdate_in_period FROM periods';

		$ErrMsg = _('Could not retrieve period data because');
		$Periods = DB_query($sql,$db,$ErrMsg);

		while ($myrow = DB_fetch_array($Periods,$db)){

			echo '<OPTION VALUE=' . $myrow['lastdate_in_period'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);

		}
	}

	echo '</SELECT></TD></TR>';


	echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></CENTER>";

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>
