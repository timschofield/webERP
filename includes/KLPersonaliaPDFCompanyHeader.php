<?php

/////////////////////////////////////////////////////////////////////
//  Prints the company header on salary slips in HTML
/////////////////////////////////////////////////////////////////////

if ($Company == 'PTBB'){
	$HTML .= '<div class="header-big">PT. Bumi Biru</div>';
	$HTML .= '<div class="header-small">Jl. Kesambi 1, Kerobokan - Bali - Indonesia</div>';
	$HTML .= '<div class="header-small">Ph. +62 81 238 167 94</div>';
}elseif ($Company == 'PTADU'){
	$HTML .= '<div class="header-big">PT. Angin Dingin Utara</div>';
	$HTML .= '<div class="header-small">Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali</div>';
	$HTML .= '<div class="header-small">Ph. +62 812 381 6795</div>';
}elseif ($Company == 'PTSMH'){
	$HTML .= '<div class="header-big">PT. Sungai Mutiara Hitam</div>';
	$HTML .= '<div class="header-small">Jl. Raya Kesambi No. 1B, Kerobokan Kuta Utara, Badung - Bali</div>';
	$HTML .= '<div class="header-small">Ph. +62 812 381 6795</div>';
}				
