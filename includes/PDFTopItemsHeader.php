<?php

/*PDF page header for Top Items report */

if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
/// @todo use the same logo-scanning logic used in other places, and support non-jpeg files
$pdf->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/logo.jpg', $XPos+20, $YPos-50, 0, 60);

if ($_GET['Customers']!='All'){
	$SQL="SELECT typename
		  FROM `debtortype`
		  WHERE typeid='".$_GET['Customers']."'";

	$Result = DB_query($SQL);
	$MyRow=DB_fetch_array($Result);
	$Customers=$MyRow["0"];
}else{
	$Customers='All';
}

//Display the searching condition
$pdf->addTextWrap($XPos+40,$YPos-70,500,9,__('Search On Location').' : '.$_GET['Location']);
$pdf->addTextWrap($XPos+240,$YPos-70,500,9,__('Customers'). ' : '.$Customers);
$pdf->addTextWrap($XPos+40,$YPos-90,500,9,__('Number Of Days')." : ".$_GET['NumberOfDays']." ");
$pdf->addTextWrap($XPos+240,$YPos-90,500,9,__('Number Of Items')." : ".$_GET['NumberOfTopItems']);
$pdf->addTextWrap($XPos+40,$YPos-110,500,9,__('Order By')." : ".$_GET['Sequence']);

$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos-($LineHeight*1.5),550,$FontSize, __('Top Items Sales Search Result') );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos-($LineHeight*3),140,$FontSize, __('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. __('Page'). ' ' . $PageNumber);

$YPos -= 100;

$YPos -=$LineHeight;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$LineHeight);

/*Draw a rectangle to put the headings in     */
$pdf->Rectangle($Left_Margin, $YPos+$LineHeight,$Page_Width-$Left_Margin-$Right_Margin,$LineHeight*2);

/*set up the headings */
$Xpos = $Left_Margin+1;

$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,  __('Code'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+100,$YPos,300-$Left_Margin,$FontSize,  __('Description'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+320,$YPos,300-$Left_Margin,$FontSize,  __('Total Inv'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+370,$YPos,300-$Left_Margin,$FontSize,  __('Unit'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+410,$YPos,300-$Left_Margin,$FontSize,  __('Value Sales'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+480,$YPos,300-$Left_Margin,$FontSize,  __('On Hand'), 'centre');

$FontSize=8;
$pdf->Rectangle($Left_Margin, $YPos-$LineHeight,$Page_Width-$Left_Margin-$Right_Margin,$YPos-$Bottom_Margin);
$YPos -= (1.5 * $LineHeight);
