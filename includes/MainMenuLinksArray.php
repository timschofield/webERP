<?php

/***************************************************************************************************************
 * 
 * KL RICARD: Divide certain menu entries into different sections depesning on the user's role
 * 			- KL Performance Board
 * 			- KL Control Board
 *			- KL Shop Transfer - Receive Transfer FROM kantor (show different caption)
 * 
 ***************************************************************************************************************/

unset($_SESSION['ModuleLink']);
unset($_SESSION['ReportList']);
unset($_SESSION['ModuleList']);
unset($_SESSION['MenuItems']);

$SQL = "SELECT `modulelink`,
				`reportlink` ,
				`modulename`
		FROM modules
		ORDER BY `sequence`";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	$ModuleLink[] = $MyRow['modulelink'];
	$ReportList[$MyRow['modulelink']] = $MyRow['reportlink'];
	$ModuleList[] = _($MyRow['modulename']);
}

$SQL = "SELECT `modulelink`,
				`menusection` ,
				`caption` ,
				`url`
		FROM menuitems
		ORDER BY `sequence`, `menusection`";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (($KL_SystemAdmin 
			OR $KL_BusinessDevelopmentManager
			OR $KL_OperationalManager
			OR $KL_SalesDirector)
		AND ($MyRow['url'] == '/KLPerformanceBoard.php')) {
			// divide the KL performance board into 3 sections
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = 	_('KL Performance Board Section 01');
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = '/KLPerformanceBoard.php?Section=01';
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = 	_('KL Performance Board Section 02');
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = '/KLPerformanceBoard.php?Section=02';
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = 	_('KL Performance Board Section 03');
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = '/KLPerformanceBoard.php?Section=03';
	}
	elseif (($KL_BusinessDevelopmentManager
			OR $KL_SalesDirector)
			// divide the KL control board into 2 sections
		AND ($MyRow['url'] == '/KLControlBoard.php')) {
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = 	_('KL Control Board Section 01');
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = '/KLControlBoard.php?Section=01';
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = 	_('KL Control Board Section 02');
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = '/KLControlBoard.php?Section=02';
	}
	elseif ($MyRow['url'] == '/StockLocTransferReceive.php') {
		// show different menu caption for KL_SPGSeniorOrSupport and KL_SPGJunior
		if ($KL_SPGSeniorOrSupport 
			OR $KL_SPGJunior){
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = _('KL Shop Transfer - Receive Transfer FROM kantor');
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = $MyRow['url'];
		}else{
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = _($MyRow['caption']);
			$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = $MyRow['url'];
		}
	}
	else {
		// Normal menu entries
		$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = _($MyRow['caption']);
		$MenuItems[$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = $MyRow['url'];
	}
}
