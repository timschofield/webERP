<?php
/* $Id: Z_MakeStockLocns.php 5784 2012-12-29 04:00:43Z daintree $*/
/* Script to make stock locations for all parts that do not have stock location records set up*/

include ('includes/session.inc');
$Title = _('Make LocStock Records');
include('includes/header.inc');

echo '<br /><br />' . _('This script makes stock location records for parts where they do not already exist');

$sql = "INSERT INTO locstock (stockid, loccode)
		SELECT stockmaster.stockid,
			locations.loccode
		FROM stockmaster CROSS JOIN locations
			LEFT JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				AND locations.loccode = locstock.loccode
                WHERE locstock.stockid IS NULL";

$ErrMsg = _('The items/locations that need stock location records created cannot be retrieved because');
$Result = DB_query($sql,$db,$ErrMsg);

echo '<p />';
prnMsg(_('Any stock items that may not have had stock location records have now been given new location stock records'),'info');

include('includes/footer.inc');
?>
