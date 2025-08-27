<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Users currently logged in');
$ViewTopic = 'Setup';// Filename in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="' . __('Logged In Users') . '" alt="" />' . ' ' . $Title .
	'</p>';

$SQL = "SELECT sessionid,
				sessions.userid,
				logintime,
				realname,
				email,
				phone,
				scripttime,
				script
			FROM sessions
			INNER JOIN www_users
			ON www_users.userid = sessions.userid";
$Result = DB_query($SQL);

echo '<table>
		<thead>
			<tr>
				<th class="SortedColumn">', __('Session'), '</th>
				<th class="SortedColumn">', __('User'), '</th>
				<th class="SortedColumn">', __('Name'), '</th>
				<th class="SortedColumn">', __('Email'), '</th>
				<th class="SortedColumn">', __('Phone'), '</th>
				<th class="SortedColumn">', __('Logged in'), '</th>
				<th class="SortedColumn">', __('Script name'), '</th>
				<th class="SortedColumn">', __('Script time'), '</th>
			</tr>
		</thead>';

echo '<tbody>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr class="striped_row">
			<td>', $MyRow['sessionid'], '</td>
			<td>', $MyRow['userid'], '</td>
			<td>', $MyRow['realname'], '</td>
			<td>', $MyRow['email'], '</td>
			<td>', $MyRow['phone'], '</td>
			<td class="date">', ConvertSQLDateTime($MyRow['logintime']), '</td>
			<td>', $MyRow['script'], '</td>
			<td class="date">', ConvertSQLDateTime($MyRow['scripttime']), '</td>
		</tr>';
}
echo '</tbody>
	</table>';

include('includes/footer.php');
