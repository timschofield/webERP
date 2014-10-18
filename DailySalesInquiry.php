<?php

/* $Id: DailySalesInquiry.php 6458 2013-11-30 11:54:03Z exsonqu $*/

include('includes/session.inc');
$Title = _('Daily Sales Inquiry');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Daily Sales') . '" alt="" />' . ' ' . _('Daily Sales') . '</p>';
echo '<div class="page_help_text">' . _('Select the month to show daily sales for') . '</div>
	<br />';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['MonthToShow'])){
	$_POST['MonthToShow'] = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
	$Result = DB_query("SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['MonthToShow'] . "'",$db);
	$myrow = DB_fetch_array($Result);
	$EndDateSQL = $myrow['lastdate_in_period'];
}

echo '<table class="selection">
	<tr>
		<td>' . _('Month to Show') . ':</td>
		<td><select tabindex="1" name="MonthToShow">';

$PeriodsResult = DB_query("SELECT periodno, lastdate_in_period FROM periods",$db);

while ($PeriodRow = DB_fetch_array($PeriodsResult)){
	if ($_POST['MonthToShow']==$PeriodRow['periodno']) {
		echo '<option selected="selected" value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
		$EndDateSQL = $PeriodRow['lastdate_in_period'];
	} else {
		echo '<option value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
	}
}
echo '</select></td>
	<td>' . _('Salesperson') . ':</td>';

if($_SESSION['SalesmanLogin'] != '') {
	echo '<td>';
	echo $_SESSION['UsersRealName'];
	echo '</td>';
}else{
	echo '<td><select tabindex="2" name="Salesperson">';

	$SalespeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman",$db);
	if (!isset($_POST['Salesperson'])){
		$_POST['Salesperson'] = 'All';
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($SalespersonRow = DB_fetch_array($SalespeopleResult)){

	if ($_POST['Salesperson']==$SalespersonRow['salesmancode']) {
		echo '<option selected="selected" value="' . $SalespersonRow['salesmancode'] . '">' . $SalespersonRow['salesmanname'] . '</option>';
	} else {
		echo '<option value="' . $SalespersonRow['salesmancode'] . '">' . $SalespersonRow['salesmanname'] . '</option>';
	}
}
echo '</select></td>';
}
echo '</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="4" type="submit" name="ShowResults" value="' . _('Show Daily Sales For The Selected Month') . '" />
	</div>
    </div>
	</form>
	<br />';
/*Now get and display the sales data returned */
if (mb_strpos($EndDateSQL,'/')) {
	$Date_Array = explode('/',$EndDateSQL);
} elseif (mb_strpos ($EndDateSQL,'-')) {
	$Date_Array = explode('-',$EndDateSQL);
} elseif (mb_strpos ($EndDateSQL,'.')) {
	$Date_Array = explode('.',$EndDateSQL);
}

if (mb_strlen($Date_Array[2])>4) {
	$Date_Array[2]= mb_substr($Date_Array[2],0,2);
}

$StartDateSQL =  date('Y-m-d', mktime(0,0,0, (int)$Date_Array[1],1,(int)$Date_Array[0]));

$sql = "SELECT 	trandate,
				SUM(price*(1-discountpercent)* (-qty)) as salesvalue,
				SUM(CASE WHEN mbflag='A' THEN 0 ELSE (standardcost * -qty) END) as cost
			FROM stockmoves
			INNER JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			INNER JOIN custbranch
			ON stockmoves.debtorno=custbranch.debtorno
				AND stockmoves.branchcode=custbranch.branchcode
			WHERE (stockmoves.type=10 or stockmoves.type=11)
			AND trandate>='" . $StartDateSQL . "'
			AND trandate<='" . $EndDateSQL . "'";

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
}elseif ($_POST['Salesperson']!='All') {
	$sql .= " AND custbranch.salesman='" . $_POST['Salesperson'] . "'";
}

