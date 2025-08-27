<?php

unset($_SESSION['ModuleLink']);
unset($_SESSION['ReportList']);
unset($_SESSION['ModuleList']);
unset($_SESSION['MenuItems']);

$SQL = "SELECT `modulelink`,
				`reportlink` ,
				`modulename`
			FROM modules
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	$ModuleLink[] = $MyRow['modulelink'];
	$ReportList[$MyRow['modulelink']] = $MyRow['reportlink'];
	$ModuleList[] = __($MyRow['modulename']);
}
$SQL = "SELECT `modulelink`,
				`menusection` ,
				`caption` ,
				`url`
			FROM menuitems
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`, `menusection`";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = __($MyRow['caption']);
	$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = $MyRow['url'];
}
