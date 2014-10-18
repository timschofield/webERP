<?php

/* $Id: GLCodesInquiry.php 6112 2013-07-19 10:07:00Z daintree $*/

include ('includes/session.inc');

$Title = _('GL Codes Inquiry');

include('includes/header.inc');

$SQL = "SELECT group_,
		accountcode ,
		accountname
		FROM chartmaster INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
		ORDER BY sequenceintb,
				accountcode";

$ErrMsg = _('No general ledger accounts were returned by the SQL because');
$AccountsResult = DB_query($SQL,$db,$ErrMsg);

/*show a table of the orders returned by the SQL */

echo '<table cellpadding="2">
		<tr>
			<th><h3>' . _('Group') . '</h3></th>
			<th><h3>' . _('Code') . '</h3></th>
			<th><h3>' . _('Account Name') . '</h3></th>
		</tr>';

$j = 1;
$k=0; //row colour counter
$ActGrp ='';

while ($myrow=DB_fetch_array($AccountsResult)) {
       if ($k==1){
              echo '<tr class="EvenTableRows">';
              $k=0;
       } else {
              echo '<tr class="OddTableRows">';
              $k++;
       }

       if ($myrow['group_']== $ActGrp){
              printf('<td></td>
	      		      <td>%s</td>
			          <td>%s</td>
			          </tr>',
			  $myrow['accountcode'],
			  htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false));
       } else {
              $ActGrp = $myrow['group_'];
              printf('<td><b>%s</b></td>
	      		      <td>%s</td>
			          <td>%s</td>
			          </tr>',
			  $myrow['group_'],
			  $myrow['accountcode'],
			  htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false));
       }
}
//end of while loop

echo '</table>';
include('includes/footer.inc');
?>