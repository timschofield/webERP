<?php
/* $Revision: 1.5 $ */
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Recurring Sales Orders');
include('includes/header.inc');

echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';

echo _('Select recurring order templates for delivery from:') . ' ' . '<select name="StockLocation">';

$sql = 'SELECT loccode, locationname FROM locations';
		
$resultStkLocs = DB_query($sql,$db);
	
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow['loccode'] == $_POST['StockLocation']){
			echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
			echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	} else {
			echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}

echo '</select>&nbsp&nbsp';
	
echo "<input type=submit name='SearchRecurringOrders' VALUE='" . _('Search Recurring Orders') . "'>";

echo '<hr>';

if (isset($_POST['SearchRecurringOrders'])){
	
	$SQL = "SELECT recurringsalesorders.recurrorderno,
				debtorsmaster.name,
				custbranch.brname,
				recurringsalesorders.customerref,
				recurringsalesorders.orddate,
				recurringsalesorders.deliverto,
				recurringsalesorders.lastrecurrence,
				recurringsalesorders.stopdate,
				recurringsalesorders.frequency,
				SUM(recurrsalesorderdetails.unitprice*recurrsalesorderdetails.quantity*(1-recurrsalesorderdetails.discountpercent)) AS ordervalue
			FROM recurringsalesorders,
				recurrsalesorderdetails,
				debtorsmaster,
				custbranch
			WHERE recurringsalesorders.recurrorderno = recurrsalesorderdetails.recurrorderno
			AND recurringsalesorders.debtorno = debtorsmaster.debtorno
			AND debtorsmaster.debtorno = custbranch.debtorno
			AND recurringsalesorders.branchcode = custbranch.branchcode
			AND recurringsalesorders.fromstkloc = '". $_POST['StockLocation'] . "'
			GROUP BY recurringsalesorders.recurrorderno,
				debtorsmaster.name,
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
	
	echo '<table cellpadding=2 colspan=7 WIDTH=100%>';
	
	$tableheader = "<tr>
			<th>" . _('Modify') . "</th>
			<th>" . _('Customer') . "</th>
			<th>" . _('Branch') . "</th>
			<th>" . _('Cust Order') . " #</th>
			<th>" . _('Last Recurrence') . "</th>
			<th>" . _('End Date') . "</th>
			<th>" . _('Times p.a.') . "</th>
			<th>" . _('Order Total') . "</th>
			</tr>";
		
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
	
		$ModifyPage = $rootpath . "/RecurringSalesOrders.php?" . SID . '&ModifyRecurringSalesOrder=' . $myrow['recurrorderno'];
		$FormatedLastRecurrence = ConvertSQLDate($myrow['lastrecurrence']);
		$FormatedStopDate = ConvertSQLDate($myrow['stopdate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);
	
		printf("<td><a href='%s'>%s</a></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td align=right>%s</td>
			</tr>",
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
	
	echo '</table></form>';
}

include('includes/footer.inc');
?>