$sql .= " GROUP BY stockmoves.trandate ORDER BY stockmoves.trandate";
$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg($db);
$SalesResult = DB_query($sql, $db,$ErrMsg);

echo '<table class="selection">
	<tr>
		<th style="width: 14%">' . _('Sunday') . '</th>
		<th style="width: 14%">' . _('Monday') . '</th>
		<th style="width: 14%">' . _('Tuesday') . '</th>
		<th style="width: 14%">' . _('Wednesday') . '</th>
		<th style="width: 14%">' . _('Thursday') . '</th>
		<th style="width: 14%">' . _('Friday') . '</th>
		<th style="width: 14%">' . _('Saturday') . '</th>
	</tr>';

$CumulativeTotalSales = 0;
$CumulativeTotalCost = 0;
$BilledDays = 0;
$DaySalesArray = array();
while ($DaySalesRow=DB_fetch_array($SalesResult)) {

	if ($DaySalesRow['salesvalue'] > 0) {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['Sales'] = $DaySalesRow['salesvalue'];
	} else {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['Sales'] = 0;
	}
	if ($DaySalesRow['salesvalue'] > 0 ) {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['GPPercent'] = ($DaySalesRow['salesvalue']-$DaySalesRow['cost'])/$DaySalesRow['salesvalue'];
	} else {
		$DaySalesArray[DayOfMonthFromSQLDate($DaySalesRow['trandate'])]['GPPercent'] = 0;
	}
	$BilledDays++;
	$CumulativeTotalSales += $DaySalesRow['salesvalue'];
	$CumulativeTotalCost += $DaySalesRow['cost'];
}
//end of while loop
echo '<tr>';
$ColumnCounter = DayOfWeekFromSQLDate($StartDateSQL);
for ($i=0;$i<$ColumnCounter;$i++){
	echo '<td></td>';
}
$DayNumber = 1;
/*Set up day number headings*/
for ($i=$ColumnCounter;$i<=6;$i++){
	   echo '<th>' . $DayNumber . '</th>';
	   $DayNumber++;
}
echo '</tr><tr>';
for ($i=0;$i<$ColumnCounter;$i++){
	echo '<td></td>';
}

$LastDayOfMonth = DayOfMonthFromSQLDate($EndDateSQL);
for ($i=1;$i<=$LastDayOfMonth;$i++){
		$ColumnCounter++;
		if(isset($DaySalesArray[$i])) {
			echo '<td class="number" style="outline: 1px solid gray;">' . locale_number_format($DaySalesArray[$i]['Sales'],0) . '<br />' .  locale_number_format($DaySalesArray[$i]['GPPercent']*100,1) . '%</td>';
		} else {
			echo '<td class="number" style="outline: 1px solid gray;">' . locale_number_format(0,0) . '<br />' .  locale_number_format(0,1) . '%</td>';
		}
		if ($ColumnCounter==7){
			echo '</tr><tr>';
						for ($j=1;$j<=7;$j++){
								   echo '<th>' . $DayNumber. '</th>';
							$DayNumber++;
							if($DayNumber>$LastDayOfMonth){
								   break;
							}
						}
						echo '</tr><tr>';
			$ColumnCounter=0;
		}


}
if ($ColumnCounter!=0) {
	echo '</tr><tr>';
}

if ($CumulativeTotalSales !=0){
	$AverageGPPercent = ($CumulativeTotalSales - $CumulativeTotalCost)*100/$CumulativeTotalSales;
	$AverageDailySales = $CumulativeTotalSales/$BilledDays;
} else {
	$AverageGPPercent = 0;
	$AverageDailySales = 0;
}

echo '<th colspan="7">' . _('Total Sales for month') . ': ' . locale_number_format($CumulativeTotalSales,0) . ' ' . _('GP%') . ': ' . locale_number_format($AverageGPPercent,1) . '% ' . _('Avg Daily Sales') . ': ' . locale_number_format($AverageDailySales,0) . '</th></tr>';

echo '</table>';

include('includes/footer.inc');
?>
