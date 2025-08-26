<?php

if ($PageNumber>1){
	$pdf->newPage();
}

$XPos = 55;
$YPos = 575;

$pdf->addText($XPos, $YPos,$FontSize, $MyRow['deliverto']);
$pdf->addText($XPos, $YPos-13,$FontSize, $MyRow['deladd1']);
$pdf->addText($XPos, $YPos-26,$FontSize, $MyRow['deladd2']);
$pdf->addText($XPos, $YPos-39,$FontSize, $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5'] . ' ' . $MyRow['deladd6']);

$YPos = 510;

$pdf->addText($XPos, $YPos,$FontSize, $MyRow['name']);
$pdf->addText($XPos, $YPos-13,$FontSize, $MyRow['address1']);
$pdf->addText($XPos, $YPos-26,$FontSize, $MyRow['address2']);
$pdf->addText($XPos, $YPos-39,$FontSize, $MyRow['address3'] . ' ' . $MyRow['address4'] . ' ' . $MyRow['address5']. ' ' . $MyRow['deladd6']);

/*Print Dispatch Date - as current date
$XPos=50;
$YPos=98;
$pdf->addText($XPos, $YPos,$FontSize, Date($_SESSION['DefaultDateFormat']));
*/

/*Print the freight company to be used */
$XPos=65;
$YPos=48;
$pdf->addText($XPos, $YPos,$FontSize, $MyRow['shippername']);

$XPos=630;
$YPos=567;
$pdf->addText($XPos, $YPos,$FontSize, __('Order No') . ': ' . $_GET['TransNo']);
$pdf->addText($XPos, $YPos-14,$FontSize, __('Your Ref') . ': ' . $MyRow['customerref']);

$XPos=687;
$YPos=539;
$pdf->addText($XPos, $YPos,$FontSize,  ConvertSQLDate($MyRow['orddate']));

$XPos=630;
$YPos=525;
$pdf->addText($XPos, $YPos,$FontSize, __('Cust') . ': ' . $MyRow['debtorno']);
$pdf->addText($XPos, $YPos-14,$FontSize, __('Branch') . ': ' . $MyRow['branchcode']);

$pdf->addText($XPos, $YPos-32,$FontSize, __('Page') . ': ' . $PageNumber);

$pdf->addText($XPos, $YPos-46,$FontSize,  __('From') . ': ' . $MyRow['locationname']);


/*Print the order number */
$XPos=510;
$YPos=96;
$pdf->addText($XPos, $YPos,$FontSize, $_GET['TransNo']);


$XPos=609;
$YPos=96;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,170,$FontSize,stripcslashes($MyRow['comments']));

if (mb_strlen($LeftOvers)>1){
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos-14,170,$FontSize,$LeftOvers);
	if (mb_strlen($LeftOvers)>1){
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos-28,170,$FontSize,$LeftOvers);
		if (mb_strlen($LeftOvers)>1){
			$LeftOvers = $pdf->addTextWrap($XPos,$YPos-42,170,$FontSize,$LeftOvers);
			if (mb_strlen($LeftOvers)>1){
				$LeftOvers = $pdf->addTextWrap($XPos,$YPos-56,170,$FontSize,$LeftOvers);
			}
		}
	}
}

$YPos = 414;
