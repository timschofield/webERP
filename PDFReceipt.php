<?php

require(__DIR__ . '/includes/session.php');

include('includes/PDFStarter.php');

$FontSize=10;
$pdf->addInfo('Title', __('Sales Receipt') );

$PageNumber=1;
$LineHeight=12;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;

/* Prints company logo */
$pdf->addJpegFromFile($_SESSION['LogoFile'], $XPos+20, $YPos-50, 0, 60);

/* Prints company info */
$pdf->addTextWrap(50,$YPos-($LineHeight*6),300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$pdf->addTextWrap(50,$YPos-($LineHeight*7),300,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
$pdf->addTextWrap(50,$YPos-($LineHeight*8),300,$FontSize,$_SESSION['CompanyRecord']['regoffice2']);
$pdf->addTextWrap(50,$YPos-($LineHeight*9),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$pdf->addTextWrap(50,$YPos-($LineHeight*10),300,$FontSize,$_SESSION['CompanyRecord']['regoffice4']);
$pdf->addTextWrap(50,$YPos-($LineHeight*11),300,$FontSize,$_SESSION['CompanyRecord']['regoffice5']);
$pdf->addTextWrap(50,$YPos-($LineHeight*12),300,$FontSize,$_SESSION['CompanyRecord']['regoffice6']);

$pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($LineHeight*3),550,$FontSize, __('Customer Receipt Number ').'  : ' . $_GET['BatchNumber'] .'/'.$_GET['ReceiptNumber'] );
$pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($LineHeight*4.5),140,$FontSize, __('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. __('Page'). ' ' . $PageNumber);

$YPos -= 150;

$YPos -=$LineHeight;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);

$FontSize=10;
$YPos -= (1.5 * $LineHeight);

$PageNumber++;

$SQL="SELECT MIN(id) as start FROM debtortrans WHERE type=12 AND transno='". $_GET['BatchNumber']. "'";
$Result = DB_query($SQL);
$MyRow=DB_fetch_array($Result);
$StartReceiptNumber=$MyRow['start'];

$SQL="SELECT debtorno,
			ovamount,
			invtext
		FROM debtortrans
		WHERE type=12
		AND transno='" . $_GET['BatchNumber'] . "'
		AND id='". ($StartReceiptNumber-1+$_GET['ReceiptNumber']) ."'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$DebtorNo = $MyRow['debtorno'];
$Amount = $MyRow['ovamount'];
$Narrative = $MyRow['invtext'];

$SQL = "SELECT 	currabrev,
				decimalplaces
			FROM currencies
			WHERE currabrev=(SELECT currcode
				FROM banktrans
				WHERE type=12
				AND transno='" . $_GET['BatchNumber']."')";
$Result = DB_query($SQL);
$MyRow=DB_fetch_array($Result);
$CurrencyCode=$MyRow['currabrev'];
$DecimalPlaces=$MyRow['decimalplaces'];

$SQL="SELECT name,
             address1,
			 address2,
			 address3,
			 address4,
			 address5,
			 address6
		FROM debtorsmaster
		WHERE debtorno='".$DebtorNo."'";

$Result = DB_query($SQL);
$MyRow=DB_fetch_array($Result);

/* Prints customer info */
$pdf->addTextWrap(50,$YPos,300,$FontSize,__('Received From').' :');
$pdf->addTextWrap(150,$YPos,300,$FontSize, htmlspecialchars_decode($MyRow['name']));
$pdf->addTextWrap(150,$YPos-($LineHeight*1),300,$FontSize, htmlspecialchars_decode($MyRow['address1']));
$pdf->addTextWrap(150,$YPos-($LineHeight*2),300,$FontSize, htmlspecialchars_decode($MyRow['address2']));
$pdf->addTextWrap(150,$YPos-($LineHeight*3),300,$FontSize, htmlspecialchars_decode($MyRow['address3']));
$pdf->addTextWrap(150,$YPos-($LineHeight*4),300,$FontSize, htmlspecialchars_decode($MyRow['address4']));
$pdf->addTextWrap(150,$YPos-($LineHeight*5),300,$FontSize, htmlspecialchars_decode($MyRow['address5']));
$pdf->addTextWrap(150,$YPos-($LineHeight*6),300,$FontSize, htmlspecialchars_decode($MyRow['address6']));

$YPos=$YPos-($LineHeight*8);

$pdf->addTextWrap(50,$YPos,300,$FontSize, __('The Sum Of').' :');
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
$pdf->addTextWrap(150,$YPos,300,$FontSize, locale_number_format(-$Amount,$DecimalPlaces).' '. $CurrencyCode . '-' . $CurrencyName[$CurrencyCode]);

$YPos=$YPos-($LineHeight*2);

$pdf->addTextWrap(50,$YPos,500,$FontSize, __('Details').' :');
$pdf->addTextWrap(150,$YPos,500,$FontSize, $Narrative);

$YPos=$YPos-($LineHeight*8);

$pdf->addTextWrap(50,$YPos,500,$FontSize,__('Signed On Behalf Of').' :     '.$_SESSION['CompanyRecord']['coyname']);

$YPos=$YPos-($LineHeight*10);

$pdf->addTextWrap(50,$YPos,300,$FontSize,'______________________________________________________________________________');

$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
