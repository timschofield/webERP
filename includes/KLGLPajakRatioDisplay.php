<?php

/////////////////////////////////////////////////////////////////////
//  Display the pajak ratios 
/////////////////////////////////////////////////////////////////////

echo '<p class="page_title_text" align="center"><strong>' . 'Pajak Ratio (for the period selected and same period last year) '. '</strong></p>';

echo '<div>
	  <table class="selection">';
$TableHeader = '<tr>
					<th class="ascending">' . _('Ratio') . '</th>
					<th class="ascending">' . _('Benchmark') . '</th>
					<th class="ascending">' . _('Period Actual') . '</th>
					<th class="ascending">' . _('Last Year') . '</th>
				</tr>';
echo $TableHeader;
$k = 0; //row colour counter

// Ratio GPM
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"HPP",
		"",
		locale_number_format($PajakRatio_HPP,0),
		locale_number_format($PajakRatio_HPP_LY,0)
		);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Sales",
		"",
		locale_number_format($PajakRatio_Sales,0),
		locale_number_format($PajakRatio_Sales_LY,0)
		);
$k = StartEvenOrOddRow($k);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"GPM Ratio",
		"30.21%",
		locale_number_format($PajakRatio_HPP / $PajakRatio_Sales * 100, 2). "%",
		locale_number_format($PajakRatio_HPP_LY / $PajakRatio_Sales_LY * 100, 2). "%"
		);


// Ratio CTTOR
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Taxes",
		"",
		locale_number_format($PajakRatio_Taxes,0),
		locale_number_format($PajakRatio_Taxes_LY,0)
		);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Sales",
		"",
		locale_number_format($PajakRatio_Sales,0),
		locale_number_format($PajakRatio_Sales_LY,0)
		);
$k = StartEvenOrOddRow($k);
$k = StartEvenOrOddRow($k);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"CTTOR Ratio",
		"3.79%",
		locale_number_format($PajakRatio_Taxes / $PajakRatio_Sales * 100, 2). "%",
		locale_number_format($PajakRatio_Taxes_LY / $PajakRatio_Sales_LY * 100, 2). "%"
		);		

		
// Ratio NPM
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Profit after tax",
		"",
		locale_number_format($PajakRatio_ProfitAfterTax,0),
		locale_number_format($PajakRatio_ProfitAfterTax_LY,0)
		);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Sales",
		"",
		locale_number_format($PajakRatio_Sales,0),
		locale_number_format($PajakRatio_Sales_LY,0)
		);
$k = StartEvenOrOddRow($k);
$k = StartEvenOrOddRow($k);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"NPM Ratio",
		"12.44%",
		locale_number_format($PajakRatio_ProfitAfterTax / $PajakRatio_Sales * 100, 2). "%",
		locale_number_format($PajakRatio_ProfitAfterTax_LY / $PajakRatio_Sales_LY * 100, 2). "%"
		);		

	
// Ratio G
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Salaries",
		"",
		locale_number_format($PajakRatio_Salaries,0),
		locale_number_format($PajakRatio_Salaries_LY,0)
		);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"Sales",
		"",
		locale_number_format($PajakRatio_Sales,0),
		locale_number_format($PajakRatio_Sales_LY,0)
		);
$k = StartEvenOrOddRow($k);
$k = StartEvenOrOddRow($k);
printf('<td>%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		<td class="number">%s</td>
		</tr>', 
		"G Ratio",
		"6.31%",
		locale_number_format($PajakRatio_Salaries / $PajakRatio_Sales * 100, 2). "%",
		locale_number_format($PajakRatio_Salaries_LY / $PajakRatio_Sales_LY * 100, 2). "%"
		);		
			
echo '</table>
	  </div>';
?>