<?php

/////////////////////////////////////////////////////////////////////
//  Update the pajak ratios components by section
/////////////////////////////////////////////////////////////////////

if ($Section == 1){
	// Income section contains the net sales, not including PPN 
	$PajakRatio_Sales = -$SectionPrdActual;
	$PajakRatio_Sales_LY = -$SectionPrdLY;
}

if ($Section == 2){
	// Cost of sales section contains the HPP 
	$PajakRatio_HPP = $SectionPrdActual;
	$PajakRatio_HPP_LY = $SectionPrdLY;
}

if ($Section == 800){
	// Pajak section contains the total tax paid (TO BE CONFIRMED) 
	$PajakRatio_Taxes = $SectionPrdActual;
	$PajakRatio_Taxes_LY = $SectionPrdLY;
	
	$PajakRatio_ProfitAfterTax = -$PeriodProfitLoss;
	$PajakRatio_ProfitAfterTax_LY = -$PeriodLYProfitLoss;
}
