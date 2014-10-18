<?php
/* $Id: PDFRemittanceAdvice.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

If ((isset($_POST['PrintPDF']))
			AND isset($_POST['FromCriteria'])
			AND mb_strlen($_POST['FromCriteria'])>=1
			AND isset($_POST['ToCriteria'])
			AND mb_strlen($_POST['ToCriteria'])>=1)	{
	/*Now figure out the invoice less credits due for the Supplier range under review */

	$sql = "SELECT suppliers.supplierid,
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

	$SuppliersResult = DB_query($sql,$db);
	if (DB_num_rows($SuppliersResult)==0){
		//then there aint awt to print
		$Title = _('Print Remittance Advices Error');
		include('includes/header.inc');
		prnMsg(_('There were no remittance advices to print out for the supplier range and payment date specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' .  _('Back') . '</a>';
		include('includes/footer.inc');
		exit;
	}
/*then print the report */

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Remmitance Advice'));
	$pdf->addInfo('Subject',_('Remittance Advice') . ' - ' . _('suppliers from') . ' ' . $_POST['FromCriteria'] . ' ' . _('to') . ' ' . $_POST['ToCriteria'] . ' ' . _('and Paid On') . ' ' .  $_POST['PaymentDate']);

	$line_height=12;

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
		$sql = "SELECT systypes.typename,
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


		$TransResult = DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db) !=0) {
			$Title = _('Remittance Advice Problem Report');
			include('includes/header.inc');
			prnMsg(_('The details of the payment to the supplier could not be retrieved because') . ' - ' . DB_error_msg($db),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
				echo '<br />' . _('The SQL that failed was') . ' ' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}


		while ($DetailTrans = DB_fetch_array($TransResult)){

			$DisplayTranDate = ConvertSQLDate($DetailTrans['trandate']);

			$LeftOvers = $pdf->addTextWrap($Left_Margin+5, $YPos, 80,$FontSize,$DetailTrans['typename'], 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+95, $YPos, 80,$FontSize,$DisplayTranDate, 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+175, $YPos, 80,$FontSize,$DetailTrans['suppreference'], 'left');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+255, $YPos, 80,$FontSize,locale_number_format($DetailTrans['trantotal'],$SuppliersPaid['currdecimalplaces']), 'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+355, $YPos,80,$FontSize,locale_number_format($DetailTrans['amt'],$SuppliersPaid['currdecimalplaces']), 'right');
			$AccumBalance += $DetailTrans['amt'];

			$YPos -=$line_height;
			if ($YPos < $Bottom_Margin + $line_height){
				$PageNumber++;
				PageHeader();
			}
		} /*end while there are detail transactions to show */
		$YPos -= (0.5*$line_height);
    	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

	    $LeftOvers = $pdf->addTextWrap($Left_Margin+280,$YPos,75,$FontSize,_('Total Payment:'), 'right');

        $TotalPayments += $AccumBalance;

	    $LeftOvers = $pdf->addTextWrap($Left_Margin+355,$YPos,80,$FontSize,locale_number_format($AccumBalance,$SuppliersPaid['currdecimalplaces']), 'right');

	    $YPos -= (1.5*$line_height);
	    $pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

	} /* end while there are supplier payments to retrieve allocations for */


	$FileName=$_SESSION['DatabaseName']. '_' . _('Remittance_Advices') . '_' . date('Y-m-d').'.pdf';
	$pdf->OutputD($FileName);
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title=_('Remittance Advices');
	include('includes/header.inc');

    echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' '
        . $Title . '</p>';
	/* show form to allow input	*/

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table>';

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
	echo '<tr>
			<td>' . _('From Supplier Code') . ':</td>
			<td><input type="text" maxlength="6" size="7" name="FromCriteria" value="' . $DefaultFromCriteria . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('To Supplier Code') . ':</td>
			<td><input type="text" maxlength="6" size="7" name="ToCriteria" value="' . $DefaultToCriteria . '" /></td>
		</tr>';

	if (!isset($_POST['PaymentDate'])){
		$DefaultDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')+1,0 ,Date('y')));
	} else {
		$DefaultDate = $_POST['PaymentDate'];
	}

	echo '<tr>
			<td>' . _('Date Of Payment') . ':</td>
			<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="PaymentDate" maxlength="11" size="12" value="' . $DefaultDate . '" /></td>
		</tr>';

	echo '</table>
        <br />
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>';

    echo '</div>
          </form>';

	include ('includes/footer.inc');
} /*end of else not PrintPDF */

function PageHeader(){
	global $pdf;
	global $PageNumber;
	global $YPos;
	global $Xpos;
	global $line_height;
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
	$pdf->addText($XPos, $YPos,$FontSize, _('Remittance Advice') );

	$FontSize = 10;
	$pdf->addText($XPos + 150, $YPos,$FontSize, ' '. _('printed:').' ' . Date($_SESSION['DefaultDateFormat']));

	$pdf->addText($XPos + 280, $YPos,$FontSize, _('Page').': ' . $PageNumber);

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
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone']);
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
	$LineCount += 1;
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);


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
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Our Code:') . ' ' .$SuppliersPaid['supplierid']);

	$YPos = $Page_Height - $Top_Margin - 120;

	$FontSize=8;
	$XPos = $Page_Width/2 - 60;
	$pdf->addText($XPos, $YPos,$FontSize, _('All amounts stated in') . ' - ' . $SuppliersPaid['currcode']);
	$YPos -= $line_height;
	$pdf->addText($XPos, $YPos,$FontSize, $SuppliersPaid['terms']);

	$YPos = $Page_Height - $Top_Margin - 180;
	//$YPos -= $line_height;
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

	$YPos -= $line_height;
	$FontSize =10;
	/*Set up headings */
	$pdf->addText($Left_Margin+10, $YPos,$FontSize, _('Trans Type') );
	$pdf->addText($Left_Margin+100, $YPos,$FontSize, _('Date') );
	$pdf->addText($Left_Margin+180, $YPos,$FontSize, _('Reference') );
	$pdf->addText($Left_Margin+310, $YPos,$FontSize, _('Total') );
	$pdf->addText($Left_Margin+390, $YPos,$FontSize, _('This Payment') );

	$YPos -= $line_height;
	/*draw a line */
	$pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);

	$YPos -= $line_height;
	$XPos = $Left_Margin;

}
?>