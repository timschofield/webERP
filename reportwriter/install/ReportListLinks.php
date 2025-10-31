<?php

/// @deprecated seems unused

/**
 * This function retrieves the reports given a certain group id as defined in /reports/admin/defaults.php
 * in the associative array $ReportGroups[]. It will fetch the reports belonging solely to the group
 * specified to create a list of links for insertion into a table to choose a report. Two table sections will
 * be generated, one for standard reports and the other for custom reports.
 *
 * Revision History:
 * Revision 1.0 - 2005-11-03 - By D. Premo - Initial Release
 */
function GetRptLinks($GroupID) {
	global $RootPath;
	$Title= array(__('Custom Reports'), __('Standard Reports'));
	$RptLinks = '';
	for ($Def=1; $Def>=0; $Def--) {
		$RptLinks .= '<tr><td class="menu_group_headers"><div align="center">'.$Title[$Def].'</div></td></tr>';
		$sql= "SELECT id, reportname FROM reports
			WHERE defaultreport='".$Def."' AND groupname='".$GroupID."'
			ORDER BY reportname";
		$Result = DB_query($sql,'','',false,true);
		if (DB_num_rows($Result)>0) {
			while ($Temp = DB_fetch_array($Result)) {
				$RptLinks .= '<tr><td class="menu_group_item">';
				$RptLinks .= '<a href="'.$RootPath.'/reportwriter/ReportMaker.php?action=go&reportid='.$Temp['id'].'"><li>'.__($Temp['reportname']).'</li></a>';
				$RptLinks .= '</td></tr>';
			}
		} else {
			$RptLinks .= '<tr><td class="menu_group_item">'.__('There are no reports to show!').'</td></tr>';
		}
	}
	return $RptLinks;
}
