<?php

/* $Revision: 1.9 $ */

$PageSecurity = 8;
include ('includes/session.inc');

$title = _('GL Codes Inquiry');

include('includes/header.inc');

$SQL = 'SELECT group_,
		accountcode ,
		accountname
	FROM chartmaster INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
	ORDER BY sequenceintb,
		accountcode';

$ErrMsg = _('No general ledger accounts were returned by the SQL because');
$AccountsResult = DB_query($SQL,$db,$ErrMsg);

/*show a table of the orders returned by the SQL */

echo "<table cellpadding=2 colspan=2>
		<tr>
			<th>"._('Group')."</font></th>
			<th>"._('Code')."</font></th>
			<th>"._('Account Name').'</font></th>
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
              printf("<td></td>
	      		<td><font size=2>%s</font></td>
			<td><font size=2>%s</font></td>
			</tr>",
			$myrow['accountcode'],
			$myrow['accountname']);
       } else {
              $ActGrp = $myrow['group_'];
              printf("<td><font size=2>%s</font></td>
	      		<td><font size=2>%s</font></td>
			<td><font size=2>%s</font></td>
			</tr>",
			$myrow['group_'],
			$myrow['accountcode'],
			$myrow['accountname']);
       }
}
//end of while loop

echo '</table>';

include('includes/footer.inc');

?>
