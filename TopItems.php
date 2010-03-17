<?php
/* $Revision: 1.3 $ */
$PageSecurity = 2;
/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$title = _('Top Items Searching');
include ('includes/header.inc');
//check if input already
if (!(isset($_POST['Location']) and isset($_POST['NumberOfDays']) and isset($_POST['Customers']) and isset($_POST['NumberOfTopItems']) and isset($_POST['order']))) {
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Top Sales Order Search') . '" alt="">' . ' ' . _('Top Sales Order Search') . '</p>';
	echo "<form action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="SelectCustomer" method=POST>';
	echo '<table cellpadding=3 colspan=4>';
	//to view store location
	echo '<tr><td width="150">' . _('Select Location') . '  </td><td>:</td><td><select name=Location>';
	$sql = 'SELECT loccode,
					locationname
				FROM `locations`';
	$result = DB_query($sql, $db);
	echo "<option value='All'>" . _('All');
	while ($myrow = DB_fetch_array($result)) {
		echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['loccode'] . " - " . $myrow['locationname'];
	}
	echo "</select></td></tr>";
	//to view list of customer
	echo '<tr><td width="150">' . _('Select Customer Type') . '   </td><td>:</td><td><select name=Customers>';
	$sql = 'SELECT typename,
					typeid
				FROM debtortype';
	$result = DB_query($sql, $db);
	echo "<option value='All'>" . _('All');
	while ($myrow = DB_fetch_array($result)) {
		echo "<option VALUE='" . $myrow['typeid'] . "'>" . $myrow['typename'];
	}
	echo "</select></td>
		</tr>";
	//view order by list to display
	echo '<tr>	<td width="150">' . _('Select Order By ') . ' </td>
				<td>:</td>
				<td><select name=order>';
	echo '	<option value=TotalInvoiced>' . _('Total Pieces') . '';
	echo '	<option value=ValueSales>' . _('Value of Sales') . '';
	echo '	</select></td>
				</tr>';
	//View number of days
	echo '<tr><td>' . _('Number Of Days') . ' </td><td>:</td>
			<td><input class="number" tabindex="3" type="Text" name=NumberOfDays size="8"	maxlength="8" value=0></td>
		 </tr>';
	//view number of NumberOfTopItems items
	echo '<tr>
			<td>' . _('Number Of Top Items') . ' </td><td>:</td>
			<td><input class="number" tabindex="4" type="Text" name=NumberOfTopItems size="8"	maxlength="8" value=1></td>
		 </tr>
		 <tr>
			<td></td>
			<td></td>
			<td><input tabindex=5 type=submit Value="' . _('Search') . '"></td>
		</tr>
	</form>';
} else {
	// everything below here to view NumberOfTopItems items sale on selected location
	//the situation if the location and customer type selected "All"
	if (($_POST['Location'] == "All") and ($_POST['Customers'] == "All")) {
		$SQL = "
				SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
						stockmaster.description,
						stockmaster.units
				FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
				WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_POST['NumberOfDays'] . " DAY)
				GROUP BY salesorderdetails.stkcode
				ORDER BY " . $_POST["order"] . " DESC
				LIMIT 0," . $_POST['NumberOfTopItems'] . "";
	} else { //the situation if only location type selected "All"
		if ($_POST['Location'] == "All") {
			$SQL = "
				SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
						stockmaster.description,
						stockmaster.units
				FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
				WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND debtorsmaster.typeid = '" . $_POST["Customers"] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_POST['NumberOfDays'] . " DAY)
				GROUP BY salesorderdetails.stkcode
				ORDER BY " . $_POST["order"] . " DESC
				LIMIT 0," . $_POST[NumberOfTopItems] . "";
		} else {
			//the situation if the customer type selected "All"
			if ($_POST['Customers'] == "All") {
				$SQL = "
					SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
						stockmaster.description,
						stockmaster.units
					FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_POST["Location"] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_POST['NumberOfDays'] . " DAY)
					GROUP BY salesorderdetails.stkcode
					ORDER BY " . $_POST["order"] . " DESC
					LIMIT 0," . $_POST[NumberOfTopItems] . "";
			} else {
				//the situation if the location and customer type not selected "All"
				$SQL = "
					SELECT 	salesorderdetails.stkcode,
						SUM(salesorderdetails.qtyinvoiced) TotalInvoiced,
						SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice ) AS ValueSales,
						stockmaster.description,
						stockmaster.units
					FROM 	salesorderdetails, salesorders, debtorsmaster,stockmaster
					WHERE 	salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.stkcode = stockmaster.stockid
						AND salesorders.debtorno = debtorsmaster.debtorno
						AND salesorders.fromstkloc = '" . $_POST["Location"] . "'
						AND debtorsmaster.typeid = '" . $_POST['Customers'] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL " . $_POST['NumberOfDays'] . " DAY)
					GROUP BY salesorderdetails.stkcode
					ORDER BY " . $_POST["order"] . " DESC
					LIMIT 0," . $_POST[NumberOfTopItems] . "";
			}
		}
	}
	$result = DB_query($SQL, $db);
	echo '<p class="page_title_text" align="center"><strong>' . _('Top Sales Items List') . '</strong></p>';
	echo "<form action=PDFTopItems.php  method='GET'> <table class='table1'>";
	$TableHeader = '<tr><th><strong>' . _('#') . '</strong></th>
								<th><strong>' . _('Code') . '</strong></th>
								<th><strong>' . _('Description') . '</strong></th>
								<th><strong>' . _('Total Invoiced') . '</strong></th>
								<th><strong>' . _('Units') . '</strong></th>
								<th><strong>' . _('Value Sales') . '</strong></th>
								<th><strong>' . _('On Hand') . '</strong></th>';
	echo $TableHeader;
	echo '
			<input type="hidden" value=' . $_POST["Location"] . ' name=location />
			<input type="hidden" value=' . $_POST["order"] . ' name=order />
			<input type="hidden" value=' . $_POST["NumberOfDays"] . ' name=numberofdays />
			<input type="hidden" value=' . $_POST["Customers"] . ' name=customers />
			<input type="hidden" value=' . $_POST["NumberOfTopItems"] . ' name=NumberOfTopItems />
			';
	$k = 0; //row colour counter
	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		//find the quantity onhand item
		$sqloh = "SELECT   sum(quantity)as qty
						FROM     `locstock`
						WHERE     stockid='" . $myrow['0'] . "'";
		$oh = db_query($sqloh, $db);
		$ohRow = db_fetch_row($oh);
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$val = number_format($myrow['2'], 2);
		printf('<td class="number">%s</td>
						<td>%s</font></td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', $i, $myrow['0'], $myrow['3'], $myrow['1'], //total invoice here
		$myrow['4'], //unit
		$val, //value sales here
		$ohRow[0] //on hand
		);
		$i+= 1;
	}
	echo '</table>';
	//			echo '<td style="text-align:center" colspan=6><a href="javascript:history.go(-1)" title="Return to previous page"><input type=Button Name="Back" Value="' . _('Back') . '"></a></font>&nbsp&nbsp&nbsp';
	echo '<div class="centre"><input type=Submit Name="PrintPDF" Value="' . _('Print To PDF') . '"></div>';
	echo '</form>';
	//end of the else statement

}
include ('includes/footer.inc');
?>