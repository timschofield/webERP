<?php

require(__DIR__ . '/includes/session.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

$Title = __('Customer Activity and Balances');
/*To do: Info in the manual. RChacon.*/
$ViewTopic = 'ARInquiries';
$BookMark = '';

if (!isset($_POST['CreateCSV'])) {
	include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Customer Activity and Balances') . '" /> ' . __('Customer Activity and Balances') . '</p>';
}

if (!isset($_POST['RunReport'])){

	$SalesAreasResult = DB_query("SELECT areacode, areadescription FROM areas");
	$CustomersResult = DB_query("SELECT debtorno, name FROM debtorsmaster ORDER BY name");
	$SalesFolkResult = DB_query("SELECT salesmancode, salesmanname FROM salesman ORDER BY salesmanname");

	echo '<form id="Form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="Customer">' . __('Customer') . '</label>
				<select name="Customer">
					<option selected="selected" value="">' . __('All') . '</option>';
	while ($CustomerRow = DB_fetch_array($CustomersResult)) {
		echo 			'<option value="' . $CustomerRow['debtorno'] . '">' . $CustomerRow['name'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SalesArea">' . __('Sales Area') . '</label>
			<select name="SalesArea">
				<option selected="selected" value="">' . __('All') . '</option>';
	while ($AreaRow = DB_fetch_array($SalesAreasResult)) {
		echo '<option value="' . $AreaRow['areacode'] . '">' . $AreaRow['areadescription'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SalesPerson">' . __('Sales Person') . '</label>
			<select name="SalesPerson">
				<option selected="selected" value="">' . __('All') . '</option>';
	while ($SalesPersonRow = DB_fetch_array($SalesFolkResult)) {
		echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="FromDate">' . __('Date From') . ':</label>
			<input type="date" name="FromDate" maxlength="10" size="11" value="' . Date('Y-m-d', Mktime(0, 0, 0, Date('m') - $_SESSION['NumberOfMonthMustBeShown'], Date('d'), Date('Y'))) . '" />
		</field>';

	echo '<field>
			<label for="ToDate">' . __('Date To') . ':</label>
			<input type="date" name="ToDate" maxlength="10" size="11" value="' . Date('Y-m-d') . '" />
		</field>';

	echo '<field>
			<label for="CreateCSV">' . __('Create CSV') . ':</label>
			<input type="checkbox" name="CreateCSV" value="">
		</field>';

	echo '</fieldset>';
	echo '<div class="centre">
			<input tabindex="4" type="submit" name="RunReport" value="' . __('Show Customer Balance Movements') . '" />
		</div>
	</form>';
	include('includes/footer.php');
	exit();
}

if ($_POST['Customer']!='') {
	$WhereClause = "debtorsmaster.debtorno='" . $_POST['Customer'] . "'";
} elseif ($_POST['SalesArea']!='') {
	$WhereClause = "custbranch.area='" . $_POST['SalesArea'] . "'";
} elseif ($_POST['SalesPerson']!='') {
	$WhereClause = "custbranch.salesman='" . $_POST['SalesPerson'] . "'";
}

$SQL = "SELECT SUM(ovamount+ovgst+ovdiscount+ovfreight-alloc) AS currencybalance,
				debtorsmaster.debtorno,
				debtorsmaster.name,
				decimalplaces AS currdecimalplaces,
				SUM((ovamount+ovgst+ovdiscount+ovfreight-alloc)/debtortrans.rate) AS localbalance
		FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
		INNER JOIN currencies
		ON debtorsmaster.currcode=currencies.currabrev
		INNER JOIN custbranch
		ON debtorsmaster.debtorno=custbranch.debtorno";

if (isset($WhereClause) and mb_strlen($WhereClause)>0){
	$SQL .= " WHERE " . $WhereClause . " ";
}
$SQL .= " GROUP BY debtorsmaster.debtorno";

$Result = DB_query($SQL);

$LocalTotal =0;

if (!isset($_POST['CreateCSV'])){

	echo '<table>
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Customer') . ' </th>
				<th class="SortedColumn">' . __('Opening Balance') . '</th>
				<th class="SortedColumn">' . __('Debits') . '</th>
				<th class="SortedColumn">' . __('Credits') . '</th>
				<th class="SortedColumn">' . __('Balance') . '</th>
			</tr>
		</thead>
		<tbody>';
} else {
	$CSVFile = '"' . __('Customer') . '","' . __('Opening Balance') . '","' . __('Debits') . '", "' . __('Credits') . '","' . __('Balance') . '"' . "\n";
}

$OpeningBalances =0;
$Debits =0;
$Credits =0;
$ClosingBalances =0;

while ($MyRow=DB_fetch_array($Result)){

/*Get the sum of all transactions after the ending date -
 * we need to take off the sum of all movements after the ending date from the current balance calculated above
 * to get the balance as at the end of the period
 */
	$SQL = "SELECT SUM(ovamount+ovgst+ovdiscount+ovfreight) AS currencytotalpost,
					debtorsmaster.debtorno,
					SUM((ovamount+ovgst+ovdiscount+ovfreight)/debtortrans.rate) AS localtotalpost
			FROM debtortrans INNER JOIN debtorsmaster
				ON debtortrans.debtorno=debtorsmaster.debtorno
			WHERE trandate > '" . FormatDateForSQL($_POST['FromDate']) . "'
			AND debtorsmaster.debtorno = '" . $MyRow['debtorno'] . "'
			GROUP BY debtorsmaster.debtorno";

	$TransPostResult = DB_query($SQL);
	$TransPostRow = DB_fetch_array($TransPostResult);
/* Now we need to get the debits and credits during the period under review
 */
	$SQL = "SELECT SUM(CASE WHEN debtortrans.type=10 THEN ovamount+ovgst+ovdiscount+ovfreight ELSE 0 END) AS currencydebits,
					SUM(CASE WHEN debtortrans.type<>10 THEN ovamount+ovgst+ovdiscount+ovfreight ELSE 0 END) AS currencycredits,
					debtorsmaster.debtorno,
					SUM(CASE WHEN debtortrans.type=10 THEN (ovamount+ovgst+ovdiscount+ovfreight)/debtortrans.rate ELSE 0 END) AS localdebits,
					SUM(CASE WHEN debtortrans.type<>10 THEN (ovamount+ovgst+ovdiscount+ovfreight)/debtortrans.rate ELSE 0 END) AS localcredits
			FROM debtortrans INNER JOIN debtorsmaster
				ON debtortrans.debtorno=debtorsmaster.debtorno
			WHERE trandate>='" . FormatDateForSQL($_POST['FromDate']) . "' AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
			AND debtorsmaster.debtorno = '" . $MyRow['debtorno'] . "'
			GROUP BY debtorsmaster.debtorno";

	$TransResult = DB_query($SQL);
	$TransRow = DB_fetch_array($TransResult);

	$OpeningBal = $MyRow['localbalance']-$TransPostRow['localtotalpost']-$TransRow['localdebits']-$TransRow['localcredits'];
	$ClosingBal = $MyRow['localbalance']-$TransPostRow['localtotalpost'];
	if($OpeningBal !=0 OR $ClosingBal!=0 OR $TransRow['localdebits']!=0 OR $TransRow['localcredits']!=0) {

		if (!isset($_POST['CreateCSV'])){
			echo '<tr>
					<td>' . $MyRow['name'] . ' </td>
					<td class="number">' . locale_number_format($OpeningBal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($TransRow['localdebits'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($TransRow['localcredits'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($ClosingBal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
		} else { //send the line to CSV file
			$CSVFile .=  '"' . stripcomma($MyRow['name']) . '","' . stripcomma($OpeningBal) . '","' . stripcomma($TransRow['localdebits']) . '","' . stripcomma($TransRow['localcredits']) . '","' . stripcomma($ClosingBal) . '"' . "\n";

		}
	}

	$OpeningBalances += $OpeningBal;
	$Debits += $TransRow['localdebits'];
	$Credits += $TransRow['localcredits'];
	$ClosingBalances += $ClosingBal;
}

if (!isset($_POST['CreateCSV'])){
	echo '</tbody></table>';
}
if ($_POST['Customer']==''){ //if there could be several customers being reported
	if (!isset($_POST['CreateCSV'])){
		echo '<table>
			<tr>
				<th></th>
				<th>' . __('Opening Balance') . '</th>
				<th>' . __('Debits') . '</th>
				<th>' . __('Credits') . '</th>
				<th>' . __('Balance') . '</th>
			</tr>
			<tr>
				<td>' . __('TOTALS') . '</td>
				<td class="number">' . locale_number_format($OpeningBalances,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($Debits,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($Credits,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($ClosingBalances,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>
		</table>';
	} else {
		$CSVFile .=  '"' . __('TOTALS') . '","' . stripcomma($OpeningBalances) . '","' . stripcomma($Debits) . '","' . stripcomma($Credits) . '","' . stripcomma($ClosingBalances) . '"' . "\n";
	}
}

if (isset($_POST['CreateCSV'])){

	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
	header("Content-disposition: attachment; filename=CustomerBalancesMovement_" . FormatDateForSQL($_POST['FromDate']) . '-' . FormatDateForSQL($_POST['ToDate']) .'.csv');
	# @todo review these headers - should we not make this uncacheable instead?
	header("Pragma: public");
	header("Expires: 0");
	// the BOM is not used much anymore in 2025...
	//echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $CSVFile;
	exit();
}

include('includes/footer.php');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(',', '', $str);
}
