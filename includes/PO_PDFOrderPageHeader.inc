<?php

/*
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - ($line_height*2);


$pdf->addJpegFromFile('logo.jpg',$Left_Margin,$YPos,90,60);

$FontSize=15;

$XPos = $Page_Width/2 - 30;

$pdf->addText($XPos,$YPos+$line_height,$FontSize, "Purchase Order Number " . $OrderNo);

if ($ViewingOnly!=0) {
	$FontSize=16;
	$pdf->addText($XPos,$YPos,$FontSize, "IMPORTANT : FOR VIEWING ONLY");
	$pdf->addText($XPos,$YPos-$line_height,$FontSize, "DO NOT SEND TO SUPPLIER");
}

$Ypos -= ($line_height*3);
$FontSize=10;
$XPos = $Page_Width-$Right_Margin-50;
$pdf->addText($XPos,$YPos +40, $FontSize, "Page: " . $PageNumber);


/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -=2*$line_height;
$pdf->addText($Left_Margin,$YPos, $FontSize, "A.B.N. " . $CompanyRecord["GSTNo"]);
$pdf->addText($Left_Margin,$YPos-$line_height, $FontSize, $CompanyName);
$pdf->addText($Left_Margin,$YPos-(2*$line_height), $FontSize, $CompanyRecord["PostalAddress"]);
$pdf->addText($Left_Margin,$YPos-(3*$line_height), $FontSize, $CompanyRecord["RegOffice1"] . " " . $CompanyRecord["RegOffice2"]);
$pdf->addText($Left_Margin,$YPos-(4*$line_height), $FontSize, "Ph:" . $CompanyRecord["Telephone"] . " Fax: " . $CompanyRecord["Fax"]);
$pdf->addText($Left_Margin,$YPos-(5*$line_height), $FontSize, "Email: " . $CompanyRecord["Email"]);


/*Now the delivery details */
$YPos -= 8;
$XPos = $Page_Width/2 + 25;
$FontSize=12;
$pdf->addText($XPos+2,$YPos, $FontSize, "Deliver To:");
$pdf->addText($XPos+2,$YPos-$line_height, $FontSize, $POHeader["DelAdd1"]);
$pdf->addText($XPos+2,$YPos-(2*$line_height), $FontSize, $POHeader["DelAdd2"]);
$pdf->addText($XPos+2,$YPos-(3*$line_height), $FontSize, $POHeader["DelAdd3"]);
$pdf->addText($XPos+2,$YPos-(4*$line_height), $FontSize, $POHeader["DelAdd4"]);


/*draw a nice curved corner box around the delivery to address */
/*from the top right */
$pdf->partEllipse($XPos+250-10,$YPos+10,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+250-10, $YPos+20,$XPos+10, $YPos+20);
/*Do top left corner */
$pdf->partEllipse($XPos+10, $YPos+10,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos+10, $YPos-70-10,$XPos+250-10, $YPos-70-10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos+10, $YPos-70,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-70,$XPos, $YPos+10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+250-10, $YPos-70,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+250, $YPos-70,$XPos+250, $YPos+10);



/*end of the small table showing the coy name and delivery address details */

$XPos = $Left_Margin;
$YPos -= ($line_height*4.5);         /* 4 lines down */

/*
PDF_moveto($p, $XPos+230, $YPos-100);
PDF_arc($p, $XPos+220, $YPos, 10, 0, 90);
PDF_arc($p, $XPos+5, $YPos, 10, 90, 180);
PDF_arc($p, $XPos+5, $YPos-100, 10, 180, 270);
PDF_arc($p, $XPos+220, $YPos-100, 10, 270, 360);
PDF_stroke($p);



$font=PDF_findfont($p,"Helvetica-Bold","host",0);
PDF_setfont($p, $font, 12.0);

*/
$YPos -= 20;
$pdf->addText($XPos+1,$YPos, $FontSize, "To: ");

$YPos-=$line_height;
$pdf->addText($XPos+1,$YPos, $FontSize, $POHeader["SuppName"]);
$pdf->addText($XPos+1,$YPos-$line_height, $FontSize, $POHeader["Address1"]);
$pdf->addText($XPos+1,$YPos-(2*$line_height), $FontSize, $POHeader["Address2"]);
$pdf->addText($XPos+1,$YPos-(3*$line_height), $FontSize, $POHeader["Address3"]);
$pdf->addText($XPos+1,$YPos-(4*$line_height), $FontSize, $POHeader["Address4"]);


$XPos = $Page_Width/2 + 25;
$pdf->addText($XPos,$YPos, $FontSize, "Order Date:");
$pdf->addText($XPos+70,$YPos, $FontSize, ConvertSQLDate($POHeader["OrdDate"]));


$YPos -= $line_height;
$pdf->addText($XPos,$YPos, $FontSize, "Initator: ");
$pdf->addText($XPos+70,$YPos, $FontSize, $POHeader["Initiator"]);

$YPos -= $line_height;
$pdf->addText($XPos,$YPos, $FontSize, "Comments:");
$YPos -= $line_height;
$LeftOvers = $pdf->addTextWrap($XPos, $YPos,250,$FontSize,$POHeader["Comments"], 'left');
if (strlen($LeftOvers)>0){
	$LeftOvers = $pdf->addTextWrap($XPos, $YPos-$line_height,250,$FontSize,$leftOvers, 'left');
}

$XPos = $Left_Margin;
$YPos -= ($line_height*2);
$pdf->addText($Left_Margin,$YPos, $FontSize, "All amounts stated in - " . $POHeader["CurrCode"]);


$YPos -= ($line_height*.5);
/*draw a square grid for entering line items */
$pdf->line($Left_Margin, $YPos, $Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos, $Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin, $Left_Margin, $Bottom_Margin);
$pdf->line($Left_Margin, $Bottom_Margin, $Left_Margin, $YPos);

$YPos -= $line_height;
/*Set up headings */
$FontSize=10;
$pdf->addText($Left_Margin+1,$YPos, $FontSize, "Code");
$pdf->addText($Left_Margin+65,$YPos, $FontSize, "Item Description");
$pdf->addText($Left_Margin+400,$YPos, $FontSize, "Quantity");
$pdf->addText($Left_Margin+450,$YPos, $FontSize, "Unit");
$pdf->addText($Left_Margin+485,$YPos, $FontSize, "Date Req'd");
$pdf->addText($Left_Margin+600,$YPos, $FontSize, "Price");
$pdf->addText($Left_Margin+680,$YPos, $FontSize, "Total");

$YPos-=$line_height;

/*draw a line */
$pdf->line($Left_Margin, $YPos, $Page_Width-$Right_Margin, $YPos);

?>
