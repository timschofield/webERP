<?php

$PageSecurity = 1;

require(__DIR__ . '/includes/session.php');

$SQL = "SELECT caption
			FROM favourites
			WHERE userid='" . $_SESSION['UserID'] . "'
				AND href='" . $_GET['Script'] . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	$SQL = "INSERT INTO favourites ( userid,
									caption,
									href
								) VALUES (
									'" . $_SESSION['UserID'] . "',
									'" . $_GET['Title'] . "',
									'" . $_GET['Script'] . "'
								)";
	$Result = DB_query($SQL);
} else {
	$SQL = "DELETE FROM favourites
					WHERE userid='" . $_SESSION['UserID'] . "'
						AND href='" . $_GET['Script'] . "'";
	$Result = DB_query($SQL);
}
$SQL = "SELECT caption, href FROM favourites WHERE userid='" . $_SESSION['UserID'] . "'";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	$_SESSION['Favourites'][$MyRow['href']] = $MyRow['caption'];
}
