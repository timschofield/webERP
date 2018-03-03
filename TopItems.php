<?php


/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Top Items Searching');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.inc');

//check if input already
if (!(isset($_POST['Search']))) {

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Top Sales Order Search') . '" alt="" />' . ' ' . _('Top Sales Order Search') . '
		</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	//to view store location
	echo '<tr>
			<td style="width:150px">' . _('Select Location') . '  </td>
			<td>:</td>
			<td><select name="Location">';
	$sql = "SELECT locations.loccode,
					locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1 ORDER BY locations.locationname";
	$result = DB_query($sql);
	echo '<option value="All">' . _('All') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
	echo '</select></td>
		</tr>';
	//to view list of customer
	echo '<tr>
			<td style="width:150px">' . _('Select Customer Type') . '</td>
			<td>:</td>
			<td><select name="Customers">';

	$sql = "SELECT typename,
					typeid
			FROM debtortype
			ORDER BY typename";
	$result = DB_query($sql);
	echo '<option value="All">' . _('All') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
	}
	echo '</select></td>
		</tr>';

	// stock category selection
	$SQL="SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$result1 = DB_query($SQL);

	echo '<tr>
			<td style="width:150px">' . _('In Stock Category') . ' </td>
			<td>:</td>
			<td><select name="StockCat">';
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']='All';
	}
	if ($_POST['StockCat']=='All'){
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid']==$_POST['StockCat']){
			echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		}
	}
    echo '</select></td>
        </tr>';

	//view order by list to display
	echo '<tr>
			<td style="width:150px">' . _('Select Order By ') . ' </td>
			<td>:</td>
			<td><select name="Sequence">
				<option value="totalinvoiced">' . _('Total Pieces') . '</option>
				<option value="valuesales">' . _('Value of Sales') . '</option>
				</select></td>
		</tr>';
	//View number of days
	echo '<tr>
			<td>' . _('Number Of Days') . ' </td>
			<td>:</td>
			<td><input class="integer" required="required" pattern="(?!^0*$)(\d+)" title="'._('The input must be positive integer').'" tabindex="3" type="text" name="NumberOfDays" size="8" maxlength="8" value="30" /></td>
		 </tr>';
	//Stock in days less than
	echo '<tr>
			<td>' . _('With less than') . ' </td><td>:</td>
			<td><input class="integer" required="required" pattern="(?!^0*$)(\d+)" title="'._('The input must be positive integer').'" tabindex="4" type="text" name="MaxDaysOfStock" size="8" maxlength="8" value="99999" /></td>
			<td>' . ' ' . _('Days of Stock (QOH + QOO) Available') . ' </td>
		 </tr>';
	//view number of NumberOfTopItems items
	echo '<tr>
			<td>' . _('Number Of Top Items') . ' </td><td>:</td>
			<td><input class="integer" required="required" pattern="(?!^0*$)(\d+)" title="'._('The input must be positive integer').'" tabindex="4" type="text" name="NumberOfTopItems" size="8" maxlength="8" value="100" /></td>
		 </tr>
		 <tr>
			<td></td>
			<td></td>
		</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="' . _('Search') . '" />
	</div>
    </div>
	</form>';
} else {
	// everything below here to view NumberOfTopItems items sale on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -filter_number_format($_POST['NumberOfDays'])));

	$SQL = "SELECT 	salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice/currencies.rate ) AS valuesales,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					currencies.rate,
					debtorsmaster.currcode,
					fromstkloc,
					stockmaster.decimalplaces
			FROM 	salesorderdetails, salesorders INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
			debtorsmaster,stockmaster, currencies
			WHERE 	salesorderdetails.orderno = salesorders.orderno
					AND salesorderdetails.stkcode = stockmaster.stockid
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.currcode = currencies.currabrev
					AND salesorderdetails.actualdispatchdate >= '" . $FromDate . "'";

	if ($_POST['Location'] != 'All') {
		$SQL = $SQL . "	AND salesorders.fromstkloc = '" . $_POST['Location'] . "'";
	}

	if ($_POST['Customers'] != 'All') {
		$SQL = $SQL . "	AND debtorsmaster.typeid = '" . $_POST['Customers'] . "'";
	}

	if ($_POST['StockCat'] != 'All') {
		$SQL = $SQL . "	AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	}

	$SQL = $SQL . "	GROUP BY salesorderdetails.stkcode
					ORDER BY `" . $_POST['Sequence'] . "` DESC
					LIMIT " . filter_number_format($_POST['NumberOfTopItems']);

	$result = DB_query($SQL);

	echo '<p class="page_title_text"><strong>' . _('Top Sales Items List') . '</strong></p>';
	echo '<form action="PDFTopItems.php"  method="GET">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" value="' . $_POST['Location'] . '" name="Location" />
		<input type="hidden" value="' . $_POST['Sequence'] . '" name="Sequence" />
		<input type="hidden" value="' . filter_number_format($_POST['NumberOfDays']) . '" name="NumberOfDays" />
		<input type="hidden" value="' . $_POST['Customers'] . '" name="Customers" />
		<input type="hidden" value="' . filter_number_format($_POST['NumberOfTopItems']) . '" name="NumberOfTopItems" />
		<table class="selection">
		<thead>
			<tr>
						<th>' . _('#') . '</th>
						<th class="ascending">' . _('Code') . '</th>
						<th class="ascending">' . _('Description') . '</th>
						<th class="ascending">' . _('Total Invoiced') . '</th>
						<th class="ascending">' . _('Units') . '</th>
						<th class="ascending">' . _('Value Sales') . '</th>
						<th class="ascending">' . _('On Hand') . '</th>
						<th class="ascending">' . _('On Order') . '</th>
						<th class="ascending">' . _('Stock (Days)') . '</th>
			</tr>
		</thead>
		<tbody>';

	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		$QOH = 0;
		$QOO = 0;
		switch ($myrow['mbflag']) {
			case 'A':
			case 'D':
			case 'K':
				$QOH = _('N/A');
				$QOO = _('N/A');
			break;
			case 'M':
			case 'B':
				$QOHResult = DB_query("SELECT sum(quantity)
								FROM locstock
								INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
								WHERE stockid = '" . DB_escape_string($myrow['stkcode']) . "'");
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH = $QOHRow[0];

				// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.inc
				$QOO = GetQuantityOnOrderDueToPurchaseOrders($myrow['stkcode'], '');
				// Get the QOO due to Work Orders for all locations. Function defined in SQL_CommonFunctions.inc
				$QOO += GetQuantityOnOrderDueToWorkOrders($myrow['stkcode'], '');
			break;
		}
	        if(is_numeric($QOH) and is_numeric($QOO)){
			$DaysOfStock = ($QOH + $QOO) / ($myrow['totalinvoiced'] / $_POST['NumberOfDays']);
		}elseif(is_numeric($QOH)){
			$DaysOfStock = $QOH/ ($myrow['totalinvoiced'] / $_POST['NumberOfDays']);
		}elseif(is_numeric($QOO)){
			$DaysOfStock = $QOO/ ($myrow['totalinvoiced'] / $_POST['NumberOfDays']);

		}else{
			$DaysOfStock = 0;
		}
		if ($DaysOfStock < $_POST['MaxDaysOfStock']){
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stkcode'] . '">' . $myrow['stkcode'] . '</a>';
			$QOH = is_numeric($QOH)?locale_number_format($QOH,$myrow['decimalplaces']):$QOH;
			$QOO = is_numeric($QOO)?locale_number_format($QOO,$myrow['decimalplaces']):$QOO;
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>',
					$i,
					$CodeLink,
					$myrow['description'],
					locale_number_format($myrow['totalinvoiced'],$myrow['decimalplaces']), //total invoice here
					$myrow['units'], //unit
					locale_number_format($myrow['valuesales'],$_SESSION['CompanyRecord']['decimalplaces']), //value sales here
					$QOH,  //on hand
					$QOO, //on order
					locale_number_format($DaysOfStock, 0) //days of available stock
					);
		}
		$i++;
	}
	echo '</tbody></table>';
	echo '<br />
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print To PDF') . '" />
			</div>
        </div>
		</form>';
}
include ('includes/footer.php');
?>
