<?php

/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber>1){
	$pdf->newPage();
}

$XPos = 55;
$YPos = 575;

$pdf->addText($XPos, $YPos,$FontSize, $myrow["DeliverTo"]);
$pdf->addText($XPos, $YPos-13,$FontSize, $myrow["DelAdd1"]);
$pdf->addText($XPos, $YPos-26,$FontSize, $myrow["DelAdd2"]);
$pdf->addText($XPos, $YPos-39,$FontSize, $myrow["DelAdd3"] . " " . $myrow["DelAdd4"]);

$YPos = 510;

$pdf->addText($XPos, $YPos,$FontSize, $myrow["Name"]);
$pdf->addText($XPos, $YPos-13,$FontSize, $myrow["Address1"]);
$pdf->addText($XPos, $YPos-26,$FontSize, $myrow["Address2"]);
$pdf->addText($XPos, $YPos-39,$FontSize, $myrow["Address3"]);
$pdf->addText($XPos, $YPos-52,$FontSize, $myrow["Address4"]);


/*Print Dispatch Date - as current date
$XPos=50;
$YPos=98;
$pdf->addText($XPos, $YPos,$FontSize, Date($DefaultDateFormat));

*/


/*Print the freight company to be used */
$XPos=65;
$YPos=48;
$pdf->addText($XPos, $YPos,$FontSize, $myrow["ShipperName"]);

$XPos=630;
$YPos=567;
$pdf->addText($XPos, $YPos,$FontSize, "Order No: " . $_GET['TransNo']);
$pdf->addText($XPos, $YPos-14,$FontSize, "Your Ref: " . $myrow["CustomerRef"]);


$XPos=687;
$YPos=539;
$pdf->addText($XPos, $YPos,$FontSize,  ConvertSQLDate($myrow["OrdDate"]));

$XPos=630;
$YPos=525;
$pdf->addText($XPos, $YPos,$FontSize, "Cust: " . $myrow["DebtorNo"]);
$pdf->addText($XPos, $YPos-14,$FontSize, "Branch: " . $myrow["BranchCode"]);

$pdf->addText($XPos, $YPos-32,$FontSize, "Page: " . $PageNumber);

$pdf->addText($XPos, $YPos-46,$FontSize,  "From: " . $myrow['LocationName']);


/*Print the order number */
$XPos=510;
$YPos=96;
$pdf->addText($XPos, $YPos,$FontSize, $_GET['TransNo']);


$XPos=609;
$YPos=96;
$LeftOvers = $pdf->addTextWrap($XPos,$YPos,170,$FontSize,$myrow["Comments"]);

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
