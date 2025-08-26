<?php

/*
 * PDF page header for the Pc Tab report.
 */

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize = 8;
$YPos = $Page_Height - $Top_Margin;
$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, __('Printed'). ': ' . Date($_SESSION['DefaultDateFormat'])  . '   ' . __('Page'). ' ' . $PageNumber);
