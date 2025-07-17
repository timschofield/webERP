<?php

include('includes/session.php');
$Title = _('Users currently logged in');
$ViewTopic = 'Setup';// Filename in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.

include('includes/header.php');


echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="' . _('Logged In Users') . '" alt="" />' . ' ' . $Title .
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
				<th class="SortedColumn">', _('Session'), '</th>
				<th class="SortedColumn">', _('User'), '</th>
				<th class="SortedColumn">', _('Name'), '</th>
				<th class="SortedColumn">', _('Email'), '</th>
				<th class="SortedColumn">', _('Phone'), '</th>
				<th class="SortedColumn">', _('Logged in'), '</th>
				<th class="SortedColumn">', _('Script name'), '</th>
				<th class="SortedColumn">', _('Script time'), '</th>
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
