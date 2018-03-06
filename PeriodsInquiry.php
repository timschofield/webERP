<?php


include ('includes/session.php');

$Title = _('Periods Inquiry');

include('includes/header.php');

$SQL = "SELECT periodno ,
		lastdate_in_period
		FROM periods
		ORDER BY periodno";

$ErrMsg =  _('No periods were returned by the SQL because');
$PeriodsResult = DB_query($SQL,$ErrMsg);

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. $Title . '</p>';

/*show a table of the orders returned by the SQL */

$NumberOfPeriods = DB_num_rows($PeriodsResult);
$PeriodsInTable = round($NumberOfPeriods/3,0);

$TableHeader = '<tr><th>' . _('Period Number') . '</th>
					<th>' . _('Date of Last Day') . '</th>
				</tr>';

echo '<table><tr>';

for ($i=0;$i<3;$i++) {
	echo '<td valign="top">';
	echo '<table cellpadding="2" class="selection">';
	echo $TableHeader;
	$j=0;

	while ($myrow=DB_fetch_array($PeriodsResult)){
		echo '<tr class="striped_row">
				<td>' . $myrow['periodno'] . '</td>
			  <td>' . ConvertSQLDate($myrow['lastdate_in_period']) . '</td>
			</tr>';
		$j++;
		if ($j==$PeriodsInTable){
			break;
		}
	}
	echo '</table>';
	echo '</td>';
}

echo '</tr></table>';
//end of while loop

include('includes/footer.php');
?>
