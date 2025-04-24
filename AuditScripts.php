<?php

include('includes/session.php');
$Title = _('Audit Scripts');
include('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' 
	. _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['FromDate'])) {
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['ContainingText'])) {
	$ContainingText = trim(mb_strtoupper($_POST['ContainingText']));
} elseif (isset($_GET['ContainingText'])) {
	$ContainingText = trim(mb_strtoupper($_GET['ContainingText']));
}
if (!isset($_POST['ContainingText'])) {
	$_POST['ContainingText'] = '';
}

if (!isset($_POST['DetailedReport'])) {
	$_POST['DetailedReport'] = 'No';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
	<legend>' . _('Audit Script Selection Criteria') . '</legend>';

echo FieldToSelectOneDate('FromDate', FormatDateForSQL($_POST['FromDate']), _('From Date'), '', '', '1', true, true);
echo FieldToSelectOneDate('ToDate', FormatDateForSQL($_POST['ToDate']), _('To Date'), '', '', '2', true, false);
echo FieldToSelectOneUser('SelectedUser', isset($_POST['SelectedUser']) ? $_POST['SelectedUser'] : 'All', _('User ID'), '', '', '3', true, false);
echo FieldToSelectOneText('ContainingText', $_POST['ContainingText'], 80, 80, _('Containing text'), '', '', '4', false, false);
echo FieldToSelectFromTwoOptions('No', _('Summary Report'), 
                                'Yes', _('Detailed Report'), 
                                'DetailedReport', $_POST['DetailedReport'], _('Summary or detailed report'), '', '', '5', true, false);

echo '</fieldset>';
echo OneButtonCenteredForm('View', _('View'));
echo '</div>
	</form>';

// View the audit trail
if (isset($_POST['View'])) {

	$FromDate = str_replace('/', '-', FormatDateForSQL($_POST['FromDate']) . ' 00:00:00');
	$ToDate = str_replace('/', '-', FormatDateForSQL($_POST['ToDate']) . ' 23:59:59');

	if (mb_strlen($ContainingText) > 0) {
		$ContainingText = " AND scripttitle LIKE '%" . $ContainingText . "%' ";
	} else {
		$ContainingText = "";
	}

	if ($_POST['SelectedUser'] == 'All') {
		$UserSQL = " ";
	} else {
		$UserSQL = " AND userid='" . $_POST['SelectedUser'] . "'";
	}

	/**************************************************************
	SCRIPT USAGE
	***************************************************************/
	
	$SQL = "SELECT scripttitle, 
			COUNT(scripttitle) AS numscripts, 
			SUM(secondsrunning) AS sumseconds
		FROM auditscripts
		WHERE executiondate BETWEEN '" . $FromDate . "' AND '" . $ToDate . "'" 
		. $UserSQL
		. $ContainingText
		. ' GROUP BY scripttitle';

	$Result = DB_query($SQL);

	echo '<p class="page_title_text" align="center"><strong>' . 'General Script Usage' . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th class="SortedColumn">' . _('Script') . '</th>
				<th class="SortedColumn">' . _('# Executions') . '</th>
				<th class="SortedColumn">' . _('Seconds Needed') . '</th>
				<th class="SortedColumn">' . _('Secs/Execution') . '</th>
			</tr>
		</thead>';
	echo '<tbody>';
	$TotalScripts = 0;
	$TotalSeconds = 0;

	while ($MyRow = DB_fetch_array($Result)) {
		$SecsPerExecution = $MyRow['numscripts'] > 0 ? $MyRow['sumseconds'] / $MyRow['numscripts'] : 0;
		echo '<tr class="striped_row">
				<td>' . $MyRow['scripttitle'] . '</td>
				<td class="number">' . locale_number_format($MyRow['numscripts'], 0) . '</td>
				<td class="number">' . locale_number_format($MyRow['sumseconds'], 5) . '</td>
				<td class="number">' . locale_number_format($SecsPerExecution, 5) . '</td>
				</tr>';
		$TotalScripts += $MyRow['numscripts'];
		$TotalSeconds += $MyRow['sumseconds'];
	}
	echo '</tbody>';
	echo '<tfooter>';
	$AvgSecsPerExecution = $TotalScripts > 0 ? $TotalSeconds / $TotalScripts : 0;
	echo '<tr class="striped_row">
		<td>TOTALS</td>
		<td class="number">' . locale_number_format($TotalScripts, 0) . '</td>
		<td class="number">' . locale_number_format($TotalSeconds, 5) . '</td>
		<td class="number">' . locale_number_format($AvgSecsPerExecution, 5) . '</td>
		</tr>';
	echo '</tfooter>';
	echo '</table></div>';
	
	/**************************************************************
	USERS USAGE
	***************************************************************/
	
	$SQL = "SELECT userid, 
			COUNT(scripttitle) AS numscripts, 
			SUM(secondsrunning) AS sumseconds
		FROM auditscripts
		WHERE executiondate BETWEEN '" . $FromDate . "' AND '" . $ToDate . "'" 
		. $UserSQL
		. $ContainingText
		. ' GROUP BY userid';

	$Result = DB_query($SQL);

	echo '<p class="page_title_text" align="center"><strong>' . 'General Users Usage' . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th class="SortedColumn">' . _('User') . '</th>
				<th class="SortedColumn">' . _('# Executions') . '</th>
				<th class="SortedColumn">' . _('Seconds Needed') . '</th>
				<th class="SortedColumn">' . _('Secs/Execution') . '</th>
			</tr>
		</thead>';
	
	echo '<tbody>';
	$TotalScripts = 0;
	$TotalSeconds = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$SecsPerExecution = $MyRow['numscripts'] > 0 ? $MyRow['sumseconds'] / $MyRow['numscripts'] : 0;
		echo '<tr class="striped_row">
				<td>' . $MyRow['userid'] . '</td>
				<td class="number">' . locale_number_format($MyRow['numscripts'], 0) . '</td>
				<td class="number">' . locale_number_format($MyRow['sumseconds'], 5) . '</td>
				<td class="number">' . locale_number_format($SecsPerExecution, 5) . '</td>
				</tr>';
		$TotalScripts += $MyRow['numscripts'];
		$TotalSeconds += $MyRow['sumseconds'];
	}
	echo '</tbody>
		<tfooter>';
	$AvgSecsPerExecution = $TotalScripts > 0 ? $TotalSeconds / $TotalScripts : 0;
	echo '<tr class="striped_row">
		<td>TOTALS</td>
		<td class="number">' . locale_number_format($TotalScripts, 0) . '</td>
		<td class="number">' . locale_number_format($TotalSeconds, 5) . '</td>
		<td class="number">' . locale_number_format($AvgSecsPerExecution, 5) . '</td>
		</tr>';
	echo '</tfooter>
		</table>
		</div>';

	/**************************************************************
	QUERY DETAILED
	***************************************************************/
	if ($_POST['DetailedReport'] == "Yes") {
		$SQL = "SELECT executiondate,
				userid,
				secondsrunning,
				scripttitle
			FROM auditscripts
			WHERE executiondate BETWEEN '" . $FromDate . "' AND '" . $ToDate . "'" 
			. $UserSQL
			. $ContainingText;

		$Result = DB_query($SQL);

		echo '<p class="page_title_text" align="center"><strong>' . 'Detailed Script usage' . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		echo '<thead>
				<tr>
					<th class="SortedColumn">' . _('Date/Time') . '</th>
					<th class="SortedColumn">' . _('User') . '</th>
					<th class="SortedColumn">' . _('Seconds') . '</th>
					<th class="SortedColumn">' . _('Script') . '</th>
				</tr>';
		echo '</thead>';
		echo '<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>' . $MyRow['executiondate'] . '</td>
					<td>' . $MyRow['userid'] . '</td>
					<td class="number">' . locale_number_format($MyRow['secondsrunning'], 5) . '</td>
					<td>' . $MyRow['scripttitle'] . '</td>
					</tr>';
		}
		echo '</tbody>';
		echo '</table></div>';
	}
}
include('includes/footer.php');

?>
