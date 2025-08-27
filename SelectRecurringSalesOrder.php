<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Search Recurring Sales Orders');
$ViewTopic = 'SalesOrders';
$BookMark = 'RecurringSalesOrders';
include('includes/header.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' .
	__('Inventory Items') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<fieldset>
		<legend class="search">', __('Search Recurring Orders'), '</legend>
		<field>
			<label for="StockLocation">' . __('Select recurring order templates for delivery from:') . ' </label>
			<select name="StockLocation">';

$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";

$ResultStkLocs = DB_query($SQL);

while ($MyRow=DB_fetch_array($ResultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($MyRow['loccode'] == $_POST['StockLocation']){
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif ($MyRow['loccode']==$_SESSION['UserStockLocation']){
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}

echo '</select>
	</field>
	</fieldset>';

echo '<div class="centre"><input type="submit" name="SearchRecurringOrders" value="' . __('Search Recurring Orders') . '" /></div>';

if (isset($_POST['SearchRecurringOrders'])){

	$SQL = "SELECT recurringsalesorders.recurrorderno,
				debtorsmaster.name,
				currencies.decimalplaces AS currdecimalplaces,
				custbranch.brname,
				recurringsalesorders.customerref,
				recurringsalesorders.orddate,
				recurringsalesorders.deliverto,
				recurringsalesorders.lastrecurrence,
				recurringsalesorders.stopdate,
				recurringsalesorders.frequency,
SUM(recurrsalesorderdetails.unitprice*recurrsalesorderdetails.quantity*(1-recurrsalesorderdetails.discountpercent)) AS ordervalue
			FROM recurringsalesorders INNER JOIN recurrsalesorderdetails
			ON recurringsalesorders.recurrorderno = recurrsalesorderdetails.recurrorderno
			INNER JOIN debtorsmaster
			ON recurringsalesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch
			ON debtorsmaster.debtorno = custbranch.debtorno
			AND recurringsalesorders.branchcode = custbranch.branchcode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE recurringsalesorders.fromstkloc = '". $_POST['StockLocation'] . "'
			GROUP BY recurringsalesorders.recurrorderno,
				debtorsmaster.name,
				currencies.decimalplaces,
				custbranch.brname,
				recurringsalesorders.customerref,
				recurringsalesorders.orddate,
				recurringsalesorders.deliverto,
				recurringsalesorders.lastrecurrence,
				recurringsalesorders.stopdate,
				recurringsalesorders.frequency";

	$ErrMsg = __('No recurring orders were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL, $ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<table cellpadding="2" width="90%" class="selection">
			<tr>
				<th>' . __('Modify') . '</th>
				<th>' . __('Customer') . '</th>
				<th>' . __('Branch') . '</th>
				<th>' . __('Cust Order') . ' #</th>
				<th>' . __('Last Recurrence') . '</th>
				<th>' . __('End Date') . '</th>
				<th>' . __('Times p.a.') . '</th>
				<th>' . __('Order Total') . '</th>
			</tr>';

	while ($MyRow=DB_fetch_array($SalesOrdersResult)) {

		$ModifyPage = $RootPath . '/RecurringSalesOrders.php?ModifyRecurringSalesOrder=' . $MyRow['recurrorderno'];
		$FormatedLastRecurrence = ConvertSQLDate($MyRow['lastrecurrence']);
		$FormatedStopDate = ConvertSQLDate($MyRow['stopdate']);
		$FormatedOrderValue = locale_number_format($MyRow['ordervalue'],$MyRow['currdecimalplaces']);

		echo '<tr class="striped_row">
				<td><a href="', $ModifyPage, '">', $MyRow['recurrorderno'], '</a></td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['brname'], '</td>
				<td>', $MyRow['customerref'], '</td>
				<td>', $FormatedLastRecurrence, '</td>
				<td>', $FormatedStopDate, '</td>
				<td>', $MyRow['frequency'], '</td>
				<td class="number">', $FormatedOrderValue, '</td>
			</tr>';
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';
}
echo '</div>
      </form>';

include('includes/footer.php');
