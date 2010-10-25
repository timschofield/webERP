<?php

/* $Id: PDFStarter.php 3526 2010-06-26 08:05:36Z tim_schofield $ */

/*	-------------------------------------------------------------------------------------
	November 2009. Moving from FPDF to TCPDF.
 	This file is included by most of the scripts (47 from 54 at now) that creates a pdf.
	This file creates a new instance of the PDF object defined in class.pdf.php
	The changes applied to the PDF class affected this file that needed some changes too.
	Javier de Lorenzo-Cáceres <info@civicom.eu>
	------------------------------------------------------------------------------------- */


require_once (dirname(__FILE__).'/class.pdf.php');

/*
//	Changes to move from FPDF to TCPDF to support UTF-8 by Javier de Lorenzo-Cáceres <info@civicom.eu>
*/

if (!isset($PaperSize)){				// Javier: Results True, it's not set.
	$PaperSize = $_SESSION['DefaultPageSize'];	// Javier: DefaultPageSize is taken from DB, www_users, pagesize = A4
}

/* Javier: TCPDF supports 45 standard ISO (DIN) paper formats and 4 american common formats and does this cordinates calculation.
		However, reports use this units */

switch ($PaperSize) {

  case 'A4':

// Javier: Now I use the native TCPDF constructor to which I send these values in each case,
//	this should have been done whith FPDF which use the same values in its constructor.

	$DocumentPaper = 'A4'; $DocumentOrientation ='P';

// Javier: DIN-A4 is 210 mm width, i.e., 595'2756 points (inches * 72 ppi)
      $Page_Width=595;
      $Page_Height=842;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=40;
      $Right_Margin=30;
      break;

  case 'A4_Landscape':

	$DocumentPaper = 'A4'; $DocumentOrientation ='L';

      $Page_Width=842;
      $Page_Height=595;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=40;
      $Right_Margin=30;
      break;

  case 'A5':

	$DocumentPaper = 'A5'; $DocumentOrientation ='P';

      $Page_Width=421;
      $Page_Height=595;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=40;
      $Right_Margin=30;
      break;

  case 'A5_Landscape':

	$DocumentPaper = 'A5'; $DocumentOrientation ='L';

      $Page_Width=595;
      $Page_Height=421;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=40;
      $Right_Margin=30;
      break;

   case 'A3':

	$DocumentPaper = 'A3'; $DocumentOrientation ='P';

      $Page_Width=842;
      $Page_Height=1190;
      $Top_Margin=50;
      $Bottom_Margin=50;
      $Left_Margin=50;
      $Right_Margin=40;
      break;

   case 'A3_landscape':

	$DocumentPaper = 'A3'; $DocumentOrientation ='L';

      $Page_Width=1190;
      $Page_Height=842;
      $Top_Margin=50;
      $Bottom_Margin=50;
      $Left_Margin=50;
      $Right_Margin=40;
      break;

   case 'letter':

	$DocumentPaper = 'LETTER'; $DocumentOrientation ='P';

      $Page_Width=612;
      $Page_Height=792;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=30;
      $Right_Margin=25;
      break;

   case 'letter_landscape':

	$DocumentPaper = 'LETTER'; $DocumentOrientation ='L';

      $Page_Width=792;
      $Page_Height=612;
      $Top_Margin=30;
      $Bottom_Margin=30;
      $Left_Margin=30;
      $Right_Margin=25;
      break;

   case 'legal':

	$DocumentPaper = 'LEGAL'; $DocumentOrientation ='P';

      $Page_Width=612;
      $Page_Height=1008;
      $Top_Margin=50;
      $Bottom_Margin=40;
      $Left_Margin=30;
      $Right_Margin=25;
      break;

   case 'legal_landscape':

	$DocumentPaper = 'LEGAL'; $DocumentOrientation ='L';

      $Page_Width=1008;
      $Page_Height=612;
      $Top_Margin=50;
      $Bottom_Margin=40;
      $Left_Margin=30;
      $Right_Margin=25;
      break;
}

// Javier: I correct the call to the constructor to match TCPDF (and FPDF ;-)
//	$PageSize = array(0,0,$Page_Width,$Page_Height);
//	$pdf = new Cpdf($PageSize);
$pdf = new Cpdf($DocumentOrientation, 'pt', $DocumentPaper);

$pdf->addInfo('Creator', 'WebERP http://www.weberp.org');
$pdf->addInfo('Author', 'WebERP ' . $Version);


/* Javier: I have brought this piece from the pdf class constructor to get it closer to the admin/user,
	I corrected it to match TCPDF, but it still needs check, after which,
	I think it should be moved to each report to provide flexible Document Header and Margins in a per-report basis. */
 	$pdf->SetAutoPageBreak(true, 0);	// Javier: needs check.
	$pdf->SetPrintHeader(false);	// Javier: I added this must be called before Add Page
	$pdf->AddPage();
//	$this->SetLineWidth(1); 	   Javier: It was ok for FPDF but now is too gross with TCPDF. TCPDF defaults to 0'57 pt (0'2 mm) which is ok.
	$pdf->cMargin = 0;		// Javier: needs check.
/* END Brought from class.pdf.php constructor */

?>
