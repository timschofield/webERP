<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Database table details');
include('includes/header.php');

$SQL='DESCRIBE '.$_GET['table'];
$Result = DB_query($SQL);

echo '<table><tr>';
echo '<th>' . __('Field name') . '</th>';
echo '<th>' . __('Field type') . '</th>';
echo '<th>' . __('Can field be null') . '</th>';
echo '<th>' . __('Default') . '</th>';
while ($MyRow=DB_fetch_row($Result)) {
	echo '<tr><td>' .$MyRow[0]  . '</td><td>';
	echo $MyRow[1]  . '</td><td>';
	echo $MyRow[2]  . '</td><td>';
	echo $MyRow[4]  . '</td></tr>';
}
echo '</table>';
include('includes/footer.php');
