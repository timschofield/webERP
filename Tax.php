<?php
/* $Revision: 1.3 $ */

$PageSecurity = 2;

Class Tax {
	Var $Rate;
	Var $OuputAmount;
	Var $OutputTax;
	Var $InputAmount;
	Var $InputTax;

	function Tax ($Rate){
		$this->Rate = $Rate;
		$this->OutputAmount =0;
		$this->OutputTax =0;
		$this->InputAmount =0;
		$this->InputTax =0;
	}
}


If (isset($_POST['PrintPDF']) AND isset($_POST['NoOfPeriods']) AND isset($_POST['ToPeriod'])){


	include("config.php");
	include("includes/ConnectDB.inc");
	include("includes/DateFunctions.inc");
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
		AND (DebtorTrans.Type=10 OR DebtorTrans.Type=11)
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

	if ($_POST['DetailOrSummary']=='Detail'){
		include ("includes/PDFTaxPageHeader.inc");
	}

	$Taxes = array(); /* elements are TaxTotal, TaxRate, OutputAmount, OutputTax, InputAmount, InputTax */

	While ($DebtorTransRow = DB_fetch_array($DebtorTransResult,$db)){


		/*Now need to figure out if the line items on the transation were all taxable */
		$SQL = 'SELECT TaxRate,
				Price*-Qty*(1-DiscountPercent) AS LineNetAmt,
				Price*-Qty*TaxRate*(1-DiscountPercent) AS LineTaxAmt
			FROM StockMoves
			WHERE Type=' . $DebtorTransRow['Type'] . '
			AND TransNo=' . $DebtorTransRow['TransNo'] . '
			AND Show_On_Inv_Crds=1';

		$ErrMsg = _('Could not retrieve the line item tax amounts for') . ' ' . $DebtorTransRow['TypeName'] . ' ' . $DebtorTranRow['TransNo'];

		$LineItemsResult = DB_query($SQL, $db, $ErrMsg);

		$TransTaxLeft = $DebtorTransRow["Tax"];

		While ($LineItemsRow = DB_fetch_array($LineItemsResult,$db)){

			$LineItemsRow['TaxRate'] = number_format($LineItemsRow['TaxRate'],2);

			if (! isset($Taxes[$LineItemsRow['TaxRate']]->Rate)){
				$Taxes[$LineItemsRow['TaxRate']] = new Tax((float) $LineItemsRow['TaxRate']);
			}

			$Taxes[$LineItemsRow['TaxRate']]->OutputAmount += $LineItemsRow['LineNetAmt'];
			$Taxes[$LineItemsRow['TaxRate']]->OutputTax += $LineItemsRow['LineTaxAmt'];
			$TransTaxLeft -= $LineItemsRow['LineTaxAmt'];
		} /* end of loop around invoice/credit note line items */

		if ($DebtorTransRow['FreightAmount']!=0){
			$FreightRate = number_format(ABS($TransTaxLeft/$DebtorTransRow['FreightAmount']),2);

			if (!isset($Taxes[$FreightRate]->Rate)){
				$Taxes[$FreightRate] = new Tax((float) $FreightRate);
			}
			$Taxes[$FreightRate]->OutputAmount += $DebtorTransRow['FreightAmount'];
			$Taxes[$FreightRate]->OutputTax += $TransTaxLeft;
			$TransTaxLeft =0;
		}


		if (round($TransTaxLeft,2)!=0){
			$title = 'Tax Report Error';
			include ('includes/header.inc');
			$Msg = '<BR>' . _('The total tax on') . ' ' . $DebtorTransRow['TypeName'] . ' ' . $DebtorTransRow['TransNo'] . ' ' . _('of') . $LineItemsRow['LineTaxAmt'] . ' ' . _('as per the transaction header record was not equal to the sum of the line items - there is a data inconsistency of') . ' ' . $TransTaxLeft ;
			prnMsg($Msg, 'error');
			echo "<BR>Freight Amount = " . $DebtorTransRow['FreightAmount'];
			echo "<BR>Freight Rate = " . $FreightRate;

			include('includes/footer.inc');
			exit;
		}

		if ($_POST['DetailOrSummary']=='Detail'){
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize, $DebtorTransRow['TypeName'],'left');
			$LeftOvers = $pdf->addTextWrap(100,$YPos,40,$FontSize, $DebtorTransRow['TransNo'],'left');
			$LeftOvers = $pdf->addTextWrap(140,$YPos,60,$FontSize,ConvertSQLDate($DebtorTransRow['TranDate']),'left');
			$LeftOvers = $pdf->addTextWrap(200,$YPos,150,$FontSize, $DebtorTransRow['Name'],'left');
			$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize, $DebtorTransRow['BranchCode'],'left');
			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize, number_format($DebtorTransRow['NetAmount'],2),'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize, number_format($DebtorTransRow['Tax'],2),'right');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				include("includes/PDFTaxPageHeader.inc");
			}
		}
	} /*end listing while loop */

	if ($_POST['DetailOrSummary']=='Detail'){
		$YPos -=$line_height;

		if ($YPos < $Bottom_Margin + $line_height){
			include("includes/PDFTaxPageHeader.inc");
		}

		$pdf->line(410, $YPos+$line_height,530, $YPos+$line_height);

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,350,12, _('Total Outputs'),'right');
	}

	/*Accumulate the outputs totals */
	$TotalOutputAmount = 0;
	$TotalOutputTax = 0;

	foreach ($Taxes as $TaxDetails) {
		$TotalOutputAmount += $TaxDetails->OutputAmount;
		$TotalOutputTax += $TaxDetails->OutputTax;
	}

	if ($_POST['DetailOrSummary']=='Detail'){
		/*Print out the outputs totals */
		$LeftOvers = $pdf->addTextWrap(410,$YPos,60,8, number_format($TotalOutputAmount,2),'right');
		$LeftOvers = $pdf->addTextWrap(470,$YPos,60,8, number_format($TotalOutputTax,2),'right');

		/*Rule off under output totals */
		$pdf->line(410, $YPos-5,530, $YPos-5);
	}

	/*Now do the inputs from SuppTrans */
	/*Only have dates in SuppTrans no periods so need to get the starting date */

	$Date_Array = explode("/",$PeriodEnd);
	if ($DefaultDateFormat=="d/m/Y"){
		$StartDateSQL = Date('Y-m-d', mktime(0,0,0, (int)$Date_Array[1]-$_POST['NoOfPeriods']+1,1,(int)$Date_Array[2]));
	} elseif ($DefaultDateFormat=="m/d/Y") {
		$StartDateSQL = Date('Y-m-d', mktime(0,0,0, (int)$Date_Array[0]-$_POST['NoOfPeriods']+1,1,(int)$Date_Array[2]));
	}


	$SQL = "SELECT SuppTrans.Type,
			SuppTrans.SuppReference,
			SysTypes.TypeName,
			SuppTrans.TranDate,
			Suppliers.SuppName,
   			SuppTrans.OvAmount/SuppTrans.Rate AS NetAmount,
			SuppTrans.OvGST/SuppTrans.Rate AS TaxAmt
		FROM SuppTrans
		INNER JOIN Suppliers ON SuppTrans.SupplierNo=Suppliers.SupplierID
		INNER JOIN SysTypes ON SuppTrans.Type=SysTypes.TypeID
		WHERE SuppTrans.TranDate >= '" . $StartDateSQL . "'
		AND SuppTrans.TranDate <= '" . FormatDateForSQL($PeriodEnd) . "'
		AND (SuppTrans.Type=20 OR SuppTrans.Type=21)
		ORDER BY SuppTrans.ID";



	$SuppTransResult = DB_query($SQL,$db,'','',false,false); //doint trap errors in DB_query

	if (DB_error_no($db) !=0) {
		$title = _('Taxation Reporting Error');
		include('includes/header.inc');
		echo _('The accounts payable transation details could not be retrieved because') . ' ' . DB_error_msg($db);
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back To The Menu') . '</A>';
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include("includes/footer.inc");
		exit;
	}

	if ($_POST['DetailOrSummary']=='Detail'){
		include ("includes/PDFTaxPageHeader.inc");
	}

	$Taxes['Inputs'] = new Tax(0);

	While ($SuppTransRow = DB_fetch_array($SuppTransResult,$db)){

		/*$TaxRate = number_format($SuppTransRow['TaxRate'],2);

		if (! isset($Taxes[$TaxRate]->Rate)){
			$Taxes[$TaxRate] = new Tax((float) $TaxRate);
		}

		$Taxes[$TaxRate]->InputAmount += $SuppTransRow['NetAmount'];
		$Taxes[$TaxRate]->InputTax += $SuppTransRow['TaxAmt'];
		*/
		$Taxes['Inputs']->InputAmount += $SuppTransRow['NetAmount'];
		$Taxes['Inputs']->InputTax += $SuppTransRow['TaxAmt'];


		if ($_POST['DetailOrSummary']=='Detail'){
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize, $SuppTransRow['TypeName'],'left');
			$LeftOvers = $pdf->addTextWrap(100,$YPos,40,$FontSize, $SuppTransRow['SuppReference'],'left');
			$LeftOvers = $pdf->addTextWrap(140,$YPos,60,$FontSize,ConvertSQLDate($SuppTransRow['TranDate']),'left');
			$LeftOvers = $pdf->addTextWrap(200,$YPos,150,$FontSize, $SuppTransRow['SuppName'],'left');

			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize, number_format($SuppTransRow['NetAmount'],2),'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize, number_format($SuppTransRow['TaxAmt'],2),'right');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				include("includes/PDFTaxPageHeader.inc");
			}
		}
	} /*end listing while loop */

	if ($_POST['DetailOrSummary']=='Detail'){
		$YPos -=$line_height;

		if ($YPos < $Bottom_Margin + $line_height){
			include("includes/PDFTaxPageHeader.inc");
		}

		$pdf->line(410, $YPos+$line_height,530, $YPos+$line_height);

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,350,12, _('Total Inputs'),'right');
	}
	/*Accumulate the input totals */

	$TotalInputAmount = $Taxes['Inputs']->InputAmount;
	$TotalInputTax = $Taxes['Inputs']->InputTax;

	/*
	foreach ($Taxes as $TaxDetails) {
		$TotalInputAmount += $TaxDetails->InputAmount;
		$TotalInputTax += $TaxDetails->InputTax;
	}
	*/

	if ($_POST['DetailOrSummary']=='Detail'){
		/*Print out the input totals */
		$LeftOvers = $pdf->addTextWrap(410,$YPos,60,8, number_format($TotalInputAmount,2),'right');
		$LeftOvers = $pdf->addTextWrap(470,$YPos,60,8, number_format($TotalInputTax,2),'right');

		/*Rule off under input totals */
		$pdf->line(410, $YPos-5,530, $YPos-5);

		/*New page before summary */
		$PageNumber++;
		$pdf->newPage();
	}
	/*OK and now the summary */

	$FontSize=8;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addText($Left_Margin, $YPos,$FontSize, $CompanyName);

	$YPos -=$line_height;

	$FontSize =10;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $ReportTitle . ' ' . _('Summary'));

	$FontSize = 8;
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed:') . ' ' . Date("d M Y") . '    ' . _('Page') . ' ' . $PageNumber);

	$YPos -=(3*$line_height);

	/*Draw a rectangle to put the headings in     */
	$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,60,$FontSize,_('Tax Rate'),'center');
	$LeftOvers = $pdf->addTextWrap(100,$YPos,100,$FontSize,_('Outputs'),'center');
	$LeftOvers = $pdf->addTextWrap(200,$YPos,100,$FontSize,_('Ouptput Tax'),'center');
	$LeftOvers = $pdf->addTextWrap(300,$YPos,100,$FontSize,_('Inputs'),'center');
	$LeftOvers = $pdf->addTextWrap(400,$YPos,100,$FontSize,_('Input Tax'),'center');

	$YPos =$YPos - (2*$line_height);


	foreach ($Taxes as $TaxDetails) {
		$LeftOvers = $pdf->addTextWrap(40,$YPos,60,$FontSize,number_format($TaxDetails->Rate*100,2) .'%','center');
		$LeftOvers = $pdf->addTextWrap(100,$YPos,100,$FontSize,number_format($TaxDetails->OutputAmount,2),'right');
		$LeftOvers = $pdf->addTextWrap(200,$YPos,100,$FontSize,number_format($TaxDetails->OutputTax,2),'right');
		$LeftOvers = $pdf->addTextWrap(300,$YPos,100,$FontSize,number_format($TaxDetails->InputAmount,2),'right');
		$LeftOvers = $pdf->addTextWrap(400,$YPos,100,$FontSize,number_format($TaxDetails->InputTax,2),'right');
		$YPos =$YPos - $line_height;
	}

	$YPos =$YPos - $line_height;

