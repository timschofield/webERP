<?php
/* $Revision: 1.2 $ */
/* R&OS PHP-pdf class code to head up a new page */

$YPos = $Page_Height - $Top_Margin;

$pdf->addJpegFromFile('logo.jpg',$Page_Width/2 -80,$YPos-40,125,65);
$FontSize =15;
if ($InvOrCredit=="Invoice") {

        $pdf->addText($Page_Width - 200, $YPos, $FontSize, "TAX INVOICE ");
} else {
	$pdf->addText($Page_Width - 200, $YPos, $FontSize, "CREDIT NOTE ");
}

$XPos = $Page_Width - 265;
$YPos -= 111;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+225,$YPos+93,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+103,$XPos, $YPos+103);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+93,$XPos-10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+93);

$YPos = $Page_Height - $Top_Margin - 10;

$FontSize = 10;

$pdf->addText($Page_Width-268, $YPos-13, $FontSize, "Number");
$pdf->addText($Page_Width-180, $YPos-13, $FontSize, $FromTransNo);
$pdf->addText($Page_Width-268, $YPos-26, $FontSize, "CustomerCode");
$pdf->addText($Page_Width-180, $YPos-26, $FontSize, $myrow["DebtorNo"] . " Branch " . $myrow["BranchCode"]);
$pdf->addText($Page_Width-268, $YPos-39, $FontSize, "Date");
$pdf->addText($Page_Width-180, $YPos-39, $FontSize, ConvertSQLDate($myrow["TranDate"]));


if ($InvOrCredit=="Invoice") {

	$pdf->addText($Page_Width-268, $YPos-52, $FontSize, "Order No");
	$pdf->addText($Page_Width-180, $YPos-52, $FontSize, $myrow["OrderNo"]);
	$pdf->addText($Page_Width-268, $YPos-65, $FontSize, "Order Date");
	$pdf->addText($Page_Width-180, $YPos-65, $FontSize, ConvertSQLDate($myrow["OrdDate"]));
	$pdf->addText($Page_Width-268, $YPos-78, $FontSize, "Dispatch Method");
	$pdf->addText($Page_Width-180, $YPos-78, $FontSize, $myrow["ShipperName"]);
	$pdf->addText($Page_Width-268, $YPos-91, $FontSize, "Dispatched From"); 
	$pdf->addText($Page_Width-180, $YPos-91, $FontSize, $myrow["LocationName"]); 
}


$pdf->addText($Page_Width-268, $YPos-104, $FontSize, "Page");
$pdf->addText($Page_Width-180, $YPos-104, $FontSize, $PageNumber);

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
$XPos = $Page_Width/2 -90;
$YPos = $Page_Height - $Top_Margin-60;
$pdf->addText($XPos, $YPos, $FontSize, $CompanyName);

$FontSize=8;
$pdf->addText($XPos, $YPos-10, $FontSize, "Tax Authority No: " . $CompanyRecord["GSTNo"]);
$pdf->addText($XPos, $YPos-20, $FontSize, $CompanyRecord["PostalAddress"]);
$pdf->addText($XPos, $YPos-30, $FontSize, $CompanyRecord["RegOffice1"] . " " . $CompanyRecord["RegOffice2"]);
$pdf->addText($XPos, $YPos-40, $FontSize, $CompanyRecord["RegOffice3"]);
$pdf->addText($XPos, $YPos-50, $FontSize, "Phone:" . $CompanyRecord["Telephone"] . " Fax: " . $CompanyRecord["Fax"]);
$pdf->addText($XPos, $YPos-60, $FontSize, "Email: " . $CompanyRecord["Email"]);

