<?php
/* $Revision: 1.1 $ */
/*PDF page header for aged analysis reports */
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $CompanyName);

$YPos -=$line_height;

$FontSize =10;

$Heading = "Customers List for ";

if (in_array('All', $_POST['Areas'])){
	$Heading .= "All Territories ";
} else {
	if (count($_POST['Areas'])==1){
		$Heading .= "Territory " . $_POST['Areas'][0];
	} else {
		$Heading .= "Territories ";
		$NoOfAreas = count($_POST['Areas']);
		$i=1;
		foreach ($_POST['Areas'] as $Area){
			if ($i==$NoOfAreas){
				$Heading .= "and " . $Area . " ";
			} elseif ($i==($NoOfAreas-1)) {
				$Heading .= $Area . " ";
			} else {
				$Heading .= $Area . ", ";
			}
		}
	}
}

$Heading .= " and for ";
if (in_array('All', $_POST['SalesPeople'])){
	$Heading .= "All Salespeople";
} else {
	if (count($_POST['SalesPeople'])==1){
		$Heading .= "only " . $_POST['SalesPeople'][0];
	} else {
		$Heading .= "Sales-people ";
		$NoOfSalesfolk = count($_POST['SalesPeople']);
		$i=1;
		foreach ($_POST['SalesPeople'] as $Salesperson){
			if ($i==$NoOfSalesfolk){
				$Heading .= "and " . $Salesperson . " ";
			} elseif ($i==($NoOfSalesfolk-1)) {
				$Heading .= $Salesperson . " ";
			} else {
				$Heading .= $Salesperson . ", ";
			}
		}
	}
}

$pdf->selectFont('./fonts/Helvetica-Bold.afm');

$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

$pdf->selectFont('./fonts/Helvetica.afm');

$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, "Printed: " . Date("d M Y") . "   Page " . $PageNumber);

$YPos -=(3*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */

$LeftOvers = $pdf->addTextWrap(40,$YPos,40,$FontSize,"Act Code",'left');
$LeftOvers = $pdf->addTextWrap(80,$YPos,150,$FontSize,"Postal Address",'left');
$LeftOvers = $pdf->addTextWrap(230,$YPos,60,$FontSize,"Branch Code",'left');
$LeftOvers = $pdf->addTextWrap(290,$YPos,150,$FontSize,"Branch Contact Information",'left');
$LeftOvers = $pdf->addTextWrap(440,$YPos,150,$FontSize,"Branch Delivery Address",'left');

$YPos =$YPos - (2*$line_height);
?>