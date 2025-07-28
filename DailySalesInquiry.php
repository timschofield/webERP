<?php

include('includes/session.php');
include('includes/DateFunctions.php');
use Dompdf\Dompdf;
$Title = _('Daily Sales Inquiry');
$ViewTopic = 'ARInquiries';
$BookMark = '';

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$_POST['MonthToShow'] = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$EndDateSQL = EndDateSQLFromPeriodNo($_POST['MonthToShow']);

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

	$SQL = "SELECT 	trandate,
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
	} elseif ($_POST['Salesperson']!='All') {
		$SQL .= " AND custbranch.salesman='" . $_POST['Salesperson'] . "'";
	}

	$SQL .= " GROUP BY stockmoves.trandate ORDER BY stockmoves.trandate";
	$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg();
	$SalesResult = DB_query($SQL,$ErrMsg);

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
				<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<img class="logo" src=' . $_SESSION['LogoFile'] . ' /><br />';
	}

	$HTML .= '<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . _('Daily Sales Inquiry') . '<br />
				' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				' . _('For The Month Of') . ' ' . MonthAndYearFromSQLDate($EndDateSQL) . '<br />
			</div>';


	$HTML .= '<table class="selection">
		<tr>
			<th colspan="7">' . _('Sales For The Month Of') . ' ' . MonthAndYearFromSQLDate($EndDateSQL) . '</th>
		</tr>
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
	$HTML .= '<tr>';
	$ColumnCounter = DayOfWeekFromSQLDate($StartDateSQL);
    $HTML .= str_repeat('<td></td>', $ColumnCounter);
	$DayNumber = 1;
	/*Set up day number headings*/
	for ($i=$ColumnCounter;$i<=6;$i++){
		$HTML .= '<th>' . $DayNumber . '</th>';
		$DayNumber++;
	}
	$HTML .= '</tr><tr>';
    $HTML .= str_repeat('<td></td>', $ColumnCounter);

	$LastDayOfMonth = DayOfMonthFromSQLDate($EndDateSQL);
	for ($i=1;$i<=$LastDayOfMonth;$i++){
		$ColumnCounter++;
		if(isset($DaySalesArray[$i])) {
			$HTML .= '<td class="number" style="outline: 1px solid gray;">' . locale_number_format($DaySalesArray[$i]['Sales'],0) . '<br />' .  locale_number_format($DaySalesArray[$i]['GPPercent']*100,1) . '%</td>';
		} else {
			$HTML .= '<td class="number" style="outline: 1px solid gray;">' . locale_number_format(0,0) . '<br />' .  locale_number_format(0,1) . '%</td>';
		}
		if ($ColumnCounter==7){
			$HTML .= '</tr><tr>';
						for ($j=1;$j<=7;$j++){
								   $HTML .= '<th>' . $DayNumber. '</th>';
							$DayNumber++;
							if($DayNumber>$LastDayOfMonth){
								   break;
							}
						}
						$HTML .= '</tr><tr>';
			$ColumnCounter=0;
		}
	}
if ($ColumnCounter!=0) {
	$HTML .= '</tr><tr>';
}

if ($CumulativeTotalSales !=0){
	$AverageGPPercent = ($CumulativeTotalSales - $CumulativeTotalCost)*100/$CumulativeTotalSales;
	$AverageDailySales = $CumulativeTotalSales/$BilledDays;
} else {
	$AverageGPPercent = 0;
	$AverageDailySales = 0;
}

$HTML .= '<th colspan="7">' . _('Total Sales for month') . ': ' . locale_number_format($CumulativeTotalSales,0) . ' ' . _('GP%') . ': ' . locale_number_format($AverageGPPercent,1) . '% ' . _('Avg Daily Sales') . ': ' . locale_number_format($AverageDailySales,0) . '</th></tr>';

$HTML .= '</table>';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_OrderStatus_' . date('Y-m-d') . '.pdf', array("Attachment" => false));
	} else {
		include('includes/header.php');

		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Daily Sales') . '" alt="" />' . ' ' . _('Daily Sales') . '
			</p>';
		$Title = _('Daily Sales Report');
		echo $HTML;
	}

} else { /*The option to print PDF was not hit so display form */
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Daily Sales') . '" alt="" />' . ' ' . _('Daily Sales') . '
		</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$_POST['MonthToShow'] = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	echo '<fieldset>
			<legend>', _('Select a Month'), '</legend>
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
		echo '<td>';
		echo $_SESSION['UsersRealName'];
		echo '</td>';
	} else {
		echo '<select tabindex="2" name="Salesperson">';

		$SalespeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman");
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
		echo '</select>';
	}
	echo '</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
			<input type="submit" name="View" title="View" value="' . _('View') . '" />
		</div>
	</form>';
}
include('includes/footer.php');
