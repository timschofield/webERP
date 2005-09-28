<?php
/* $Revision: 1.5 $ */
/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber>1){
	$pdf->newPage();
}

$XPos = 55;
$YPos = 575;

$pdf->addText($XPos, $YPos,$FontSize, $myrow['deliverto']);
$pdf->addText($XPos, $YPos-13,$FontSize, $myrow['deladd1']);
$pdf->addText($XPos, $YPos-26,$FontSize, $myrow['deladd2']);
$pdf->addText($XPos, $YPos-39,$FontSize, $myrow['deladd3'] . ' ' . $myrow['deladd4'] . ' ' . $myrow['deladd5']);

$YPos = 510;

$pdf->addText($XPos, $YPos,$FontSize, $myrow['name']);
$pdf->addText($XPos, $YPos-13,$FontSize, $myrow['address1']);
$pdf->addText($XPos, $YPos-26,$FontSize, $myrow['address2']);
$pdf->addText($XPos, $YPos-39,$FontSize, $myrow['address3'] . ' ' . $myrow['address4'] . ' ' . $myrow['address5']);


/*Print Dispatch Date - as current date
$XPos=50;
$YPos=98;
$pdf->addText($XPos, $YPos,$FontSize, Date($_SESSION['DefaultDateFormat']));

*/


/*Print the freight company to be used */
$XPos=65;
$YPos=48;
$pdf->addText($XPos, $YPos,$FontSize, $myrow['shippername']);

$XPos=630;
$YPos=567;
$pdf->addText($XPos, $YPos,$FontSize, _('Order No') . ': ' . $_GET['TransNo']);
$pdf->addText($XPos, $YPos-14,$FontSize, _('Your Ref') . ': ' . $myrow['customerref']);


$XPos=687;
$YPos=539;
$pdf->addText($XPos, $YPos,$FontSize,  ConvertSQLDate($myrow['orddate']));

$XPos=630;
$YPos=525;
$pdf->addText($XPos, $YPos,$FontSize, _('Cust') . ': ' . $myrow['debtorno']);
$pdf->addText($XPos, $YPos-14,$FontSize, _('Branch') . ': ' . $myrow['branchcode']);

$pdf->addText($XPos, $YPos-32,$FontSize, _('Page') . ': ' . $PageNumber);

$pdf->addText($XPos, $YPos-46,$FontSize,  _('From') . ': ' . $myrow['locationname']);


/*Print the order number */
$XPos=510;
$YPos=96;
$pdf->addText($XPos, $YPos,$FontSize, $_GET['TransNo']);


$XPos=609;
$YPos=96;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,170,$FontSize,$myrow['comments']);

if (strlen($LeftOvers)>1){
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos-14,170,$FontSize,$LeftOvers);
	if (strlen($LeftOvers)>1){
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos-28,170,$FontSize,$LeftOvers);
		if (strlen($LeftOvers)>1){
			$LeftOvers = $pdf->addTextWrap($XPos,$YPos-42,170,$FontSize,$LeftOvers);
			if (strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($XPos,$YPos-56,170,$FontSize,$LeftOvers);
			}
		}
	}
}


$YPos = 414;

?>
