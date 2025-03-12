<?php


include ('includes/session.php');

$Title = _('GL Codes Inquiry');

$ViewTopic = 'GeneralLedger';
$BookMark = '';

include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Print Invoices or Credit Notes (Portrait Mode)') . '
	</p>';

$SQL = "SELECT group_,
		accountcode ,
		accountname
		FROM chartmaster INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
		ORDER BY sequenceintb,
				accountcode";

$ErrMsg = _('No general ledger accounts were returned by the SQL because');
$AccountsResult = DB_query($SQL,$ErrMsg);

/*show a table of the orders returned by the SQL */

echo '<table cellpadding="2">
		<thead style="position: -webkit-sticky; position: sticky; top: 0px; z-index: 100;">
			<tr>
				<th>' . _('Group') . '</th>
				<th>' . _('Code') . '</th>
				<th>' . _('Account Name') . '</th>
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
?>