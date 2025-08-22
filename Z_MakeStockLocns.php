<?php
/* Script to make stock locations for all parts that do not have stock location records set up*/

include('includes/session.php');
$Title = __('Make LocStock Records');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

echo '<br /><br />' . __('This script makes stock location records for parts where they do not already exist');

$SQL = "INSERT INTO locstock (stockid, loccode)
		SELECT stockmaster.stockid,
			locations.loccode
		FROM stockmaster CROSS JOIN locations
			LEFT JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				AND locations.loccode = locstock.loccode
                WHERE locstock.stockid IS NULL";

$ErrMsg = __('The items/locations that need stock location records created cannot be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

echo '<p />';
prnMsg(__('Any stock items that may not have had stock location records have now been given new location stock records'),'info');

include('includes/footer.php');
