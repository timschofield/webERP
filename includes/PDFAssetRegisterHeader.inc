<?php
/* $Revision: 1.3 $ */
/* $Id$*/
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+20,$YPos-50,0,60);

if ($_POST['assetcategory']=='%') {
	$category=_('All');
} else {
	$categorysql='SELECT categorydescription FROM stockcategory WHERE categoryid="'.$_POST['assetcategory'].'"';
	$categoryresult=DB_query($categorysql, $db);
	$categoryrow=DB_fetch_array($categoryresult);
	$category=$categoryrow[0];
}

if ($_POST['assettype']=='%') {
	$type=_('All');
} else {
	$typesql='SELECT description FROM stockmaster WHERE stockid="'.$_POST['assettype'].'"';
	$typeresult=DB_query($typesql, $db);
	$typerow=DB_fetch_array($typeresult);
	$type=$typerow[0];
}

$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos,240,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*1),240,$FontSize, _('Asset Category ').' ' . $category );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*2),240,$FontSize, _('Asset Location ').' ' . $_POST['assetlocation'] );
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*3),240,$FontSize, _('Asset Type').': ' . $type);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*4),240,$FontSize, _('Acquired After').': ' . $_POST['fromDate']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*5),240,$FontSize, _('and Before').': ' . $_POST['toDate']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*7),240,$FontSize, _('Page'). ' ' . $PageNumber);

$YPos -= 60;

$YPos -=2*$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */
$YTopLeft=$YPos+$line_height;
$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$FontSize=8;
$Xpos = $Left_Margin+1;
$LeftOvers = $pdf->addTextWrap($Xpos+440,$YPos,300-$Left_Margin,$FontSize,  _('<--------------External Depreciation-------------->'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+605,$YPos,300-$Left_Margin,$FontSize,  _('<--------------Internal Depreciation-------------->'), 'centre');
$YPos -=(0.8*$line_height);
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,40,$FontSize,  _('Asset ID'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+40,$YPos,300-$Left_Margin,$FontSize,  _('Stock ID'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+80,$YPos,300-$Left_Margin,$FontSize,  _('Description'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+250,$YPos,300-$Left_Margin,$FontSize,  _('Serial No.'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+300,$YPos,300-$Left_Margin,$FontSize,  _('Location'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+380,$YPos,300-$Left_Margin,$FontSize,  _('Date Acquired'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+440,$YPos,55,$FontSize,  _('Cost'), 'right');
$LeftOvers = $pdf->addTextWrap($Xpos+495,$YPos,55,$FontSize,  _('Depn'), 'right');
$LeftOvers = $pdf->addTextWrap($Xpos+550,$YPos,50,$FontSize,  _('NBV'), 'right');
$LeftOvers = $pdf->addTextWrap($Xpos+600,$YPos,55,$FontSize,  _('Cost'), 'right');
$LeftOvers = $pdf->addTextWrap($Xpos+655,$YPos,55,$FontSize,  _('Depn'), 'right');
$LeftOvers = $pdf->addTextWrap($Xpos+710,$YPos,50,$FontSize,  _('NBV'), 'right');


$pdf->line($Left_Margin, $YTopLeft,$Page_Width-$Right_Margin, $YTopLeft);
$pdf->line($Left_Margin, $YTopLeft,$Left_Margin, $Bottom_Margin);
$pdf->line($Left_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $Bottom_Margin);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $YTopLeft);

$FontSize=8;
$YPos -= (1.5 * $line_height);

$PageNumber++;

?>