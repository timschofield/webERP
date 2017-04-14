<?php
/* $Id: Z_MakeLocUsers.php 1 agaluski $*/
/* Script to make user locations for all users that do not have user location records set up*/

include ('includes/session.php');
$Title = _('Make locationusers Records');
include('includes/header.php');

echo '<br /><br />' . _('This script makes stock location records for parts where they do not already exist');

$sql = "INSERT INTO locationusers (userid, loccode, canview, canupd)
		SELECT www_users.userid,
		locations.loccode,
		1,
		1
		FROM www_users CROSS JOIN locations
		LEFT JOIN locationusers
		ON www_users.userid = locationusers.userid
		AND locations.loccode = locationusers.loccode
        WHERE locationusers.userid IS NULL;";

$ErrMsg = _('The users/locations that need user location records created cannot be retrieved because');
$Result = DB_query($sql,$ErrMsg);

echo '<p />';
prnMsg(_('Any users that may not have had user location records have now been given new location user records'),'info');

include('includes/footer.php');
?>
