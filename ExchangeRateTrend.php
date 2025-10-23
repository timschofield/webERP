<?php
/* This script shows the trend in exchange rates as retrieved from ECB. */

require(__DIR__ . '/includes/session.php');

$Title = __('View Currency Trend');
$ViewTopic = 'Currencies';
$BookMark = 'ExchangeRateTrend';
include('includes/header.php');

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if ( isset($_GET['CurrencyToShow']) ){
	$CurrencyToShow = $_GET['CurrencyToShow'];
} elseif ( isset($_POST['CurrencyToShow']) ) {
	$CurrencyToShow = $_POST['CurrencyToShow'];
}

// ************************
// SHOW OUR MAIN INPUT FORM
// ************************

	echo '<form method="post" id="update" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/currency.png" title="', // Icon image.
		__('View Currency Trend'), '" /> ', // Icon title.
		__('View Currency Trend'), '</p>';// Page title.
	echo '<table>'; // First column

	$SQL = "SELECT currabrev FROM currencies";
	$Result = DB_query($SQL);
	include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

	// CurrencyToShow Currency Picker
	echo '<tr>
			<td><select name="CurrencyToShow" onchange="ReloadForm(update.submit)">';

	DB_data_seek($Result, 0);
	while ($MyRow=DB_fetch_array($Result)) {
		if ($MyRow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
			echo '<option';
			if ( $CurrencyToShow==$MyRow['currabrev'] )	{
				echo ' selected="selected"';
			}
			echo ' value="' . $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . ' (' . $MyRow['currabrev'] . ')</option>';
		}
	}
	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Accept') . '" />
		</div>
	</div>
	</form>';

// **************
// SHOW OUR GRAPH
// **************
	$Image = '//www.google.com/finance/getchart?q=' . $FunctionalCurrency . $CurrencyToShow . '&amp;x=CURRENCY&amp;p=3M&amp;i=86400';

	echo '<br />
		<table class="selection">
		<tr>
			<th>
				<div class="centre">
					<b>' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</b>
				</div>
			</th>
		</tr>
		<tr>
			<td><img src="' . $Image . '" alt="' .__('Trend Currently Unavailable') . '" /></td>
		</tr>
		</table>';

include('includes/footer.php');
