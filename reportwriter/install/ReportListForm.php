<?php

/**
 * This function retrieves the reports given a certain group id as defined in /reports/admin/defaults.php
 * in the associative array $ReportGroups[]. It will fetch the reports belonging solely to the group
 * specified to create a select form to choose a report. Two on-submit select forms will be generated, one
 * for default reports and the other for custom reports.
 *
 * Revision History:
 * Revision 1.0 - 2005-11-03 - By D. Premo - Initial Release
 */
function GetReports($GroupID) {
	global $RootPath;
	$Title= array(__('Custom Reports'), __('Default Reports'));
	$RptForm = '<form name="ReportList" method="post" action="'.$RootPath.'/reportwriter/ReportMaker.php?action=go">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$RptForm .= '<table align="center" border="0" cellspacing="1" cellpadding="1" class="table_index">';
	for ($Def=1; $Def>=0; $Def--) {
		$RptForm .= '<tr><td colspan="2"><div align="center">'.$Title[$Def].'</div></td></tr>';
		$sql= "SELECT id, reportname FROM reports
			WHERE defaultreport='".$Def."' AND groupname='".$GroupID."'
			ORDER BY reportname";
		$Result = DB_query($sql,'','',false,true);
		if (DB_num_rows($Result)>0) {
			$RptForm .= '<tr><td><select name="ReportID" size="10" onchange="submit()">';
			while ($Temp = DB_fetch_array($Result)) {
				$RptForm .= '<option value="'.$Temp['id'].'">'.$Temp['reportname'].'</option>';
			}
			$RptForm .= '</select></td></tr>';
		} else {
			$RptForm .= '<tr><td colspan="2">'.__('There are no reports to show!').'</td></tr>';
		}
	}
	$RptForm .= '</table></form>';
	return $RptForm;
}
