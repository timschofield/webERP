<?php
/* $Revision: 1.10 $ */

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


	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/DateFunctions.inc');
	include('includes/PDFStarter_ros.inc');


	$sql = 'SELECT lastdate_in_period 
		FROM periods 
		WHERE periodno=' . $_POST['ToPeriod'];
	$ErrMsg = _('Could not determine the last date of the period selected') . '. ' . _('The sql returned the following error');
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

	$SQL = 'SELECT debtortrans.transno,
			debtortrans.type,
			systypes.typename,
			debtortrans.trandate,
			debtortrans.debtorno,
			debtorsmaster.name,
			debtortrans.branchcode,
			debtortrans.order_,
			(ovamount+ovfreight)/rate AS netamount,
			ovfreight/rate AS freightamount,
			ovgst/rate AS tax
		FROM debtortrans
		INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
		INNER JOIN systypes ON debtortrans.type=systypes.typeid
		WHERE debtortrans.prd >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
		AND debtortrans.prd <= ' . $_POST['ToPeriod'] . '
		AND (debtortrans.type=10 OR debtortrans.type=11)
		ORDER BY debtortrans.id';

	$DebtorTransResult = DB_query($SQL,$db,'','',false,false); //doint trap errors in DB_query

	if (DB_error_no($db) !=0) {
		$title = _('Taxation Reporting Error');
		include('includes/header.inc');
		echo _('The accounts receiveable transation details could not be retrieved because') . ' ' . DB_error_msg($db);
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include('includes/footer.inc');
		exit;
	}

	if ($_POST['DetailOrSummary']=='Detail'){
		include ('includes/PDFTaxPageHeader.inc');
	}

	$Taxes = array(); /* elements are TaxTotal, TaxRate, OutputAmount, OutputTax, InputAmount, InputTax */
	$MessageCounter = 0;

	While ($DebtorTransRow = DB_fetch_array($DebtorTransResult,$db)){


		/*Now need to figure out if the line items on the transation were all taxable */
		$SQL = 'SELECT taxrate,
				price*-qty*(1-discountpercent) AS linenetamt,
				price*-qty*taxrate*(1-discountpercent) AS linetaxamt
			FROM stockmoves
			WHERE type=' . $DebtorTransRow['type'] . '
			AND transno=' . $DebtorTransRow['transno'] . '
			AND show_on_inv_crds=1';

		$ErrMsg = _('Could not retrieve the line item tax amounts for') . ' ' . $DebtorTransRow['typename'] . ' ' . $DebtorTransRow['transno'];

		$LineItemsResult = DB_query($SQL, $db, $ErrMsg);

		$TransTaxLeft = $DebtorTransRow['tax'];

		While ($LineItemsRow = DB_fetch_array($LineItemsResult,$db)){

			$LineItemsRow['taxrate'] = number_format($LineItemsRow['taxrate'],2);

			if (! isset($Taxes[$LineItemsRow['taxrate']]->Rate)){
				$Taxes[$LineItemsRow['taxrate']] = new Tax((float) $LineItemsRow['taxrate']);
			}

			$Taxes[$LineItemsRow['taxrate']]->OutputAmount += $LineItemsRow['linenetamt'];
			$Taxes[$LineItemsRow['taxrate']]->OutputTax += $LineItemsRow['linetaxamt'];
			$TransTaxLeft -= $LineItemsRow['linetaxamt'];
		} /* end of loop around invoice/credit note line items */

		if ($DebtorTransRow['freightamount']!=0){
			$FreightRate = number_format(ABS($TransTaxLeft/$DebtorTransRow['freightamount']),2);

			if (!isset($Taxes[$FreightRate]->Rate)){
				$Taxes[$FreightRate] = new Tax((float) $FreightRate);
			}
			$Taxes[$FreightRate]->OutputAmount += $DebtorTransRow['freightamount'];
			$Taxes[$FreightRate]->OutputTax += $TransTaxLeft;
			$TransTaxLeft =0;
		}


		if (ABS(round($TransTaxLeft,2))>0.01){
			$WarningMessage[$MessageCounter] =  _('The total tax on') . ' ' . $DebtorTransRow['typename'] . ' ' . $DebtorTransRow['transno'] . ' ' . _('of') . $LineItemsRow['linetaxamt'] . ' ' . _('as per the transaction header record was not equal to the sum of the line items') . ' - ' . _('there is a data inconsistency of') . ' ' . $TransTaxLeft ;
			$MessageCounter++;
		}

		if ($_POST['DetailOrSummary']=='Detail'){
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize, $DebtorTransRow['typename'],'left');
			$LeftOvers = $pdf->addTextWrap(100,$YPos,40,$FontSize, $DebtorTransRow['transno'],'left');
			$LeftOvers = $pdf->addTextWrap(140,$YPos,60,$FontSize,ConvertSQLDate($DebtorTransRow['trandate']),'left');
			$LeftOvers = $pdf->addTextWrap(200,$YPos,150,$FontSize, $DebtorTransRow['name'],'left');
			$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize, $DebtorTransRow['branchcode'],'left');
			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize, number_format($DebtorTransRow['netamount'],2),'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize, number_format($DebtorTransRow['tax'],2),'right');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				include('includes/PDFTaxPageHeader.inc');
			}
		}
	} /*end listing while loop */

	if ($_POST['DetailOrSummary']=='Detail'){
		$YPos -=$line_height;

		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFTaxPageHeader.inc');
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

	$Date_Array = explode('/',$PeriodEnd);
	if ($_SESSION['DefaultDateFormat']=='d/m/Y'){
		$StartDateSQL = Date('Y-m-d', mktime(0,0,0, (int)$Date_Array[1]-$_POST['NoOfPeriods']+1,1,(int)$Date_Array[2]));
	} elseif ($_SESSION['DefaultDateFormat']=='m/d/Y') {
		$StartDateSQL = Date('Y-m-d', mktime(0,0,0, (int)$Date_Array[0]-$_POST['NoOfPeriods']+1,1,(int)$Date_Array[2]));
	}


	$SQL = "SELECT supptrans.type,
			supptrans.suppreference,
			systypes.typename,
			supptrans.trandate,
			suppliers.suppname,
   			supptrans.ovamount/supptrans.rate AS netamount,
			supptrans.ovgst/supptrans.rate AS taxamt
		FROM supptrans
		INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
		INNER JOIN systypes ON supptrans.type=systypes.typeid
		WHERE supptrans.trandate >= '" . $StartDateSQL . "'
		AND supptrans.trandate <= '" . FormatDateForSQL($PeriodEnd) . "'
		AND (supptrans.type=20 OR supptrans.type=21)
		ORDER BY supptrans.id";

	$SuppTransResult = DB_query($SQL,$db,'','',false,false); //doint trap errors in DB_query

	if (DB_error_no($db) !=0) {
		$title = _('Taxation Reporting Error');
		include('includes/header.inc');
		echo _('The accounts payable transation details could not be retrieved because') . ' ' . DB_error_msg($db);
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
		if ($debug==1){
			echo "<BR>$SQL";
		}
		include('includes/footer.inc');
		exit;
	}

	if ($_POST['DetailOrSummary']=='Detail'){
		include ('includes/PDFTaxPageHeader.inc');
	}

	$Taxes['Inputs'] = new Tax(0);

	While ($SuppTransRow = DB_fetch_array($SuppTransResult,$db)){

		/*$TaxRate = number_format($SuppTransRow['taxrate'],2);

		if (! isset($Taxes[$TaxRate]->Rate)){
			$Taxes[$TaxRate] = new Tax((float) $TaxRate);
		}

		$Taxes[$TaxRate]->InputAmount += $SuppTransRow['NetAmount'];
		$Taxes[$TaxRate]->InputTax += $SuppTransRow['TaxAmt'];
		*/
		$Taxes['Inputs']->InputAmount += $SuppTransRow['netamount'];
		$Taxes['Inputs']->InputTax += $SuppTransRow['taxamt'];


		if ($_POST['DetailOrSummary']=='Detail'){
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize, $SuppTransRow['typename'],'left');
			$LeftOvers = $pdf->addTextWrap(100,$YPos,40,$FontSize, $SuppTransRow['suppreference'],'left');
			$LeftOvers = $pdf->addTextWrap(140,$YPos,60,$FontSize,ConvertSQLDate($SuppTransRow['trandate']),'left');
			$LeftOvers = $pdf->addTextWrap(200,$YPos,150,$FontSize, $SuppTransRow['suppname'],'left');

			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize, number_format($SuppTransRow['netamount'],2),'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize, number_format($SuppTransRow['taxamt'],2),'right');

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				include('includes/PDFTaxPageHeader.inc');
			}
		}
	} /*end listing while loop */

	if ($_POST['DetailOrSummary']=='Detail'){
		$YPos -=$line_height;

		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFTaxPageHeader.inc');
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

	$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$FontSize =10;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $ReportTitle . ' ' . _('Summary'));

	$FontSize = 8;
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '    ' . _('Page') . ' ' . $PageNumber);

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

	if (count($WarningMessage)>0){
		$PageNumber++;
		$pdf->newPage();

		/*There are warnings - must shown them */

		$FontSize=8;
		$YPos= $Page_Height-$Top_Margin;

		$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

		$YPos -=$line_height;

		$FontSize =10;
		$pdf->addText($Left_Margin, $YPos, $FontSize, $ReportTitle . ' ' . _('Warnings'));

		$FontSize = 8;
		$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '    ' . _('Page') . ' ' . $PageNumber);

		$YPos -=(3*$line_height);

		foreach ($WarningMessage as $Message){
			while (strlen($Message)>0){
				$Message = $pdf->addTextWrap(40,$YPos,450,$FontSize,$Message,'left');
				$YPos -= $line_height;
			}
		}
	}


	$buf = $pdf->output();
	$len = strlen($buf);



	header('Content-type: application/pdf');
	header("Content-Length: $len");
	header('Content-Disposition: inline; filename=TaxReport.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->stream();

} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title=_('Tax Reporting');
	include('includes/header.inc');

	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

	echo '<TR><TD>' . _('Return Covering') . ':</FONT></TD>
			<TD><SELECT name=NoOfPeriods>
			<OPTION Value=1>' . _('One Month') .
			'<OPTION SELECTED Value=2>' ._('Two Months') .
			'<OPTION VALUE=3>' . _('Quarter') .
			'<OPTION VALUE=6>' . _('Six Months') .
			'</SELECT></TD></TR>';


	echo '<TR><TD>' . _('Return To') . ":</TD>
			<TD><SELECT Name='ToPeriod'>";


	$DefaultPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),0,Date('Y'))),$db);

	$sql = 'SELECT periodno, 
			lastdate_in_period 
		FROM periods';

	$ErrMsg = _('Could not retrieve the period data because');
	$Periods = DB_query($sql,$db,$ErrMsg);

	while ($myrow = DB_fetch_array($Periods,$db)){
		if ($myrow['periodno']==$DefaultPeriod){
			echo '<OPTION SELECTED VALUE=' . $myrow['periodno'] . '>' . ConvertSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<OPTION VALUE=' . $myrow['periodno'] . '>' . ConvertSQLDate($myrow['lastdate_in_period']);
		}
	}

	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Detail Or Summary Only') . ":</FONT></TD>
			<TD><SELECT name='DetailOrSummary'>
			<OPTION Value='Detail'>" . _('Detail and Summary') .
			"<OPTION SELECTED Value='Summary'>" . _('Summary Only') .
			"</SELECT></TD></TR>";


	echo "</TABLE>
		<INPUT TYPE=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'>
		</CENTER>
		</FORM>";

	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>