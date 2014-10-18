<?php

/* $Id: ExchangeRateTrend.php 6501 2013-12-14 18:43:48Z rchacon $*/

include('includes/session.inc');
$Title = _('View Currency Trends');
include('includes/header.inc');

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
	echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		_('View Currency Trend') . '" alt="" />' . ' ' . _('View Currency Trend') . '</p></div>';
	echo '<table>'; // First column

	$SQL = "SELECT currabrev FROM currencies";
	$result=DB_query($SQL,$db);
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

include('includes/footer.inc');
?>
