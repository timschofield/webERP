<?php

/////////////////////////////////////////////////////////////////////
//  Display the pajak ratios 
/////////////////////////////////////////////////////////////////////

$TableTitleText = 'Pajak Ratio (for the period selected and same period last year)';
ShowTableTitle($TableTitleText);

echo '<div>
      <table class="selection">
      <thead>
          <tr>
              <th class="SortedColumn">' . _('Ratio') . '</th>
              <th class="SortedColumn">' . _('Benchmark') . '</th>
              <th class="SortedColumn">' . _('Period Actual') . '</th>
              <th class="SortedColumn">' . _('Last Year') . '</th>
          </tr>
      </thead>
      <tbody>';

// Ratio GPM
echo '<tr class="striped_row">
		<td>HPP</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_HPP,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>Sales</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_Sales,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Sales_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>GPM Ratio</td>
		<td class="number">30.21%</td>
		<td class="number">' . locale_number_format($PajakRatio_HPP / $PajakRatio_Sales * 100, 2). '%' . '</td>
		<td class="number">' . locale_number_format($PajakRatio_HPP_LY / $PajakRatio_Sales_LY * 100, 2). '%' . '</td>
		</tr>';


// Ratio CTTOR
echo '<tr class="striped_row">
		<td>Taxes</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_Taxes,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Taxes_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>Sales</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_Sales,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Sales_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>CTTOR Ratio</td>
		<td class="number">3.79%</td>
		<td class="number">' . locale_number_format($PajakRatio_Taxes / $PajakRatio_Sales * 100, 2). '%' . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Taxes_LY / $PajakRatio_Sales_LY * 100, 2). '%' . '</td>
		</tr>';		


// Ratio NPM
echo '<tr class="striped_row">
		<td>Profit after tax</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_ProfitAfterTax,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_ProfitAfterTax_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>Sales</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_Sales,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Sales_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>NPM Ratio</td>
		<td class="number">12.44%</td>
		<td class="number">' . locale_number_format($PajakRatio_ProfitAfterTax / $PajakRatio_Sales * 100, 2). '%' . '</td>
		<td class="number">' . locale_number_format($PajakRatio_ProfitAfterTax_LY / $PajakRatio_Sales_LY * 100, 2). '%' . '</td>
		</tr>';		


// Ratio G
echo '<tr class="striped_row">
		<td>Salaries</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_Salaries,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Salaries_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>Sales</td>
		<td class="number"></td>
		<td class="number">' . locale_number_format($PajakRatio_Sales,0) . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Sales_LY,0) . '</td>
		</tr>';

echo '<tr class="striped_row">
		<td>G Ratio</td>
		<td class="number">6.31%</td>
		<td class="number">' . locale_number_format($PajakRatio_Salaries / $PajakRatio_Sales * 100, 2). '%' . '</td>
		<td class="number">' . locale_number_format($PajakRatio_Salaries_LY / $PajakRatio_Sales_LY * 100, 2). '%' . '</td>
		</tr>';		

echo '</tbody>
	</table>
	</div>';
?>