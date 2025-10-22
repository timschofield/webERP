<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Maintain journal templates');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLJournals';
include('includes/header.php');

if (isset($_GET['delete'])) {
	// Delete the lines
	$SQL = "DELETE FROM jnltmpldetails WHERE templateid='" . $_GET['delete'] . "'";
	$Result = DB_query($SQL);

	// Delete the lines
	$SQL = "DELETE FROM jnltmplheader WHERE templateid='" . $_GET['delete'] . "'";
	$Result = DB_query($SQL);

	prnMsg(__('The GL journal template has been removed from the database'), 'success');
}

$SQL = "SELECT templateid,
				templatedescription,
				journaltype
			FROM jnltmplheader";
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	prnMsg(__('There are no templates stored in the database.'), 'warn');
} else {
	echo '<p class="page_title_text" >
			<img class="page_title_icon" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="" alt="" />', ' ', __('Maintain journal templates'), '
		</p>';

	echo '<table>
			<tr>
				<th colspan="4">', __('Available journal templates'), '</th>
			</tr>
			<tr>
				<th>', __('Template ID'), '</th>
				<th>', __('Template Description'), '</th>
				<th>', __('Journal Type'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['journaltype'] == 0) {
			$JournalType = __('Normal');
		} else {
			$JournalType = __('Reversing');
		}
		echo '<tr class="striped_row">
				<td>', $MyRow['templateid'], '</td>
				<td>', $MyRow['templatedescription'], '</td>
				<td>', $JournalType, '</td>
				<td class="noPrint"><a href="', basename(__FILE__), '?delete=', urlencode($MyRow['templateid']), '" onclick="return MakeConfirm(\'' . __('Are you sure you wish to delete this template?') . '\', \'Confirm Delete\', this);">', __('Delete'), '</a></td>
			</tr>';
	}

	echo '</table>';
	include('includes/footer.php');
	exit();
}

include('includes/footer.php');
