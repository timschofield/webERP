<?php

// Currency Exchange Rate Factors Display
// Shows currency exchange rates and factor-based minimum retail prices

require(__DIR__ . '/includes/session.php');

$Title = __('Currency Exchange Rate Factors');
$ViewTopic = 'Setup';
$BookMark = 'CurrencyExchangeRateFactors';
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/CurrenciesArray.php');
include(__DIR__ . '/includes/CountriesArray.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

echo '<p class="page_title_text">
	<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/currency.png" title="' . __('Currency Exchange Rate Factors') . '" />
	' . __('Currency Exchange Rate Factors') . '
</p>';

// Get all currencies and their exchange rates
$SQL = "SELECT currabrev,
				currency,
				country,
				rate,
				decimalplaces
		FROM currencies
		ORDER BY currabrev";

$Result = DB_query($SQL);

$CurrencyData = array();
while ($MyRow = DB_fetch_array($Result)) {
	$CurrencyData[] = $MyRow;
}

// Display Factor Standard Cost table
$TableTitleText = __('Standard Cost Factors');
ShowTableTitle($TableTitleText);
echo '<div>';
echo '<table class="selection">';
echo '<tr>
	<th>&nbsp;</th>
	<th>' . __('From Indonesia') . '</th>
	<th>' . __('From Overseas') . '</th>
</tr>';

foreach ($CurrencyData as $Currency) {
	if ($Currency['currabrev'] == 'IDR') {
		echo '<tr class="striped_row">
			<td><strong>' . __('Buying in') . ' ' . $Currency['currabrev'] . '</strong></td>
			<td class="number">1</td>
			<td class="number">1</td>
		</tr>';
	} else {
		$ExchangeRate = $Currency['rate'];
		echo '<tr class="striped_row">
			<td><strong>' . __('Buying in') . ' ' . $Currency['currabrev'] . '</strong></td>
			<td class="number">' . locale_number_format(1 / $ExchangeRate, 0) . '</td>
			<td class="number">' . locale_number_format(1 / $ExchangeRate, 0) . '</td>
		</tr>';
	}
}

echo '</table></div><br />';

// Display margin information for KL
$TableTitleText = __('MINIMUM RETAIL PRICE FOR KL');
ShowTableTitle($TableTitleText);
echo '<div>';
if (isset($_SESSION['Price_Factor_Minimum_KL'])) {
	$MarginKL = $_SESSION['Price_Factor_Minimum_KL'];
	ShowTableSubTitle(__('Margin Retail KL') . ': ' . locale_number_format($MarginKL, 2));
}

echo '<table class="selection">';
echo '<tr>
	<th>&nbsp;</th>
	<th>' . __('From Indonesia') . '</th>
	<th>' . __('From Overseas') . '</th>
</tr>';

foreach ($CurrencyData as $Currency) {
	if ($Currency['currabrev'] == 'IDR') {
		$FactorKL = isset($_SESSION['Price_Factor_Minimum_KL']) ? $_SESSION['Price_Factor_Minimum_KL'] : 5;
		echo '<tr class="striped_row">
			<td><strong>' . __('Buying in') . ' ' . $Currency['currabrev'] . '</strong></td>
			<td class="number">' . locale_number_format($FactorKL, 0) . '</td>
			<td class="number">' . locale_number_format($FactorKL, 0) . '</td>
		</tr>';
	} else {
		$ExchangeRate = $Currency['rate'];
		$FactorKL = isset($_SESSION['Price_Factor_Minimum_KL']) ? $_SESSION['Price_Factor_Minimum_KL'] : 5;
		$CalculatedFactorKL = (1 / $ExchangeRate) * $FactorKL;
		echo '<tr class="striped_row">
			<td><strong>' . __('Buying in') . ' ' . $Currency['currabrev'] . '</strong></td>
			<td class="number">' . locale_number_format($CalculatedFactorKL, 0) . '</td>
			<td class="number">' . locale_number_format($CalculatedFactorKL, 0) . '</td>
		</tr>';
	}
}

echo '</table></div><br />';

// Display margin information for BLINK
$TableTitleText = __('MINIMUM RETAIL PRICE FOR BLINK');
ShowTableTitle($TableTitleText);
echo '<div>';
if (isset($_SESSION['Price_Factor_Minimum_Blink'])) {
	$MarginBlink = $_SESSION['Price_Factor_Minimum_Blink'];
	ShowTableSubTitle(__('Margin Retail BLINK') . ': ' . locale_number_format($MarginBlink, 2));
}

echo '<table class="selection">';
echo '<tr>
	<th>&nbsp;</th>
	<th>' . __('From Indonesia') . '</th>
	<th>' . __('From Overseas') . '</th>
</tr>';

foreach ($CurrencyData as $Currency) {
	if ($Currency['currabrev'] == 'IDR') {
		$FactorBlink = isset($_SESSION['Price_Factor_Minimum_Blink']) ? $_SESSION['Price_Factor_Minimum_Blink'] : 7;
		echo '<tr class="striped_row">
			<td><strong>' . __('Buying in') . ' ' . $Currency['currabrev'] . '</strong></td>
			<td class="number">' . locale_number_format($FactorBlink, 0) . '</td>
			<td class="number">' . locale_number_format($FactorBlink, 0) . '</td>
		</tr>';
	} else {
		$ExchangeRate = $Currency['rate'];
		$FactorBlink = isset($_SESSION['Price_Factor_Minimum_Blink']) ? $_SESSION['Price_Factor_Minimum_Blink'] : 7;
		$CalculatedFactorBlink = (1 / $ExchangeRate) * $FactorBlink;
		echo '<tr class="striped_row">
			<td><strong>' . __('Buying in') . ' ' . $Currency['currabrev'] . '</strong></td>
			<td class="number">' . locale_number_format($CalculatedFactorBlink, 0) . '</td>
			<td class="number">' . locale_number_format($CalculatedFactorBlink, 0) . '</td>
		</tr>';
	}
}

echo '</table></div><br />';

// Display information about price factors
echo '<div class="centre">
	<p><a href="' . $RootPath . '/KLSystemParameters.php">' . __('Configure Price Factors') . '</a></p>
	<p><a href="' . $RootPath . '/Currencies.php">' . __('Manage Currencies') . '</a></p>
</div>';

include(__DIR__ . '/includes/footer.php');
