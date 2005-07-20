<?php
/* $Revision: 1.5 $ */

$PageSecurity = 5;

include ('includes/session.inc');
$title = _('Produce Stock Quantities CSV');
include ('includes/header.inc');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", "", $str);
}

echo '<P>' . _('Making a comma seperated values file of the current stock quantities');

$ErrMsg = _('The SQL to get the stock quantites failed with the message');

$sql = 'SELECT stockid, SUM(quantity) FROM locstock GROUP BY stockid HAVING SUM(quantity)<>0';
$result = DB_query($sql, $db, $ErrMsg);

$fp = fopen($_SESSION['reports_dir'] . '/StockQties.csv', "w");

While ($myrow = DB_fetch_row($result)){
	$line = stripcomma($myrow[0]) . ', ' . stripcomma($myrow[1]);
	fputs($fp, $line . "\n");
}

fclose($fp);

echo "<P><A HREF='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/StockQties.csv'>" . _('click here') . '</A> ' . _('to view the file') . '<BR>';

include('includes/footer.inc');

?>
