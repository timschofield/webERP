<?php
/* $Revision: 1.2 $ */

/*this class is an extension to the fpdf class using a syntax that the original reports were written in
(the R &OS pdf.php class) - due to limitation of this class for foreign character support this wrapper class
was written to allow the same code base to use the more functional fpdf.class by Olivier Plathey */


include ('includes/class.pdf.php');


if (!isset($PaperSize)){
	$PaperSize = $_SESSION['DefaultPageSize'];
}

switch ($PaperSize) {

  case 'A4':

      $Page_Width=595;
      $Page_Height=842;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=40;
      $Right_Margin=30;
      break;

  case 'A4_Landscape':

      $Page_Width=842;
      $Page_Height=595;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=40;
      $Right_Margin=30;
      break;

   case 'A3':

      $Page_Width=842;
      $Page_Height=1190;
      $Top_Margin=50;
      $Bottom_Margin=50;
      $Left_Margin=50;
      $Right_Margin=40;
      break;

   case 'A3_landscape':

      $Page_Width=1190;
      $Page_Height=842;
      $Top_Margin=50;
      $Bottom_Margin=50;
      $Left_Margin=50;
      $Right_Margin=40;
      break;

   case 'letter':

      $Page_Width=612;
      $Page_Height=792;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=30;
      $Right_Margin=25;
      break;

   case 'letter_landscape':

      $Page_Width=792;
      $Page_Height=612;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=30;
      $Right_Margin=25;
      break;

   case 'legal':

      $Page_Width=612;
      $Page_Height=1008;
      $Top_Margin=50;
      $Bottom_Margin=40;
      $Left_Margin=30;
      $Right_Margin=25;
      break;

   case 'legal_landscape':

      $Page_Width=1008;
      $Page_Height=612;
      $Top_Margin=50;
      $Bottom_Margin=40;
      $Left_Margin=30;
      $Right_Margin=25;
      break;
}

$PageSize = array(0,0,$Page_Width,$Page_Height);
$pdf = & new Cpdf($PageSize);

$pdf->addinfo('Author','webERP ' . $Version);
$pdf->addinfo('Creator','webERP http://www.weberp.org');

/*depending on the language this font is modified see includes/class.pdf.php
	selectFont method interprets the text helvetica to be:
	for Chinese - BIg5
	for Japanese - SJIS
	for Korean - UHC
*/
$pdf->selectFont('helvetica');
?>