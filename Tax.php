<?php
/* $Revision: 1.1 $ */

include("includes/DateFunctions.inc");
$PageSecurity = 2;

Class Tax {
	Var $Rate;
	Var $OuputsAmount;
	Var $OutputTax;
	Var $InputsAmount;
	Var $InputTax;

	function Tax ($Rate){
		$this->Rate = $Rate;
		$this->OutputAmount =0;
		$this->OutputTax =0;
		$this->InputsAmount =0;
		$this->InputTax =0;
	}
}

If (isset($_POST['PrintPDF']) AND isset($_POST['NoOfPeriods']) AND isset($_POST['ToPeriod'])){

	include("config.php");
	include("includes/ConnectDB.inc");
	include("includes/PDFStarter_ros.inc");

	$sql = "SELECT LastDate_In_Period FROM Periods WHERE PeriodNo=" . $_POST['ToPeriod'];
	$ErrMsg = _('Could not determine the last date of the period selected. The sql returned the following error:');
	$PeriodEndResult = DB_query($sql,$db,$ErrMsg);
	$PeriodEndRow = DB_fetch_row($PeriodEndResult);

	$PeriodEnd = ConvertSQLDate($PeriodEndRow[0]);

	$FontSize=12;
	$pdf->addinfo('Title',_('Taxation Report'));
	$ReportTitle = _('Tax Report for') . ' ' . $_POST['NoOfPeriods'] . ' ' . _('months to') . ' ' . $PeriodEnd;
	$pdf->addinfo('Subject', $ReportTitle);


	$PageNumber=0;
	$line_height=12;


      /*Now get the invoices for the tax report */

	$SQL = 'SELECT DebtorTrans.TransNo,
			DebtorTrans.Type,
			SysTypes.TypeName,
			DebtorTrans.TranDate,
			DebtorTrans.DebtorNo,
			DebtorsMaster.Name,
			DebtorTrans.BranchCode,
			DebtorTrans.Order_,
			(OvAmount+OvFreight)/Rate AS NetAmount,
			OvFreight/Rate AS FreightAmount,
			OvGST/Rate AS Tax
		FROM DebtorTrans
		INNER JOIN DebtorsMaster ON DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
		INNER JOIN SysTypes ON DebtorTrans.Type=SysTypes.TypeID
		WHERE DebtorTrans.Prd >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
		AND DebtorTrans.Prd <= ' . $_POST['ToPeriod'] . '
		ORDER BY DebtorTrans.ID';



	$DebtorTransResult = DB_query($SQL,$db,'','',false,false); //doint trap errors in DB_query

	if (DB_error_no($db) !=0) {
		$title = _('Taxation Reporting Error');
		include('includes/header.inc');
		echo _('The accounts receiveable transation details could not be retrieved because') . ' ' . DB_error_msg($db);
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back To The Menu') . '</A>';
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include("includes/footer.inc");
		exit;
	}

	include ("includes/PDFTaxPageHeader.inc");

	$TotZeroRatedOutputs=0;
	$Taxes=array(); /* elements are TaxTotal, TaxRate, OutputTotal */

	While ($DebtorTransRow = DB_fetch_array($DebtorTransResult,$db)){

		$DisplayNet = number_format($DebtorTransRow["NetAmount"],2);
		$DisplayTax = number_format($DebtorTransRow["Tax"],2);

		/*Now need to figure out if the line items on the transation were all taxable */
		$SQL = 'SELECT TaxRate,
				Price*-Qty*(1-DiscountPercent) AS LineNetAmt,
				Price*-Qty*TaxRate*(1-DiscountPercent) AS LineTaxAmt
			FROM StockMoves
			WHERE Type=' . $DebtorTransRow['Type'] . '
			AND TransNo=' . $DebtorTransRow['TransNo'] . '
			AND Show_On_Inv_Crds=1';

		$ErrMsg = _('Could not retrieve the line item tax amounts for') . ' ' . $DebtorTransRow['TypeName'] . ' ' . $DebtorTranRow['TransNo'];

		$LineItemsResult = DB_query($SQL,$db,$ErrMsg);

		$TransTaxLeft = $DebtorTransRow["Tax"];

		While ($LineItemsRow = DB_fetch_array($LineItemsResult,$db)){

			if (!in_array($LineItemsRow['TaxRate'],$Taxes)){
				$Taxes[$LineItemsRow['TaxRate']] = new Tax($LineItemsRow['TaxRate']);
			}
			$Taxes[$LineItemsRow['TaxRate']]->OutputsAmount += $LineItemsRow['LineNetAmt'];
			$Taxes[$LineItemsRow['TaxRate']]->OutputTaxAmount += $LineItemsRow['LineTaxAmt'];
			$TransTaxLeft -= $LineItemsRow['LineTaxAmt'];
		}

		if ($DebtorTransRow['FreightAmount']!=0){
			$FreightRate = round($TransTaxLeft/$DebtorTransRow['FreightAmount'],2);

			echo "Warning the freight rate is not an existing rate of tax";

			if (!in_array($FreightRate,$Taxes)){
				$Taxes[$FreightRate] = new Tax($FreightRate);
			}
			$Taxes[$FreightRate]->OutputsAmount += $DebtorTransRow['FreightAmount'];
			$Taxes[$FreightRate]->OutputTaxAmount += $TransTaxLeft;
		}

		if (round($TransTaxLeft,2)!=0){
			$title = 'Tax Report Error';
			include ('includes/header.inc');
			$Msg = _('The total tax on') . ' ' . $DebtorTransRow['TypeName'] . ' ' . $DebtorTransRow['TransNo'] . ' ' . _('of') . $LineItemsRow['LineTaxAmt'] . ' ' . _('as per the transaction header record was not equal to the sum of the line items - there is a data inconsistency of') . ' ' . $TransTaxLeft;
			prnMsg($Msg,'error');
			include('includes/footer.inc');
			exit;
		}

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,$DebtorTransRow['TypeName'],'left');
		$LeftOvers = $pdf->addTextWrap(60,$YPos,40,$FontSize,$DebtorTransRow['TransNo'],'left');
		$LeftOvers = $pdf->addTextWrap(100,$YPos,60,$FontSize,ConvertSQLDate($DebtorTransRow['TranDate']),'left');
		$LeftOvers = $pdf->addTextWrap(160,$YPos,100,$FontSize,$DebtorTransRow['Name'],'left');
		$LeftOvers = $pdf->addTextWrap(260,$YPos,60,$FontSize,$DebtorTransRow['BranchCode'],'left');
		$LeftOvers = $pdf->addTextWrap(320,$YPos,60,$FontSize,$DebtorTransRow['NetAmount'],'right');
		$LeftOvers = $pdf->addTextWrap(380,$YPos,60,$FontSize,$DebtorTransRow['Tax'],'left');

		$YPos -=$line_height;
		if ($YPos < $Bottom_Margin + $line_height){
			include("includes/PDFTaxPageHeader.inc");
		}
	} /*end DebtorTrans listing while loop */

	$YPos -=$line_height;
	if ($YPos < $Bottom_Margin + (2*$line_height)){
		$PageNumber++;
		include("includes/PDFTaxPageHeader.inc");
	}


	$buf = $pdf->output();
	$len = strlen($buf);

	header("Content-type: application/pdf");
	header("Content-Length: $len");
	header("Content-Disposition: inline; filename=TaxReport.pdf");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: public");

	$pdf->stream();

} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	$title=_('Tax Reporting');
	include("includes/header.inc");
	include("includes/SQL_CommonFunctions.inc");

	$CompanyRecord = ReadInCompanyRecord($db);


	echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

	echo '<TR><TD>' . _('Return Covering:') . "</FONT></TD>
			<TD><SELECT name=NoOfPeriods>
			<OPTION Value=1> One Month
			<OPTION SELECTED Value=2>Two Months
			<OPTION VALUE=3>Quarter
			<OPTION VALUE=6>Six Months
			</SELECT></TD></TR>";


	echo '<TR><TD>' . _('Return To:') . "</TD>
			<TD><SELECT Name='ToPeriod'>";

	$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";

	$ErrMsg = _('Could not retrieve the period data because');
	$Periods = DB_query($sql,$db,$ErrMsg);

	while ($myrow = DB_fetch_array($Periods,$db)){

		echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow["LastDate_In_Period"]);
	}

	echo "</SELECT></TD></TR>";


	echo "</TABLE>
		<INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'>
		</CENTER>
		</FORM>";

	include("includes/footer.inc");
} /*end of else not PrintPDF */

?>
