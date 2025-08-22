<?php

include('includes/session.php');
if (isset($_POST['PaymentDate'])){$_POST['PaymentDate'] = ConvertSQLDate($_POST['PaymentDate']);}

If ((isset($_POST['PrintPDF']))
			AND isset($_POST['FromCriteria'])
			AND mb_strlen($_POST['FromCriteria'])>=1
			AND isset($_POST['ToCriteria'])
			AND mb_strlen($_POST['ToCriteria'])>=1)	{
	/*Now figure out the invoice less credits due for the Supplier range under review */

	$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode,
					supptrans.id,
					currencies.decimalplaces AS currdecimalplaces
			FROM supptrans INNER JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
			INNER JOIN paymentterms ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
			WHERE supptrans.type=22
			AND trandate ='" . FormatDateForSQL($_POST['PaymentDate']) . "'
			AND supplierno >= '" . $_POST['FromCriteria'] . "'
			AND supplierno <= '" . $_POST['ToCriteria'] . "'
			AND suppliers.remittance=1
			ORDER BY supplierno";

	$SuppliersResult = DB_query($SQL);
	if (DB_num_rows($SuppliersResult)==0){
		//then there aint awt to print
		$Title = __('Print Remittance Advices Error');
		include('includes/header.php');
		prnMsg(__('There were no remittance advices to print out for the supplier range and payment date specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' .  __('Back') . '</a>';
		include('includes/footer.php');
		exit();
	}
/*then print the report */

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',__('Remittance Advice'));
	$pdf->addInfo('Subject',__('Remittance Advice') . ' - ' . __('suppliers from') . ' ' . $_POST['FromCriteria'] . ' ' . __('to') . ' ' . $_POST['ToCriteria'] . ' ' . __('and Paid On') . ' ' .  $_POST['PaymentDate']);

	$LineHeight=12;

	$SupplierID ='';
	$RemittanceAdviceCounter =0;
	while ($SuppliersPaid = DB_fetch_array($SuppliersResult)){

		$PageNumber=1;
		PageHeader();
		$RemittanceAdviceCounter++;
		$SupplierID = $SuppliersPaid['supplierid'];
		$SupplierName = $SuppliersPaid['suppname'];
		$AccumBalance = 0;

		/* Now get the transactions and amounts that the payment was allocated to */
		$SQL = "SELECT systypes.typename,
						supptrans.suppreference,
						supptrans.trandate,
						supptrans.transno,
						suppallocs.amt,
						(supptrans.ovamount + supptrans.ovgst ) AS trantotal
				FROM supptrans
				INNER JOIN systypes ON systypes.typeid = supptrans.type
				INNER JOIN suppallocs ON suppallocs.transid_allocto=supptrans.id
				WHERE suppallocs.transid_allocfrom='" . $SuppliersPaid['id'] . "'
				ORDER BY supptrans.type,
						 supptrans.transno";

		$ErrMsg = __('The details of the payment to the supplier could not be retrieved');
		$TransResult = DB_query($SQL, $ErrMsg);

		while ($DetailTrans = DB_fetch_array($TransResult)){

			$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$pdf->addTextWrap($Left_Margin+5, $YPos, 80,$FontSize,$DetailTrans['typename'], 'left');
			$pdf->addTextWrap($Left_Margin+95, $YPos, 80,$FontSize,$DisplayTranDate, 'left');
			$pdf->addTextWrap($Left_Margin+175, $YPos, 80,$FontSize,$DetailTrans['suppreference'], 'left');
			$pdf->addTextWrap($Left_Margin+255, $YPos, 80,$FontSize,locale_number_format($DetailTrans['trantotal'],$SuppliersPaid['currdecimalplaces']), 'right');
			$pdf->addTextWrap($Left_Margin+355, $YPos,80,$FontSize,locale_number_format($DetailTrans['amt'],$SuppliersPaid['currdecimalplaces']), 'right');
			$AccumBalance += $DetailTrans['amt'];

			$YPos -=$LineHeight;
			if ($YPos < $Bottom_Margin + $LineHeight){
				$PageNumber++;
				PageHeader();
			}
		} /*end while there are detail transactions to show */
		$YPos -= (0.5*$LineHeight);
    	$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);

	    $pdf->addTextWrap($Left_Margin+280,$YPos,75,$FontSize,__('Total Payment:'), 'right');

        $TotalPayments += $AccumBalance;

	    $pdf->addTextWrap($Left_Margin+355,$YPos,80,$FontSize,locale_number_format($AccumBalance,$SuppliersPaid['currdecimalplaces']), 'right');

	    $YPos -= (1.5*$LineHeight);
	    $pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);

	} /* end while there are supplier payments to retrieve allocations for */


	$FileName=$_SESSION['DatabaseName']. '_' . __('Remittance_Advices') . '_' . date('Y-m-d').'.pdf';
	$pdf->OutputD($FileName);
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title=__('Remittance Advices');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');

    echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' '
        . $Title . '</p>';
	/* show form to allow input	*/

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<fieldset>
			<legend>', __('Remittance Advice Criteria'), '</legend>';

	if (!isset($_POST['FromCriteria']) or mb_strlen($_POST['FromCriteria'])<1){
		$DefaultFromCriteria = '1';
	} else {
		$DefaultFromCriteria = $_POST['FromCriteria'];
	}
	if (!isset($_POST['ToCriteria']) or mb_strlen($_POST['ToCriteria'])<1){
		$DefaultToCriteria = 'zzzzzzz';
	} else {
		$DefaultToCriteria = $_POST['ToCriteria'];
	}
	echo '<field>
			<label for="FromCriteria">' . __('From Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" />
		</field>';
	echo '<field>
			<label for="ToCriteria">' . __('To Supplier Code') . ':</label>
			<input type="text" maxlength="6" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" />
		</field>';

	if (!isset($_POST['PaymentDate'])){
		$DefaultDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')+1,0 ,Date('y')));
	} else {
		$DefaultDate = $_POST['PaymentDate'];
	}

	echo '<field>
			<label for="PaymentDate">' . __('Date Of Payment') . ':</label>
			<input type="date" name="PaymentDate" maxlength="10" size="11" value="' . FormatDateForSQL($DefaultDate) . '" />
		</field>';

	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
		</div>';

    echo '</form>';

	include('includes/footer.php');
} /*end of else not PrintPDF */

