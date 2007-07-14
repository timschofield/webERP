<?php

/* $Revision: 1.16 $ */

$PageSecurity = 2;
include('includes/session.inc');

If (isset($_POST['TaxAuthority']) AND 
	isset($_POST['PrintPDF']) AND 
	isset($_POST['NoOfPeriods']) AND 
	isset($_POST['ToPeriod'])){

	include('includes/PDFStarter.php');
	
	$sql = 'SELECT lastdate_in_period 
		FROM periods 
		WHERE periodno=' . $_POST['ToPeriod'];
	$ErrMsg = _('Could not determine the last date of the period selected') . '. ' . _('The sql returned the following error');
	$PeriodEndResult = DB_query($sql,$db,$ErrMsg);
	$PeriodEndRow = DB_fetch_row($PeriodEndResult);
	$PeriodEnd = ConvertSQLDate($PeriodEndRow[0]);
	
	$result = DB_query('SELECT description FROM taxauthorities WHERE taxid=' . $_POST['TaxAuthority'],$db);
	$TaxAuthDescription = DB_fetch_row($result);
	$TaxAuthorityName =  $TaxAuthDescription[0];

	$FontSize=12;
	$pdf->addinfo('Title',_('Taxation Report'));
	$ReportTitle = $TaxAuthorityName . ' ' . _('Tax Report for') . ' ' . $_POST['NoOfPeriods'] . ' ' . _('months to') . ' ' . $PeriodEnd;
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
			(debtortrans.ovamount+debtortrans.ovfreight)/debtortrans.rate AS netamount,
			debtortrans.ovfreight/debtortrans.rate AS freightamount,
			debtortranstaxes.taxamount/debtortrans.rate AS tax
		FROM debtortrans
		INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
		INNER JOIN systypes ON debtortrans.type=systypes.typeid
		INNER JOIN debtortranstaxes ON debtortrans.id = debtortranstaxes.debtortransid
		WHERE debtortrans.prd >= ' . ($_POST['ToPeriod'] - $_POST['NoOfPeriods'] + 1) . '
		AND debtortrans.prd <= ' . $_POST['ToPeriod'] . '
		AND (debtortrans.type=10 OR debtortrans.type=11)
		AND debtortranstaxes.taxauthid = ' . $_POST['TaxAuthority'] . '
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
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize+2, _('Tax On Sales'),'left');
		$YPos -=$line_height;
	}

	$Ouputs =0;
	$OuputTax =0;
	$Inputs =0;
	$InputTax =0;

	While ($DebtorTransRow = DB_fetch_array($DebtorTransResult,$db)){

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
		$Outputs += $DebtorTransRow['netamount'];
		$OutputTax += $DebtorTransRow['tax'];
	} /*end listing while loop */

	if ($_POST['DetailOrSummary']=='Detail'){
		$YPos -=$line_height;

		if ($YPos < $Bottom_Margin + $line_height){
			include('includes/PDFTaxPageHeader.inc');
		}

		$pdf->line(410, $YPos+$line_height,530, $YPos+$line_height);

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,350,12, _('Total Outputs'),'right');
	}

	
	if ($_POST['DetailOrSummary']=='Detail'){
		/*Print out the outputs totals */
		$LeftOvers = $pdf->addTextWrap(410,$YPos,60,8, number_format($Outputs,2),'right');
		$LeftOvers = $pdf->addTextWrap(470,$YPos,60,8, number_format($OutputTax,2),'right');

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
			supptranstaxes.taxamount/supptrans.rate AS taxamt
		FROM supptrans
		INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
		INNER JOIN systypes ON supptrans.type=systypes.typeid 
		INNER JOIN supptranstaxes ON supptrans.id = supptranstaxes.supptransid 
		WHERE supptrans.trandate >= '" . $StartDateSQL . "'
		AND supptrans.trandate <= '" . FormatDateForSQL($PeriodEnd) . "'
		AND (supptrans.type=20 OR supptrans.type=21)  
		AND supptranstaxes.taxauthid = " . $_POST['TaxAuthority'] . "
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
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize+2, _('Tax On Purchases'),'left');
		$YPos -=$line_height;
	}


	While ($SuppTransRow = DB_fetch_array($SuppTransResult,$db)){

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
		$Inputs += $SuppTransRow['netamount'];
		$InputTax += $SuppTransRow['taxamt'];
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
	
	if ($_POST['DetailOrSummary']=='Detail'){
		/*Print out the input totals */
		$LeftOvers = $pdf->addTextWrap(410,$YPos,60,8, number_format($Inputs,2),'right');
		$LeftOvers = $pdf->addTextWrap(470,$YPos,60,8, number_format($InputTax,2),'right');

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


	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);

	$YPos =$YPos - $line_height;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('Return Suggested Entries'),'left');
	$YPos -= (2*$line_height);

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('Total Sales and Income (incl Tax)'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($Outputs+$OutputTax,2),'right');

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap(40,$YPos,180,$FontSize,_('Tax On Liable Sales'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($OutputTax,2),'right');


	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap(40,$YPos,200,$FontSize,_('Tax On Purchases'),'left');
	$LeftOvers = $pdf->addTextWrap(220,$YPos,100,$FontSize,number_format($InputTax,2),'right');

	$YPos -= (2*$line_height);
	$LeftOvers = $pdf->addTextWrap(40,$YPos,500,$FontSize,_('Adjustments for Tax paid to Customs, FBT, entertainments etc must also be entered'),'left');
	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap(40,$YPos,500,$FontSize,_('This information excludes Tax on journal entries/payments/receipts all Tax should be entered through AR/AP'),'left');
	
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

	$title=_('Tax Reporting');
	include('includes/header.inc');

	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

	echo '<TR><TD>' . _('Tax Authority To Report On:') . ':</FONT></TD>
			<TD><SELECT name=TaxAuthority>';
			
	$result = DB_query('SELECT taxid, description FROM taxauthorities',$db);
	while ($myrow = DB_fetch_array($result)){
		echo '<OPTION Value=' . $myrow['taxid'] . '>' . $myrow['description'];
	}
	echo '</SELECT></TD></TR>';
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