<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Sales Report');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Sales Report') . '" alt="" />' . ' ' . __('Sales Report') . '</p>';
echo '<div class="page_help_text">' . __('Select the parameters for the report') . '</div><br />';

if (!isset($_POST['DisplayData'])){
	/* then assume to display daily - maybe wrong to do this but hey better than reporting an error?*/
	$_POST['DisplayData']='Weekly';
}
if (!isset($_POST['DateRange'])){
	/* then assume report is for This Month - maybe wrong to do this but hey better than reporting an error?*/
	$_POST['DateRange']='ThisMonth';
}

echo '<form id="Form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>';

echo '<legend>' . __('Date Selection') . '</legend>';

echo '<field>
		<label for="DateRange">' . __('Custom Range') . ':</label>
		<input type="radio" name="DateRange" value="Custom" ';
if ($_POST['DateRange']=='Custom'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(Form1.ShowSales)"/>
		</field>';

echo '<field>
		<label for="DateRange">' . __('This Week') . ':</label>
		<input type="radio" name="DateRange" value="ThisWeek" ';
if ($_POST['DateRange']=='ThisWeek'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(Form1.ShowSales)" />
		</field>';

echo '<field>
		<label for="DateRange">' . __('This Month') . ':</label>
		<input type="radio" name="DateRange" value="ThisMonth" ';
if ($_POST['DateRange']=='ThisMonth'){
	echo 'checked="checked"';
}
echo ' onchange="ReloadForm(Form1.ShowSales)" />
		</field>';

echo '<field>
		<label for="ThisQuarter">' . __('This Quarter') . ':</label>
		<input type="radio" name="DateRange" value="ThisQuarter" ';
if ($_POST['DateRange']=='ThisQuarter'){
	echo 'checked="checked"';
}
echo ' onchange="ReloadForm(Form1.ShowSales)" />
	</field>';

if ($_POST['DateRange']=='Custom'){
	if (!isset($_POST['ToDate'])){
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'],mktime(1,1,1,Date('m')-12,Date('d')+1,Date('Y')));
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	echo '<field>
			<label for="FromDate">' . __('Date From') . ':</label>
			<input type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
		</field>';
	echo '<field>
			<label for="ToDate">' . __('Date To') . ':</label>
			<input type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>';
}
echo '</fieldset>';

echo '<fieldset>
		<legend>' . __('Display Data') . '</legend>';

echo '<field>
		<label for="DisplayData">' . __('Daily') . ':</label>
		<input type="radio" name="DisplayData" value="Daily" ';
if ($_POST['DisplayData']=='Daily'){
	echo 'checked="checked"';
}
echo ' onchange="ReloadForm(Form1.ShowSales)" />
		</field>';

echo '<field>
		<label for="DisplayData">' . __('Weekly') . ':</label>
		<input type="radio" name="DisplayData" value="Weekly" ';
if ($_POST['DisplayData']=='Weekly'){
	echo 'checked="checked"';
}
echo ' onchange="ReloadForm(Form1.ShowSales)" />
	</field>';

echo '<field>
		<label for="DisplayData">' . __('Monthly') . ':</label>
		<input type="radio" name="DisplayData" value="Monthly" ';
if ($_POST['DisplayData']=='Monthly'){
	echo 'checked="checked"';
}
echo ' onchange="ReloadForm(Form1.ShowSales)" />
	</field>';

echo '<field>
		<label for="DisplayData">' . __('Quarterly') . ':</label>
		<input type="radio" name="DisplayData" value="Quarterly" ';
if ($_POST['DisplayData']=='Quarterly'){
	echo 'checked="checked"';
}
echo	' onchange="ReloadForm(Form1.ShowSales)" />
		</field>';
echo '</fieldset>';

echo '<div class="centre">
		<input tabindex="4" type="submit" name="ShowSales" value="' . __('Show Sales') . '" />
	</div>
</form>';

if ($_POST['DateRange']=='Custom' AND !isset($_POST['FromDate']) AND !isset($_POST['ToDate'])){
	//Don't run the report until custom dates entered
	unset($_POST['ShowSales']);
}

if (isset($_POST['ShowSales'])){
	$InputError=0; //assume no input errors now test for errors
	if ($_POST['DateRange']=='Custom'){
		if (!Is_Date($_POST['FromDate'])){
			$InputError = 1;
			prnMsg(__('The date entered for the from date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		}
		if (!Is_Date($_POST['ToDate'])){
			$InputError = 1;
			prnMsg(__('The date entered for the to date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		}
		if (Date1GreaterThanDate2($_POST['FromDate'],$_POST['ToDate'])){
			$InputError = 1;
			prnMsg(__('The from date is expected to be a date prior to the to date. Please review the selected date range'),'error');
		}
	}
	switch ($_POST['DateRange']) {
		case 'ThisWeek':
			$FromDate = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'ThisMonth':
			$FromDate = date('Y-m-d',mktime(0,0,0,date('m'),1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'ThisQuarter':
			switch (date('m')) {
				case 1:
				case 2:
				case 3:
					$QuarterStartMonth=1;
					break;
				case 4:
				case 5:
				case 6:
					$QuarterStartMonth=4;
					break;
				case 7:
				case 8:
				case 9:
					$QuarterStartMonth=7;
					break;
				default:
					$QuarterStartMonth=10;
			}
			$FromDate = date('Y-m-d',mktime(0,0,0,$QuarterStartMonth,1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'Custom':
			$FromDate = FormatDateForSQL($_POST['FromDate']);
			$ToDate = FormatDateForSQL($_POST['ToDate']);
	}
	switch ($_POST['DisplayData']) {
		case 'Daily':
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.tpe,
						SUM(CASE WHEN stockmoves.type=10 THEN
							price*(1-discountpercent)* -qty
							ELSE 0 END)
						 as salesvalue,
						 SUM(CASE WHEN stockmoves.type=10 THEN
							1 ELSE 0 END)
						 as nooforders,
						 SUM(CASE WHEN stockmoves.type=11 THEN
							price*(1-discountpercent)* (-qty)
							ELSE 0 END)
						 as returnvalue,
						SUM((standardcost * -qty)) as cost
					FROM stockmoves
					INNER JOIN custbranch
					ON stockmoves.debtorno=custbranch.debtorno
					AND stockmoves.branchcode=custbranch.branchcode
					INNER JOIN debtortrans
					ON stockmoves.type=debtortrans.type
					AND stockmoves.transno=debtortrans.transno
					WHERE (stockmoves.type=10 or stockmoves.type=11)
					AND show_on_inv_crds =1
					AND debtortrans.trandate>='" . $FromDate . "'
					AND debtortrans.trandate<='" . $ToDate . "'";

			if ($_SESSION['SalesmanLogin'] != '') {
				$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$SQL .= " GROUP BY debtortrans.trandate,
							tpe
					ORDER BY debtortrans.trandate,
							tpe";

			break;
		case 'Weekly':
			$SQL = "SELECT WEEKOFYEAR(debtortrans.trandate) as week_no,
							YEAR(debtortrans.trandate) as transyear,
							debtortrans.tpe,
						SUM(CASE WHEN stockmoves.type=10 THEN
							price*(1-discountpercent)* -qty
							ELSE 0 END)
						 as salesvalue,
						 SUM(CASE WHEN stockmoves.type=10 THEN
							1 ELSE 0 END)
						 as nooforders,
						 SUM(CASE WHEN stockmoves.type=11 THEN
							price*(1-discountpercent)* (-qty)
							ELSE 0 END)
						as returnvalue,
						SUM((standardcost * -qty)) as cost
					FROM stockmoves
					INNER JOIN custbranch
					ON stockmoves.debtorno=custbranch.debtorno
					AND stockmoves.branchcode=custbranch.branchcode
					INNER JOIN debtortrans
					ON stockmoves.type=debtortrans.type
					AND stockmoves.transno=debtortrans.transno
					WHERE (stockmoves.type=10 or stockmoves.type=11)
					AND show_on_inv_crds =1
					AND debtortrans.trandate>='" . $FromDate . "'
					AND debtortrans.trandate<='" . $ToDate . "'";

			if ($_SESSION['SalesmanLogin'] != '') {
				$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$SQL .= " GROUP BY week_no,
							transyear,
							tpe
					ORDER BY transyear,
							week_no,
							tpe";

			break;
		case 'Monthly':
			$SQL = "SELECT MONTH(debtortrans.trandate) as month_no,
							MONTHNAME(debtortrans.trandate) as month_name,
							YEAR(debtortrans.trandate) as transyear,
							debtortrans.tpe,
						SUM(CASE WHEN stockmoves.type=10 THEN
							price*(1-discountpercent)* -qty
							ELSE 0 END)
						 as salesvalue,
						 SUM(CASE WHEN stockmoves.type=10 THEN
							1 ELSE 0 END)
						 as nooforders,
						 SUM(CASE WHEN stockmoves.type=11 THEN
							price*(1-discountpercent)* (-qty)
							ELSE 0 END)
						as returnvalue,
						SUM((standardcost * -qty)) as cost
					FROM stockmoves
					INNER JOIN custbranch
					ON stockmoves.debtorno=custbranch.debtorno
					AND stockmoves.branchcode=custbranch.branchcode
					INNER JOIN debtortrans
					ON stockmoves.type=debtortrans.type
					AND stockmoves.transno=debtortrans.transno
					WHERE (stockmoves.type=10 or stockmoves.type=11)
					AND show_on_inv_crds =1
					AND debtortrans.trandate>='" . $FromDate . "'
					AND debtortrans.trandate<='" . $ToDate . "'";

			if ($_SESSION['SalesmanLogin'] != '') {
				$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$SQL .= " GROUP BY month_no,
							month_name,
							transyear,
							debtortrans.tpe
					ORDER BY transyear,
							month_no,
							tpe";

			break;
		case 'Quarterly':
			$SQL = "SELECT QUARTER(debtortrans.trandate) as quarter_no,
							YEAR(debtortrans.trandate) as transyear,
							debtortrans.tpe,
						SUM(CASE WHEN stockmoves.type=10 THEN
							price*(1-discountpercent)* -qty
							ELSE 0 END)
						 as salesvalue,
						 SUM(CASE WHEN stockmoves.type=10 THEN
							1 ELSE 0 END)
						 as nooforders,
						 SUM(CASE WHEN stockmoves.type=11 THEN
							price*(1-discountpercent)* (-qty)
							ELSE 0 END)
						as returnvalue,
						SUM((standardcost * -qty)) as cost
					FROM stockmoves
					INNER JOIN custbranch
					ON stockmoves.debtorno=custbranch.debtorno
					AND stockmoves.branchcode=custbranch.branchcode
					INNER JOIN debtortrans
					ON stockmoves.type=debtortrans.type
					AND stockmoves.transno=debtortrans.transno
					WHERE (stockmoves.type=10 or stockmoves.type=11)
					AND show_on_inv_crds =1
					AND debtortrans.trandate>='" . $FromDate . "'
					AND debtortrans.trandate<='" . $ToDate . "'";

			if ($_SESSION['SalesmanLogin'] != '') {
				$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
			}

			$SQL .= " GROUP BY quarter_no,
							transyear,
							tpe
					ORDER BY transyear,
							quarter_no,
							tpe";

			break;
		}

	$ErrMsg = __('The sales data could not be retrieved because') . ' - ' . DB_error_msg();
	$SalesResult = DB_query($SQL, $ErrMsg);


	echo '<table cellpadding="2" class="selection">';

	echo'<tr>
		<th>' . __('Period') . '</th>
		<th>' . __('Sales') . '<br />' . __('Type') . '</th>
		<th>' . __('No Orders') . '</th>
		<th>' . __('Total Sales') . '</th>
		<th>' . __('Refunds') . '</th>
		<th>' . __('Net Sales') . '</th>
		<th>' . __('Cost of Sales') . '</th>
		<th>' . __('Gross Profit') . '</th>
		</tr>';

	$CumulativeTotalSales = 0;
	$CumulativeTotalOrders = 0;
	$CumulativeTotalRefunds = 0;
	$CumulativeTotalNetSales = 0;
	$CumulativeTotalCost = 0;
	$CumulativeTotalGP = 0;

	$PrdTotalOrders =0;
	$PrdTotalSales=0;
	$PrdTotalRefunds=0;
	$PrdTotalNetSales=0;
	$PrdTotalCost=0;
	$PrdTotalGP=0;

	$PeriodHeadingDone = false;
	$LastPeriodHeading = 'First Run Through';

	while ($SalesRow=DB_fetch_array($SalesResult)) {
		echo '<tr class="striped_row">';

		switch ($_POST['DisplayData']){
			case 'Daily':
				if ($LastPeriodHeading != ConvertSQLDate($SalesRow['trandate'])) {
					$PeriodHeadingDone=false;
					if ($LastPeriodHeading != 'First Run Through'){ //print the footer for the period
						echo '<td colspan="2" class="number">' . __('Total') . '-' . $LastPeriodHeading . '</td>
							<td class="number">' . $PrdTotalOrders . '</td>
							<td class="number">' . locale_number_format($PrdTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalGP,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

						echo '<tr class="striped_row"><td colspan="8"><hr /></td></tr>';
						echo '<tr class="striped_row">';

						$PrdTotalOrders =0;
						$PrdTotalSales=0;
						$PrdTotalRefunds=0;
						$PrdTotalNetSales=0;
						$PrdTotalCost=0;
						$PrdTotalGP=0;
					}
				}
				if (! $PeriodHeadingDone){
					echo '<td>' . ConvertSQLDate($SalesRow['trandate']) . '</td>';
					$LastPeriodHeading = ConvertSQLDate($SalesRow['trandate']);
					$PeriodHeadingDone = true;
				} else {
					echo '<td></td>';
				}
				break;
			case 'Weekly':
				if ($LastPeriodHeading != __('wk'). '-' . $SalesRow['week_no'] . ' ' . $SalesRow['transyear']) {
					$PeriodHeadingDone=false;
					if ($LastPeriodHeading != 'First Run Through'){
						echo '<td colspan="2" class="number">' . __('Total') . '-' . $LastPeriodHeading . '</td>
							<td class="number">' . $PrdTotalOrders . '</td>
							<td class="number">' . locale_number_format($PrdTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalGP,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

						echo '<tr class="striped_row"><td colspan="8"><hr /></td></tr>';
						echo '<tr class="striped_row">';

						$PrdTotalOrders =0;
						$PrdTotalSales=0;
						$PrdTotalRefunds=0;
						$PrdTotalNetSales=0;
						$PrdTotalCost=0;
						$PrdTotalGP=0;
					}
				}
				if (! $PeriodHeadingDone){
					echo '<td>' . __('wk'). '-' . $SalesRow['week_no'] . ' ' . $SalesRow['transyear'] . '</td>';
					$LastPeriodHeading = __('wk'). '-' . $SalesRow['week_no'] . ' ' . $SalesRow['transyear'];
					$PeriodHeadingDone = true;
				} else {
					echo '<td></td>';
				}
				break;
			case 'Monthly':
				if ($LastPeriodHeading != $SalesRow['month_name'] . ' ' . $SalesRow['transyear']) {
					$PeriodHeadingDone=false;
					if ($LastPeriodHeading != 'First Run Through'){
						echo '<td colspan="2" class="number">' . __('Total') . '-' . $LastPeriodHeading . '</td>
							<td class="number">' . $PrdTotalOrders . '</td>
							<td class="number">' . locale_number_format($PrdTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalGP,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

						echo '<tr class="striped_row"><td colspan="8"><hr /></td></tr>';
						echo '<tr class="striped_row">';

						$PrdTotalOrders =0;
						$PrdTotalSales=0;
						$PrdTotalRefunds=0;
						$PrdTotalNetSales=0;
						$PrdTotalCost=0;
						$PrdTotalGP=0;
					}
				}
				if (! $PeriodHeadingDone){
					echo '<td>' . $SalesRow['month_name'] . ' ' . $SalesRow['transyear'] . '</td>';
					$LastPeriodHeading = $SalesRow['month_name'] . ' ' . $SalesRow['transyear'];
					$PeriodHeadingDone = true;
				} else {
					echo '<td></td>';
				}
				break;
			case 'Quarterly':
				if ($LastPeriodHeading != __('Qtr'). '-' . $SalesRow['quarter_no'] . ' ' . $SalesRow['transyear']) {
					$PeriodHeadingDone=false;
					if ($LastPeriodHeading != 'First Run Through'){
						echo '<td colspan="2" class="number">' . __('Total') . '-'. $LastPeriodHeading . '</td>
							<td class="number">' . $PrdTotalOrders . '</td>
							<td class="number">' . locale_number_format($PrdTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
							<td class="number">' . locale_number_format($PrdTotalGP,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';

						echo '<tr class="striped_row"><td colspan="8"><hr /></td></tr>';
						echo '<tr class="striped_row">';

						$PrdTotalOrders =0;
						$PrdTotalSales=0;
						$PrdTotalRefunds=0;
						$PrdTotalNetSales=0;
						$PrdTotalCost=0;
						$PrdTotalGP=0;
					}
				}
				if (! $PeriodHeadingDone){
					echo '<td>' . __('Qtr'). '-' . $SalesRow['quarter_no'] . ' ' . $SalesRow['transyear'] . '</td>';
					$LastPeriodHeading = __('Qtr'). '-' . $SalesRow['quarter_no'] . ' ' . $SalesRow['transyear'];
					$PeriodHeadingDone = true;
				} else {
					echo '<td></td>';
				}
				break;
		} // end switch

		// This apparently completes the "double-row" that appears within each
		// case. The last portion of each case does a PeriodHeadingDone check.
		// The output from the PeriodHeadingDone area is the first column to go
		// with these below to make 8 columns.
		echo '<td>' . $SalesRow['tpe'] . '</td>
				<td class="number">' . $SalesRow['nooforders'] . '</td>
				<td class="number">' . locale_number_format($SalesRow['salesvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['returnvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['salesvalue']+$SalesRow['returnvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['cost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format(($SalesRow['salesvalue']+$SalesRow['returnvalue']-$SalesRow['cost']),$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
		$PrdTotalOrders +=$SalesRow['nooforders'];
		$PrdTotalSales += $SalesRow['salesvalue'];
		$PrdTotalRefunds += $SalesRow['returnvalue'];
		$PrdTotalNetSales += ($SalesRow['salesvalue']+$SalesRow['returnvalue']);
		$PrdTotalCost += $SalesRow['cost'];
		$PrdTotalGP += ($SalesRow['salesvalue']+$SalesRow['returnvalue']-$SalesRow['cost']);

		$CumulativeTotalSales += $SalesRow['salesvalue'];
		$CumulativeTotalOrders += $SalesRow['nooforders'];
		$CumulativeTotalRefunds += $SalesRow['returnvalue'];
		$CumulativeTotalNetSales += ($SalesRow['salesvalue']+$SalesRow['returnvalue']);
		$CumulativeTotalCost += $SalesRow['cost'];
		$CumulativeTotalGP += ($SalesRow['salesvalue']+$SalesRow['returnvalue']-$SalesRow['cost']);
	} // end loop

	echo '<tr class="striped_row">
		<td colspan="2" class="number">' . __('Total') . ' ' . $LastPeriodHeading . '</td>
		<td class="number">' . $PrdTotalOrders . '</td>
		<td class="number">' . locale_number_format($PrdTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($PrdTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($PrdTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($PrdTotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($PrdTotalGP,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

	echo '<tr class="striped_row"><td colspan="8"><hr /></td></tr>';
	echo '<tr class="striped_row">';

	echo '<td colspan="2" class="number">' . __('GRAND Total') . '</td>
		<td class="number">' . $CumulativeTotalOrders . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalRefunds,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalNetSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CumulativeTotalGP,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

	echo '</table>';

} //end of if user hit show sales
include('includes/footer.php');