function PageHeader(){
	global $pdf;
	global $PageNumber;
	global $YPos;
	global $Xpos;
	global $LineHeight;
	global $Page_Height;
	global $Top_Margin;
	global $Page_Width;
	global $Right_Margin;
	global $Left_Margin;
	global $Bottom_Margin;
	global $FontSize;
	global $SupplierName;
	global $AccumBalance;
	global $RemittanceAdviceCounter;
	global $SuppliersPaid;

	if ($RemittanceAdviceCounter>0){
		$pdf->newPage();
	}

	$YPos = $Page_Height - $Top_Margin;

	$pdf->addJpegFromFile($_SESSION['LogoFile'],$Page_Width/2 -50,$YPos-50,0,30);

	// Title
	$FontSize =15;
	$XPos = $Page_Width/2 - 110;
	$pdf->addText($XPos, $YPos,$FontSize, __('Remittance Advice') );

	$FontSize = 10;
	$pdf->addText($XPos + 150, $YPos,$FontSize, ' '. __('printed:').' ' . Date($_SESSION['DefaultDateFormat']));

	$pdf->addText($XPos + 280, $YPos,$FontSize, __('Page').': ' . $PageNumber);

	/*Now print out company info at the top left */

	$XPos = $Left_Margin;
	$YPos = $Page_Height - $Top_Margin - 20;

	$FontSize = 10;
	$LineHeight = 13;
	$LineCount = 0;

	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);

	$FontSize = 8;
	$LineHeight = 10;

	if ($_SESSION['CompanyRecord']['regoffice1'] <> '') {
	  $LineCount += 1;
	  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
	}
	if ($_SESSION['CompanyRecord']['regoffice2'] <> '') {
	  $LineCount += 1;
	  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
	}
	if (($_SESSION['CompanyRecord']['regoffice3'] <> '') OR ($_SESSION['CompanyRecord']['regoffice4'] <> '') OR ($_SESSION['CompanyRecord']['regoffice5'] <> '')) {
	  $LineCount += 1;
	  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);  // country in 6 not printed
	}
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, __('Phone') . ':' . $_SESSION['CompanyRecord']['telephone']);
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, __('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, __('Email') . ': ' . $_SESSION['CompanyRecord']['email']);


	/*Now the supplier details and remittance advice address */

	$XPos = $Left_Margin+20;
	$YPos = $Page_Height - $Top_Margin - 120;

	$LineCount = 0;
	$FontSize = 10;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $SuppliersPaid['suppname']);
	$LineCount ++;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $SuppliersPaid['address1']);
	$LineCount ++;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $SuppliersPaid['address2']);
	$LineCount ++;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $SuppliersPaid['address3'] . ' ' . $SuppliersPaid['address4']  . ' ' . $SuppliersPaid['address5']  . ' ' . $SuppliersPaid['address6']);
	$LineCount += 2;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, __('Our Code:') . ' ' .$SuppliersPaid['supplierid']);

	$YPos = $Page_Height - $Top_Margin - 120;

	$FontSize=8;
	$XPos = $Page_Width/2 - 60;
	$pdf->addText($XPos, $YPos,$FontSize, __('All amounts stated in') . ' - ' . $SuppliersPaid['currcode']);
	$YPos -= $LineHeight;
	$pdf->addText($XPos, $YPos,$FontSize, $SuppliersPaid['terms']);

	$YPos = $Page_Height - $Top_Margin - 180;
	//$YPos -= $LineHeight;
	$XPos = $Left_Margin;

	/*draw a nice curved corner box around the statement details */
	/*from the top right */
	$pdf->partEllipse($Page_Width-$Right_Margin-10,$YPos-10,0,90,10,10);
	/*line to the top left */
	$pdf->line($Page_Width-$Right_Margin-10, $YPos,$Left_Margin+10, $YPos);
	/*Do top left corner */
	$pdf->partEllipse($Left_Margin+10, $YPos-10,90,180,10,10);
	/*Do a line to the bottom left corner */
	$pdf->line($Left_Margin, $YPos-10,$Left_Margin, $Bottom_Margin+10);
	/*Now do the bottom left corner 180 - 270 coming back west*/
	$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
	/*Now a line to the bottom right */
	$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
	/*Now do the bottom right corner */
	$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
	/*Finally join up to the top right corner where started */
	$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $YPos-10);

	/*Finally join up to the top right corner where started */
	$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $YPos-10);

	$YPos -= $LineHeight;
	$FontSize =10;
	/*Set up headings */
	$pdf->addText($Left_Margin+10, $YPos,$FontSize, __('Trans Type') );
	$pdf->addText($Left_Margin+100, $YPos,$FontSize, __('Date') );
	$pdf->addText($Left_Margin+180, $YPos,$FontSize, __('Reference') );
	$pdf->addText($Left_Margin+310, $YPos,$FontSize, __('Total') );
	$pdf->addText($Left_Margin+390, $YPos,$FontSize, __('This Payment') );

	$YPos -= $LineHeight;
	/*draw a line */
	$pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);

	$YPos -= $LineHeight;
	$XPos = $Left_Margin;

}
