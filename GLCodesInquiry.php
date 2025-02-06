<?php


include ('includes/session.php');

$Title = _('GL Codes Inquiry');

$ViewTopic = 'GeneralLedger';
$BookMark = '';

include('includes/header.php');

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
		<tr>
			<th><h3>' . _('Group') . '</h3></th>
			<th><h3>' . _('Code') . '</h3></th>
			<th><h3>' . _('Account Name') . '</h3></th>
		</tr>';

$j = 1;
$ActGrp ='';

while ($MyRow=DB_fetch_array($AccountsResult)) {

       if ($MyRow['group_']== $ActGrp) {
              printf('<tr class="striped_row">
					<td></td>
	      		      <td>%s</td>
			          <td>%s</td>
			          </tr>',
			  $MyRow['accountcode'],
			  htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false));
       } else {
              $ActGrp = $MyRow['group_'];
              printf('<tr class="striped_row">
					<td><b>%s</b></td>
	      		      <td>%s</td>
			          <td>%s</td>
			          </tr>',
			  $MyRow['group_'],
			  $MyRow['accountcode'],
			  htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false));
       }
}
//end of while loop

echo '</table>';
include('includes/footer.php');
?>