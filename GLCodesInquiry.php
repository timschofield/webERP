<?php

/* $Revision: 1.7 $ */

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

echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=2>
		<TR>
			<TD class='tableheader'>"._('Group')."</FONT></TD>
			<TD class='tableheader'>"._('Code')."</FONT></TD>
			<TD class='tableheader'>"._('Account Name').'</FONT></TD>
		</TR>';

$j = 1;
$k=0; //row colour counter
$ActGrp ='';

while ($myrow=DB_fetch_array($AccountsResult)) {
       if ($k==1){
              echo "<tr bgcolor='#CCCCCC'>";
              $k=0;
       } else {
              echo "<tr bgcolor='#EEEEEE'>";
              $k++;
       }

       if ($myrow['group_']== $ActGrp){
              printf("<td></td>
	      		<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			</tr>",
			$myrow['accountcode'],
			$myrow['accountname']);
       } else {
              $ActGrp = $myrow['group_'];
              printf("<td><FONT SIZE=2>%s</FONT></td>
	      		<td><FONT SIZE=2>%s</FONT></td>
			<td><FONT SIZE=2>%s</FONT></td>
			</tr>",
			$myrow['group_'],
			$myrow['accountcode'],
			$myrow['accountname']);
       }
       $j++;
       If ($j == 18){
              $j=1;
              echo "<TR><TD class='tableheader'>"._('Group')."</FONT></TD>
	      		<TD class='tableheader'>"._('Code')."</FONT></TD>
			<TD class='tableheader'>"._('Account Name').'</FONT></TD></TR>';
       }
}
//end of while loop

echo '</TABLE></CENTER>';

include('includes/footer.inc');

?>