/*Now the customer charged to details top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;

$FontSize=10;

$pdf->addText($XPos, $YPos, $FontSize, "Sold To:");
$XPos +=80;

if ($myrow["InvAddrBranch"]==0){
	$pdf->addText($XPos, $YPos, $FontSize, $myrow["Name"]);
	$pdf->addText($XPos, $YPos-14, $FontSize, $myrow["Address1"]);
	$pdf->addText($XPos, $YPos-28, $FontSize, $myrow["Address2"]);
	$pdf->addText($XPos, $YPos-42, $FontSize, $myrow["Address3"] . " " . $myrow["Address4"]);
} else {
	$pdf->addText($XPos, $YPos, $FontSize, $myrow["Name"]);
	$pdf->addText($XPos, $YPos-14, $FontSize, $myrow["BrPostAddr1"]);
	$pdf->addText($XPos, $YPos-28, $FontSize, $myrow["BrPostAddr2"]);
	$pdf->addText($XPos, $YPos-42, $FontSize, $myrow["BrPostAddr3"] . " " . $myrow["BrPostAddr4"]);
}


$XPos -=80;
$YPos -=($line_height*4);

if ($InvOrCredit=="Invoice") {

	$pdf->addText($XPos, $YPos, $FontSize, "Delivered To:");
	$XPos +=80;
	$pdf->addText($XPos, $YPos, $FontSize, $myrow["DeliverTo"]);
	$pdf->addText($XPos, $YPos-14, $FontSize, $myrow["DelAdd1"]);
	$pdf->addText($XPos, $YPos-28, $FontSize, $myrow["DelAdd2"]);
	$pdf->addText($XPos, $YPos-42, $FontSize, $myrow["DelAdd3"] . " " . $myrow["DelAdd4"]);
	$XPos -=80;
}
if ($InvOrCredit=="Credit"){
/* then its a credit note */

	$pdf->addText($XPos, $YPos, $FontSize, "Charge Branch:");
	$XPos +=80;
	$pdf->addText($XPos, $YPos, $FontSize, $myrow["BrName"]);
	$pdf->addText($XPos, $YPos-14, $FontSize, $myrow["BrAddress1"]);
	$pdf->addText($XPos, $YPos-28, $FontSize, $myrow["BrAddress2"]);
	$pdf->addText($XPos, $YPos-42, $FontSize, $myrow["BrAddress3"] . " " . $myrow["BrAddress4"]);
	$XPos -=80;
}

$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin - 80;
/*draw a line under the company address and charge to address
$pdf->line($XPos, $YPos,$Right_Margin, $YPos); */

$XPos = $Page_Width/2;

$XPos = $Left_Margin;
$YPos -= ($line_height*2);

$pdf->addText($Left_Margin, $YPos-8, $FontSize, "All amounts stated in - " . $myrow["CurrCode"]);

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+400,0,90,10,10);
/*line to the top left */
$pdf->line($Page_Width-$Right_Margin-10, $Bottom_Margin+410,$Left_Margin+10, $Bottom_Margin+410);
/*Dow top left corner */
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+400,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $Bottom_Margin+400,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+400);


$YPos -= ($line_height*2);
/*Set up headings */
$FontSize=10;
$pdf->addText($Left_Margin + 2, $YPos, $FontSize, "Customer ABN:");
/* Need a field to enter the ABN into to print out here */

/*Print a vertical line */
$pdf->line($Left_Margin+248, $YPos+$line_height+3,$Left_Margin+248, $YPos - 8);

$pdf->addText($Left_Margin + 252, $YPos, $FontSize, "Your Order No:");
$pdf->addText($Left_Margin+330, $YPos, $FontSize, $myrow["CustomerRef"]);

/*Print a vertical line */
$pdf->line($Left_Margin+450, $YPos+$line_height+3,$Left_Margin+450,$YPos-8);

$pdf->addText($Left_Margin+453, $YPos, $FontSize, "Sales Person:");
$pdf->addText($Left_Margin+530, $YPos, $FontSize, $myrow["SalesmanName"]);

$YPos -= 8;
/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= 12;

$TopOfColHeadings = $YPos;

$pdf->addText($Left_Margin+5, $YPos, $FontSize, "Item Code");
$pdf->addText($Left_Margin+100, $YPos, $FontSize, "Description");
$pdf->addText($Left_Margin+382, $YPos, $FontSize, "Unit Price");
$pdf->addText($Left_Margin+485, $YPos, $FontSize, "Quantity");
$pdf->addText($Left_Margin+555, $YPos, $FontSize, "UOM");
$pdf->addText($Left_Margin+595, $YPos, $FontSize, "Discount");
$pdf->addText($Left_Margin+690, $YPos, $FontSize, "Extended Price");

$YPos-=8;

/*draw a line */
$pdf->line($XPos, $YPos,$Page_Width-$Right_Margin, $YPos);

$YPos -= ($line_height);

?>
