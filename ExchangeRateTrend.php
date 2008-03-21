<?php
/* $Revision: 1.2 $ */

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

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<P><CENTER><FONT SIZE=4 COLOR=BLUE><B><U>' . _('View Currency Trend') . '</B></U></FONT>';
	echo '<TABLE>'; // First column

	$SQL = 'SELECT * FROM currencies';
	$result=DB_query($SQL,$db);


	// CurrencyToShow Currency Picker
	echo '<TR><TD><SELECT name="CurrencyToShow">';

		DB_data_seek($result,0);
		while ($myrow=DB_fetch_array($result)) {
			if ($myrow['currabrev']!=$_SESSION['CompanyRecord']['currencydefault']){
				if ( $CurrencyToShow==$myrow['currabrev'] )	{
					echo '<OPTION SELECTED value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')';
				} else {
					echo '<OPTION value=' . $myrow['currabrev'] . '>' . $myrow['country'] . ' ' . $myrow['currency'] . '&nbsp;(' . $myrow['currabrev'] . ')';
				}
			}
		}
		echo '</SELECT></TD></TR>';

   	echo '</TABLE>'; /*close off the table in the third column */


	echo '<P><INPUT TYPE=submit NAME=submit VALUE="' . _('Accept') . '"></CENTER>';
   	echo '</FORM>';



// **************
// SHOW OUR GRAPH
// **************


	$graph = $CurrencyToShow. '/' . $FunctionalCurrency . $graph;
	$image = 'http://www.x-rates.com/d/' . $graph;

	echo '<P><CENTER><FONT SIZE=4 COLOR=BLUE><B><U>' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</B></U></FONT>';
	echo '<P><TABLE BORDER=1>';
	echo '<TR><td align=center><img src=' . $image . ' alt="Trend Currently Unavailable"></td></TR>';
	echo '</TABLE></CENTER>';


include('includes/footer.inc');
?>