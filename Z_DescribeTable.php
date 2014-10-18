<?php
/* $Id: Z_DescribeTable.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Database table details');
include('includes/header.inc');

$sql='DESCRIBE '.$_GET['table'];
$result=DB_query($sql, $db);

echo '<table><tr>';
echo '<th>' . _('Field name') . '</th>';
echo '<th>' . _('Field type') . '</th>';
echo '<th>' . _('Can field be null') . '</th>';
echo '<th>' . _('Default') . '</th>';
while ($myrow=DB_fetch_row($result)) {
	echo '<tr><td>' .$myrow[0]  . '</td><td>';
	echo $myrow[1]  . '</td><td>';
	echo $myrow[2]  . '</td><td>';
	echo $myrow[4]  . '</td></tr>';
}
echo '</table>';
include('includes/footer.inc');


?>