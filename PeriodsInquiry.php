<?php

/* $Revision: 1.6 $ */

$PageSecurity = 2;

include ('includes/session.inc');

$title = _('Periods Inquiry');

include('includes/header.inc');

$SQL = "SELECT periodno ,
		lastdate_in_period
	FROM periods
	ORDER BY periodno";

$ErrMsg =  _('No periods were returned by the SQL because');
$PeriodsResult = DB_query($SQL,$db,$ErrMsg);


/*show a table of the orders returned by the SQL */

echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=2>';

$TableHeader = '<TR><TD class="tableheader">' . _('Period Number') . '</TD>
			<TD class="tableheader">' . _('Date of Last Day') . '</TD>
		</TR>';

echo $TableHeader;

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($PeriodsResult)) {
       if ($k==1){
              echo '<tr bgcolor="#CCCCCC">';
              $k=0;
       } else {
              echo '<tr bgcolor="#EEEEEE">';
              $k++;
       }

       $FormatedLastDate = ConvertSQLDate($myrow['lastdate_in_period']);
       printf("<td><FONT SIZE=2>%s</td>
		<td>%s</td>
		</tr>",
		$myrow['periodno'],
		$FormatedLastDate);

       $j++;
       If ($j == 12){
              $j=1;
              echo $TableHeader;
       }
}
//end of while loop

echo '</TABLE></CENTER>';

include('includes/footer.inc');
?>
