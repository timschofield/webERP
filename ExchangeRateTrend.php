<?php
/* $Revision: 1.3 $ */
/* $Id$*/

$PageSecurity = 2;

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

	echo "<form method='post' name=update action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
		_('View Currency Trend') . '" alt="">' . ' ' . _('View Currency Trend') . '';
	echo '<table>'; // First column

	$SQL = 'SELECT * FROM currencies';
	$result=DB_query($SQL,$db);


	// CurrencyToShow Currency Picker
	echo '<tr><td><select name="CurrencyToShow" onChange="ReloadForm(update.submit)">';

		DB_data_seek($result,0);
		while ($myrow=DB_fetch_array($result)) {
			if ($myrow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
				if ( $CurrencyToShow==$myrow['currabrev'] )	{
					echo '<option selected value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')';
				} else {
					echo '<option value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')';
				}
			}
		}
		echo '</select></td></tr>';

   	echo '</table>'; /*close off the table in the third column */


	echo '<p><div class="centre"><input type=submit name=submit VALUE="' . _('Accept') . '"></div>';
   	echo '</form>';



// **************
// SHOW OUR GRAPH
// **************


	$graph = $CurrencyToShow. '/' . $FunctionalCurrency . $graph;
	$image = 'http://www.x-rates.com/d/' . $graph;

	echo '<p></div><table class=selection>';
	echo '<tr><th><div class="centre"><font size=4 color=BLUE><b><U>' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</b></U></font></th></tr>';
	echo '<tr><td><img src=' . $image . ' alt="Trend Currently Unavailable"></td></tr>';
	echo '</table>';


include('includes/footer.inc');
?>