<?php

/* $Id$*/

include('includes/session.inc');
$title = _('View Currency Trends');

include('includes/header.inc');

$graph = '/graph120.png';

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if ( isset($_GET['CurrencyToShow']) ){
	$CurrencyToShow = $_GET['CurrencyToShow'];
} elseif ( isset($_POST['CurrencyToShow']) ) {
	$CurrencyToShow = $_POST['CurrencyToShow'];
}

// ************************
// SHOW OUR MAIN INPUT FORM
// ************************

	echo '<form method="post" name=update action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
		_('View Currency Trend') . '" alt="" />' . ' ' . _('View Currency Trend') . '</p>';
	echo '<table>'; // First column

	$SQL = "SELECT * FROM currencies";
	$result=DB_query($SQL,$db);


	// CurrencyToShow Currency Picker
	echo '<tr><td><select name="CurrencyToShow" onChange="ReloadForm(update.submit)">';

	DB_data_seek($result,0);
	while ($myrow=DB_fetch_array($result)) {
		if ($myrow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
			if ( $CurrencyToShow==$myrow['currabrev'] )	{
				echo '<option selected value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')'. '</option>';
			} else {
				echo '<option value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')'. '</option>';
			}
		}
	}
	echo '</select></td></tr>';

   	echo '</table>'; /*close off the table in the third column */

	echo '<p><div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" /></div>';
   	echo '</form>';

// **************
// SHOW OUR GRAPH
// **************


	$graph = $CurrencyToShow. '/' . $FunctionalCurrency . $graph;
	$image = 'http://www.x-rates.com/d/' . $graph;

	echo '<p></div><table class="selection">';
	echo '<tr><th><div class="centre"><b><U>' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</b></U></th></tr>';
	echo '<tr><td><img src=' . $image . ' alt="' ._('Trend Currently Unavailable') . '" /></td></tr>';
	echo '</table>';


include('includes/footer.inc');
?>