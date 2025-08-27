<?php

/*PDF page header for aged analysis reports */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -=$LineHeight;

$FontSize =10;

$Heading = __('Customers List for'). ' ';

if (in_array('All', $_POST['Areas'])){
	$Heading .= __('All Territories'). ' ';
} else {
	if (count($_POST['Areas'])==1){
		$Heading .= __('Territory') . ' ' . $_POST['Areas'][0];
	} else {
		$Heading .= __('Territories'). ' ';
		$NoOfAreas = count($_POST['Areas']);
		$i=1;
		foreach ($_POST['Areas'] as $Area){
			if ($i==$NoOfAreas){
				$Heading .= __('and') . ' ' . $Area . ' ';
			} elseif ($i==($NoOfAreas-1)) {
				$Heading .= $Area . ' ';
			} else {
				$Heading .= $Area . ', ';
			}
		}
	}
}

$Heading .= ' '. __('and for').' ';
if (in_array('All', $_POST['SalesPeople'])){
	$Heading .= __('All Salespeople');
} else {
	if (count($_POST['SalesPeople'])==1){
		$Heading .= __('only') .' ' . $_POST['SalesPeople'][0];
	} else {
		$Heading .= __('Salespeople') .' ';
		$NoOfSalesfolk = count($_POST['SalesPeople']);
		$i=1;
		foreach ($_POST['SalesPeople'] as $Salesperson){
			if ($i==$NoOfSalesfolk){
				$Heading .= __('and') . ' ' . $Salesperson . " ";
			} elseif ($i==($NoOfSalesfolk-1)) {
				$Heading .= $Salesperson . " ";
			} else {
				$Heading .= $Salesperson . ", ";
			}
		}
	}
}

$pdf->setFont('','B');

$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

$pdf->setFont('','');

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, __('Printed'). ': ' . Date($_SESSION['DefaultDateFormat']) . '   '. __('Page'). ' ' . $PageNumber);

$YPos -=(3*$LineHeight);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Left_Margin, $YPos+$LineHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos-5);

/*set up the headings */

$LeftOvers = $pdf->addTextWrap(40,$YPos,40,$FontSize, __('Act Code'),'left');
$LeftOvers = $pdf->addTextWrap(80,$YPos,150,$FontSize, __('Postal Address'),'left');
$LeftOvers = $pdf->addTextWrap(230,$YPos,60,$FontSize,__('Branch Code'),'left');
$LeftOvers = $pdf->addTextWrap(290,$YPos,150,$FontSize,__('Branch Contact Information'),'left');
$LeftOvers = $pdf->addTextWrap(440,$YPos,150,$FontSize,__('Branch Delivery Address'),'left');

$YPos =$YPos - (2*$LineHeight);
