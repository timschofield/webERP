<?php

require(__DIR__ . '/includes/session.php');

$Title = __('GL Codes Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . __('Print Invoices or Credit Notes (Portrait Mode)') . '
	</p>';

$SQL = "SELECT group_,
		accountcode ,
		accountname
		FROM chartmaster INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
		ORDER BY sequenceintb,
				accountcode";

$ErrMsg = __('No general ledger accounts were returned by the SQL because');
$AccountsResult = DB_query($SQL, $ErrMsg);

/*show a table of the orders returned by the SQL */

echo '<table cellpadding="2">
		<thead style="position: -webkit-sticky; position: sticky; top: 0px; z-index: 100;">
			<tr>
				<th>' . __('Group') . '</th>
				<th>' . __('Code') . '</th>
				<th>' . __('Account Name') . '</th>
			</tr>
		</thead>';

$j = 1;
$ActGrp ='';

while ($MyRow=DB_fetch_array($AccountsResult)) {

	   if ($MyRow['group_']== $ActGrp) {
			echo '<tr class="striped_row">
					<td></td>
		  			<td>', $MyRow['accountcode'], '</td>
					<td>', htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false), '</td>
				</tr>';
	   } else {
			$ActGrp = $MyRow['group_'];
			echo '<tr class="striped_row">
					<td><b>', $MyRow['group_'], '</b></td>
		  			<td>', $MyRow['accountcode'], '</td>
					<td>', htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false), '</td>
				</tr>';
	   }
}
//end of while loop

echo '</table>';
include('includes/footer.php');
