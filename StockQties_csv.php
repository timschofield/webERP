<?php
/* $Revision: 1.3 $ */

$PageSecurity = 5;

include ('includes/session.inc');
$title = _('Produce Stock Quantities CSV');
include ('includes/header.inc');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", "", $str);
}

echo '<P>' . _('Making a comma seperated values file of the current stock quantities');

$ErrMsg = _('The SQL to get the stock quantites failed with the message');

$sql = 'SELECT StockID, SUM(Quantity) FROM LocStock GROUP BY StockID HAVING SUM(Quantity)<>0';
$result = DB_query($sql, $db, $ErrMsg);

$fp = fopen($reports_dir . '/StockQties.csv', "w");

While ($myrow = DB_fetch_row($result)){

	$line = stripcomma($myrow[0]) . ', ' . stripcomma($myrow[1]);
	fputs($fp, $line . "\n");
}

fclose($fp);

echo "<P><A HREF='" . $rootpath . '/' . $reports_dir . "/StockQties.csv'>" . _('click here') . '</A> ' . _('to view the file') . '<BR>';

include('includes/footer.inc');

?>
