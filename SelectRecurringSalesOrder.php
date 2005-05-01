<?php
/* $Revision: 1.3 $ */
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Recurring Sales Orders');
include('includes/header.inc');

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] .'?' .SID . ' METHOD=POST>';

echo _('Select recurring order templates for delivery from:') . ' ' . '<SELECT NAME="StockLocation">';

$sql = 'SELECT loccode, locationname FROM locations';
		
$resultStkLocs = DB_query($sql,$db);
	
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow['loccode'] == $_POST['StockLocation']){
			echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
			echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	} else {
			echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}

echo '</SELECT>&nbsp&nbsp';
	
echo "<INPUT TYPE=SUBMIT NAME='SearchRecurringOrders' VALUE='" . _('Search Recurring Orders') . "'>";

echo '<HR>';

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
	
	echo '<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>';
	
	$tableheader = "<TR>
			<TD class='tableheader'>" . _('Modify') . "</TD>
			<TD class='tableheader'>" . _('Customer') . "</TD>
			<TD class='tableheader'>" . _('Branch') . "</TD>
			<TD class='tableheader'>" . _('Cust Order') . " #</TD>
			<TD class='tableheader'>" . _('Last Recurrence') . "</TD>
			<TD class='tableheader'>" . _('End Date') . "</TD>
			<TD class='tableheader'>" . _('Times p.a.') . "</TD>
			<TD class='tableheader'>" . _('Order Total') . "</TD>
			</TR>";
		
	echo $tableheader;
	
	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {
	
	
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
	
		$ModifyPage = $rootpath . "/RecurringSalesOrders.php?" . SID . '&ModifyRecurringSalesOrder=' . $myrow['recurrorderno'];
		$FormatedLastRecurrence = ConvertSQLDate($myrow['lastrecurrence']);
		$FormatedStopDate = ConvertSQLDate($myrow['stopdate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);
	
		printf("<td><A HREF='%s'>%s</A></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
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
	
	echo '</TABLE></FORM>';
}

include('includes/footer.inc');
?>