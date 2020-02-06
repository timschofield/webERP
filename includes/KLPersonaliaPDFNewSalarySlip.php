<?php

/////////////////////////////////////////////////////////////////////
//  Creates and sets new PDF document information
/////////////////////////////////////////////////////////////////////

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
// set PDF document information
$pdf->SetCreator($Company . ' Admin Team');
$pdf->SetAuthor($Company . ' Admin Team');
$pdf->SetTitle($CoreFileName);
$pdf->SetSubject($CoreFileName);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$FontType = 'helvetica';
$FontBigSize = 12;
$FontNormalSize = 10;
$FontSmallSize = 8;

?>