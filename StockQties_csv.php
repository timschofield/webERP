<?php
/* $Revision: 1.8 $ */

$PageSecurity = 5;

include ('includes/session.inc');
$title = _('Produce Stock Quantities CSV');
include ('includes/header.inc');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", "", $str);
}

echo '<p>' . _('Making a comma separated values file of the current stock quantities');

$ErrMsg = _('The SQL to get the stock quantities failed with the message');

$sql = 'SELECT stockid, SUM(quantity) FROM locstock GROUP BY stockid HAVING SUM(quantity)<>0';
$result = DB_query($sql, $db, $ErrMsg);

if (!file_exists($_SESSION['reports_dir'])){
	$Result = mkdir('./' . $_SESSION['reports_dir']);
}

$filename = $_SESSION['reports_dir'] . '/StockQties.csv';

$fp = fopen($filename,"w");

if ($fp==FALSE){
	
	prnMsg(_('Could not open or create the file under') . ' ' . $_SESSION['reports_dir'] . '/StockQties.csv','error');
	include('includes/footer.inc');
	exit;
}

While ($myrow = DB_fetch_row($result)){
	$line = stripcomma($myrow[0]) . ', ' . stripcomma($myrow[1]);
	fputs($fp, $line . "\n");
}

fclose($fp);

echo "<p><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/StockQties.csv'>" . _('click here') . '</a> ' . _('to view the file') . '<br>';

include('includes/footer.inc');

?>
