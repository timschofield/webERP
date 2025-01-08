<?php

/*************************************************************************************************************************
 * 
 * KL RICARD: Change the SQL to use salesorders table to filter by SPG correctly
 *			Added code do salesman in dropdown 			
 *			Filter by Current salesman = 1
 *			No one needs to know the GP% :-)
 *			
 *  
 ************************************************************************************************************************ */

include('includes/session.php');
$Title = _('KL Daily Sales Inquiry');
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Daily Sales') . '" alt="" />' . ' ' . _('Daily Sales') . '</p>';
echo '<div class="page_help_text">' . _('Select the month to show daily sales for') . '</div>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['MonthToShow'])){
	$_POST['MonthToShow'] = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$Result = DB_query("SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['MonthToShow'] . "'");
	$MyRow = DB_fetch_array($Result);
	$EndDateSQL = $MyRow['lastdate_in_period'];
}

echo '<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="MonthToShow">' . _('Month to Show') . ':</label>
			<select tabindex="1" name="MonthToShow">';

$PeriodsResult = DB_query("SELECT periodno, lastdate_in_period FROM periods");

while ($PeriodRow = DB_fetch_array($PeriodsResult)){
	if ($_POST['MonthToShow']==$PeriodRow['periodno']) {
		echo '<option selected="selected" value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
		$EndDateSQL = $PeriodRow['lastdate_in_period'];
	} else {
		echo '<option value="' . $PeriodRow['periodno'] . '">' . MonthAndYearFromSQLDate($PeriodRow['lastdate_in_period']) . '</option>';
	}
}
echo '</select>
	<field>';

echo '<field>
		<label for="Salesperson">' . _('Salesperson') . ':</label>';

if($_SESSION['SalesmanLogin'] != '') {
	echo '<span>';
	echo $_SESSION['UsersRealName'];
	echo '</span>';
}else{
	echo '<select tabindex="2" name="Salesperson">';
// KL RICARD Filter by Current = 1
	$SalespeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current = 1 ORDER BY salesmancode");
	if (!isset($_POST['Salesperson'])){
		$_POST['Salesperson'] = 'All';
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($SalespersonRow = DB_fetch_array($SalespeopleResult)){

		if ($_POST['Salesperson']==$SalespersonRow['salesmancode']) {
			echo '<option selected="selected" value="' . $SalespersonRow['salesmancode'] . '">' . $SalespersonRow['salesmancode'] . '-' . $SalespersonRow['salesmanname'] . '</option>';
		} else {
			echo '<option value="' . $SalespersonRow['salesmancode'] . '">' . $SalespersonRow['salesmancode'] . '-' . $SalespersonRow['salesmanname'] . '</option>';
		}
	}
echo '</select>';
}

echo '</field>';

echo '<field>
		<label for="CustomerType">' . _('Customer Type') . ':</label>
		<select tabindex="3" name="CustomerType">';
$CustomerTypeResult = DB_query("SELECT typename, typeid FROM debtortype ORDER BY typename");
if (!isset($_POST['CustomerType'])){
	$_POST['CustomerType'] = 'All';
	echo '<option selected="selected" value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($CustomerTypeRow = DB_fetch_array($CustomerTypeResult)){

	if ($_POST['CustomerType']==$CustomerTypeRow['typeid']) {
		echo '<option selected="selected" value="' . $CustomerTypeRow['typeid'] . '">' . $CustomerTypeRow['typename'] . '</option>';
	} else {
		echo '<option value="' . $CustomerTypeRow['typeid'] . '">' . $CustomerTypeRow['typename'] . '</option>';
	}
}
echo '</select>
	</field>';


echo '</field>
	</fieldset>
	<div class="centre">
		<input tabindex="4" type="submit" name="ShowResults" value="' . _('Show Daily Sales For The Selected Month') . '" />
    </div>
	</form>';
	
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

/* KL RICARD Change the SQL to use salesorders table to filter by SPG correctly*/
$SQL = "SELECT 	orddate AS trandate,
				SUM(unitprice*(1-discountpercent)* (qtyinvoiced) / currencies.rate) as salesvalue,
				SUM(CASE WHEN mbflag='A' THEN 0 ELSE ((actualcost) * qtyinvoiced) END) as cost
			FROM salesorders
			INNER JOIN salesorderdetails ON salesorders.orderno=salesorderdetails.orderno
			INNER JOIN stockmaster ON stockmaster.stockid=salesorderdetails.stkcode
			INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN debtortype ON debtorsmaster.typeid = debtortype.typeid
			INNER JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
			WHERE orddate>='" . $StartDateSQL . "'
			AND orddate<='" . $EndDateSQL . "'";
			
if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}elseif ($_POST['Salesperson']!='All') {
	$SQL .= " AND salesorders.salesperson='" . $_POST['Salesperson'] . "'";
}

if ($_POST['CustomerType']!='All') {
	$SQL .= " AND debtorsmaster.typeid='" . $_POST['CustomerType'] . "'";
}
$SQL .= " GROUP BY salesorders.orddate ORDER BY salesorders.orddate";

$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg();
$SalesResult = DB_query($SQL,$ErrMsg);

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

//echo '<th colspan="7">' . _('Total Sales for month') . ': ' . locale_number_format($CumulativeTotalSales,0) . ' ' . _('GP%') . ': ' . locale_number_format($AverageGPPercent,1) . '% ' . _('Avg Daily Sales') . ': ' . locale_number_format($AverageDailySales,0) . '</th></tr>';
// KL RICARD No one needs to know the GP% :-)
echo '<th colspan="7">' . _('Total Sales for month') . ': ' . locale_number_format($CumulativeTotalSales,0) . ' ' . _('Avg Daily Sales') . ': ' . locale_number_format($AverageDailySales,0) . '</th></tr>';

echo '</table>';

include('includes/footer.php');
?>
