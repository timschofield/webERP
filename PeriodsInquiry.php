<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Periods Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = '';
include('includes/header.php');

$SQL = "SELECT periodno ,
		lastdate_in_period
		FROM periods
		ORDER BY periodno";

$ErrMsg =  __('No periods were returned by the SQL because');
$PeriodsResult = DB_query($SQL, $ErrMsg);

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. $Title . '</p>';

/*show a table of the orders returned by the SQL */

$NumberOfPeriods = DB_num_rows($PeriodsResult);
$PeriodsInTable = round($NumberOfPeriods/3,0);

$TableHeader = '<tr><th>' . __('Period Number') . '</th>
					<th>' . __('Date of Last Day') . '</th>
				</tr>';

echo '<table><tr>';

for ($i=0;$i<3;$i++) {
	echo '<td valign="top">';
	echo '<table cellpadding="2" class="selection">';
	echo $TableHeader;
	$j=0;

	while ($MyRow=DB_fetch_array($PeriodsResult)){
		echo '<tr class="striped_row">
				<td>' . $MyRow['periodno'] . '</td>
			  <td>' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</td>
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
