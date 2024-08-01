<?php

/////////////////////////////////////////////////////////////////////
//  Prints the company header on salary slips
/////////////////////////////////////////////////////////////////////

if ($Company == 'PTBB'){
	$pdf->SetFont($FontType, 'B', $FontBigSize);
	$pdf->MultiCell(0, 0, 'PT. Bumi Biru', 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont($FontType, '', $FontSmallSize);
	$pdf->MultiCell(0, 0, 'Jl. Kesambi 1, Kerobokan - Bali - Indonesia', 0, 'L', 0, 1, '', '', true);
	$pdf->MultiCell(0, 0, 'Ph. +62 81 238 167 94', 0, 'L', 0, 1, '', '', true);
}elseif ($Company == 'PTADU'){
	$pdf->SetFont($FontType, 'B', $FontBigSize);
	$pdf->MultiCell(0, 0, 'PT. Angin Dingin Utara', 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont($FontType, '', $FontSmallSize);
	$pdf->MultiCell(0, 0, 'Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali', 0, 'L', 0, 1, '', '', true);
	$pdf->MultiCell(0, 0, 'Ph. +62 812 381 6795', 0, 'L', 0, 1, '', '', true);
}elseif ($Company == 'PTSMH'){
	$pdf->SetFont($FontType, 'B', $FontBigSize);
	$pdf->MultiCell(0, 0, 'PT. Sungai Mutiara Hitam', 0, 'L', 0, 1, '', '', true);
	$pdf->SetFont($FontType, '', $FontSmallSize);
	$pdf->MultiCell(0, 0, 'Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali', 0, 'L', 0, 1, '', '', true);
	$pdf->MultiCell(0, 0, 'Ph. +62 812 381 6795', 0, 'L', 0, 1, '', '', true);
}				

?>