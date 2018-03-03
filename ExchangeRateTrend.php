<?php
/* This script shows the trend in exchange rates as retrieved from ECB. */

include('includes/session.php');
$Title = _('View Currency Trend');
$ViewTopic= 'Currencies';
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
		_('View Currency Trend'), '" /> ', // Icon title.
		_('View Currency Trend'), '</p>';// Page title.
	echo '<table>'; // First column

	$SQL = "SELECT currabrev FROM currencies";
	$result=DB_query($SQL);
	include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

	// CurrencyToShow Currency Picker
	echo '<tr>
			<td><select name="CurrencyToShow" onchange="ReloadForm(update.submit)">';

	DB_data_seek($result, 0);
	while ($myrow=DB_fetch_array($result)) {
		if ($myrow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
			echo '<option';
			if ( $CurrencyToShow==$myrow['currabrev'] )	{
				echo ' selected="selected"';
			}
			echo ' value="' . $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . ' (' . $myrow['currabrev'] . ')</option>';
		}
	}
	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Accept') . '" />
		</div>
	</div>
	</form>';

// **************
// SHOW OUR GRAPH
// **************
	$image = 'http://www.google.com/finance/getchart?q=' . $FunctionalCurrency . $CurrencyToShow . '&amp;x=CURRENCY&amp;p=3M&amp;i=86400';

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
			<td><img src="' . $image . '" alt="' ._('Trend Currently Unavailable') . '" /></td>
		</tr>
		</table>';

include('includes/footer.php');
?>