/*Print out the totals for the boxes on the NZ GST Return
	This needs to be modified for the requirements of other countries*/

	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);

	$YPos =$YPos - $line_height;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('GST Return Suggested Entries'),'left');
	$YPos -= (2*$line_height);

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('BOX 5 - Total Sales and Income (incl GST)'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($TotalOutputAmount+$TotalOutputTax,2),'right');

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('BOX 6 - Zero Rated Supplies'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($Taxes['0.00']->OutputAmount,2),'right');

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('BOX 7 - Liable Sales'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($TotalOutputAmount+$TotalOutputTax-$Taxes['0.00']->OutputAmount,2),'right');

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('BOX 8 - GST On Liable Sales'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($TotalOutputTax,2),'right');


	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap(40,$YPos,200,$FontSize,_('BOX 12 - GST On Purchases'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($TotalInputTax,2),'right');

	$YPos -= (2*$line_height);
	$LeftOvers = $pdf->addTextWrap(40,$YPos,500,$FontSize,_('Adjustments for GST paid to NZ Customs, FBT, entertainments etc must also be entered'),'left');
	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap(40,$YPos,500,$FontSize,_('This information excludes GST on journal entries/payments/receipts all GST should be entered through AR/AP'),'left');
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
	include("includes/DateFunctions.inc");
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


	$DefaultPeriod = GetPeriod(Date($DefaultDateFormat,Mktime(0,0,0,Date("m"),0,Date("Y"))),$db);

	$sql = "SELECT PeriodNo, LastDate_In_Period FROM Periods";

	$ErrMsg = _('Could not retrieve the period data because');
	$Periods = DB_query($sql,$db,$ErrMsg);

	while ($myrow = DB_fetch_array($Periods,$db)){
		if ($myrow['PeriodNo']==$DefaultPeriod){
			echo '<OPTION SELECTED VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow["LastDate_In_Period"]);
		} else {
			echo '<OPTION VALUE=' . $myrow['PeriodNo'] . '>' . ConvertSQLDate($myrow["LastDate_In_Period"]);
		}
	}

	echo "</SELECT></TD></TR>";

	echo '<TR><TD>' . _('Detail Or Summary Only:') . "</FONT></TD>
			<TD><SELECT name='DetailOrSummary'>
			<OPTION Value='Detail'>Detail and Summary
			<OPTION SELECTED Value='Summary'>Summary Only
			</SELECT></TD></TR>";


	echo "</TABLE>
		<INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'>
		</CENTER>
		</FORM>";

	include("includes/footer.inc");
} /*end of else not PrintPDF */

?>
