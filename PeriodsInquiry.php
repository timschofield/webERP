<?php

/* $Revision: 1.8 $ */

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

echo '<table cellpadding=2 colspan=2>';

$TableHeader = '<tr><th>' . _('Period Number') . '</th>
			<th>' . _('Date of Last Day') . '</th>
		</tr>';

echo $TableHeader;

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($PeriodsResult)) {
       if ($k==1){
              echo '<tr class="EvenTableRows">';
              $k=0;
       } else {
              echo '<tr class="OddTableRows">';
              $k++;
       }

       $FormatedLastDate = ConvertSQLDate($myrow['lastdate_in_period']);
       printf("<td><font size=2>%s</td>
		<td>%s</td>
		</tr>",
		$myrow['periodno'],
		$FormatedLastDate);

}
//end of while loop

echo '</table>';

include('includes/footer.inc');
?>
