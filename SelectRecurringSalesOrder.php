<?php
/* $Id: SelectRecurringSalesOrder.php 5785 2012-12-29 04:47:42Z daintree $*/

include('includes/session.inc');
$Title = _('Search Recurring Sales Orders');
/* webERP manual links before header.inc */
$ViewTopic= 'SalesOrders';
$BookMark = 'RecurringSalesOrders';

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' .
	_('Inventory Items') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<table class="selection">
		<tr>
			<td>' . _('Select recurring order templates for delivery from:') . ' </td>
			<td>' . '<select name="StockLocation">';

$sql = "SELECT loccode, locationname FROM locations";

$resultStkLocs = DB_query($sql,$db);

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow['loccode'] == $_POST['StockLocation']){
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}

echo '</select></td>
	</tr>
	</table>';

echo '<br /><div class="centre"><input type="submit" name="SearchRecurringOrders" value="' . _('Search Recurring Orders') . '" /></div>';

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

	$ErrMsg = _('No recurring orders were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<br />
		<table cellpadding="2" width="90%" class="selection">';

	$tableheader = '<tr>
						<th>' . _('Modify') . '</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Cust Order') . ' #</th>
						<th>' . _('Last Recurrence') . '</th>
						<th>' . _('End Date') . '</th>
						<th>' . _('Times p.a.') . '</th>
						<th>' . _('Order Total') . '</th>
					</tr>';

	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';;
			$k++;
		}

		$ModifyPage = $RootPath . '/RecurringSalesOrders.php?ModifyRecurringSalesOrder=' . $myrow['recurrorderno'];
		$FormatedLastRecurrence = ConvertSQLDate($myrow['lastrecurrence']);
		$FormatedStopDate = ConvertSQLDate($myrow['stopdate']);
		$FormatedOrderValue = locale_number_format($myrow['ordervalue'],$myrow['currdecimalplaces']);

		printf('<td><a href="%s">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>',
				$ModifyPage,
				$myrow['recurrorderno'],
				$myrow['name'],
				$myrow['brname'],
				$myrow['customerref'],
				$FormatedLastRecurrence,
				$FormatedStopDate,
				$myrow['frequency'],
				$FormatedOrderValue);

		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';
}
echo '</div>
      </form>';

include('includes/footer.inc');
?>