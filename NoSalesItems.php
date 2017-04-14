<?php

/* $Id: NoSalesItems.php 2012-05-12 $*/

include ('includes/session.php');
$Title = _('No Sales Items Searching');
include ('includes/header.php');
if (!(isset($_POST['Search']))) {
echo '<div class="centre"><p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('No Sales Items') . '" alt="" />' . ' ' . _('No Sales Items') . '</p></div>';
	echo '<div class="page_help_text">'
	. _('List of items with stock available during the last X days at the selected locations but did not sell any quantity during these X days.'). '<br />' .  _( 'This list gets the no selling items, items at the location just wasting space, or need a price reduction, etc.') . '<br />' .  _('Stock available during the last X days means there was a stock movement that produced that item into that location before that day, and no other positive stock movement has been created afterwards.  No sell any quantity means, there is no sales order for that item from that location.')  . '</div>';
	echo '<br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?name="SelectCustomer" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';

	//select location
	echo '<tr>
			 <td>' . _('Select Location') . '</td>
			 <td>:</td>
			 <td><select name="Location[]" multiple="multiple">
				<option value="All" selected="selected">' . _('All') . '</option>';;
	$sql = "SELECT 	locations.loccode,locationname
			FROM 	locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			ORDER BY locationname";
	$locationresult = DB_query($sql);
	$i=0;
	while ($myrow = DB_fetch_array($locationresult)) {
		if(isset($_POST['Location'][$i]) AND $myrow['loccode'] == $_POST['Location'][$i]){
		echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		$i++;
		} else {
			echo '<option value="' . $myrow['loccode'] . '">'  . $myrow['locationname']  . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	//to view list of customer
	echo '<tr>
			<td width="150">' . _('Select Customer Type') . '</td>
			<td>:</td>
			<td><select name="Customers">';

	$sql = "SELECT typename,
					typeid
				FROM debtortype";
	$result = DB_query($sql);
	echo '<option value="All">' . _('All') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
	}
	echo '</select></td>
		</tr>';

	// stock category selection
	$SQL="SELECT categoryid,categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$result1 = DB_query($SQL);
	echo '<tr>
			<td width="150">' . _('In Stock Category') . ' </td>
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

	//View number of days
	echo '<tr>
			<td>' . _('Number Of Days') . ' </td>
			<td>:</td>
			<td><input class="integer" tabindex="3" type="text" required="required" title="' . _('Enter the number of days to examine the sales for') . '" name="NumberOfDays" size="8" maxlength="8" value="30" /></td>
		 </tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="' . _('Search') . '" />
	</div>
	</form>';
} else {

	// everything below here to view NumberOfNoSalesItems on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -filter_number_format($_POST['NumberOfDays'])));
	if ($_POST['StockCat']=='All'){
		$WhereStockCat = "";
	}else{
		$WhereStockCat = " AND stockmaster.categoryid = '" . $_POST['StockCat'] ."'";
	}

	if ($_POST['Location'][0] == 'All') {
		$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM 	stockmaster,locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE 	stockmaster.stockid = locstock.stockid ".
						$WhereStockCat . "
					AND (locstock.quantity > 0)
					AND NOT EXISTS (
							SELECT *
							FROM 	salesorderdetails, salesorders
							INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 	stockmaster.stockid = salesorderdetails.stkcode
									AND (salesorderdetails.orderno = salesorders.orderno)
									AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
					AND NOT EXISTS (
							SELECT *
							FROM 	stockmoves
							INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 	stockmoves.stockid = stockmaster.stockid
									AND stockmoves.trandate >= '" . $FromDate . "')
					AND EXISTS (
							SELECT *
							FROM 	stockmoves
							INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 	stockmoves.stockid = stockmaster.stockid
									AND stockmoves.trandate < '" . $FromDate . "'
									AND stockmoves.qty >0)
				GROUP BY stockmaster.stockid
				ORDER BY stockmaster.stockid";
	}else{
		$WhereLocation = '';
		if (sizeof($_POST['Location']) == 1) {
			$WhereLocation = " AND locstock.loccode ='" . $_POST['Location'][0] . "' ";
		} else {
			$WhereLocation = " AND locstock.loccode IN(";
			$commactr = 0;
			foreach ($_POST['Location'] as $key => $value) {
				$WhereLocation .= "'" . $value . "'";
				$commactr++;
				if ($commactr < sizeof($_POST['Location'])) {
					$WhereLocation .= ",";
				} // End of if
			} // End of foreach
			$WhereLocation .= ')';
		}
		$SQL = "SELECT 	stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.quantity,
						locations.locationname
				FROM 	stockmaster,locstock,locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE 	stockmaster.stockid = locstock.stockid
						AND (locstock.loccode = locations.loccode)".
						$WhereLocation .
						$WhereStockCat . "
						AND (locstock.quantity > 0)
						AND NOT EXISTS (
								SELECT *
								FROM 	salesorderdetails, salesorders
								WHERE 	stockmaster.stockid = salesorderdetails.stkcode
										AND (salesorders.fromstkloc = locstock.loccode)
										AND (salesorderdetails.orderno = salesorders.orderno)
										AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
						AND NOT EXISTS (
								SELECT *
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate >= '" . $FromDate . "')
						AND EXISTS (
								SELECT *
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '" . $FromDate . "'
										AND stockmoves.qty >0)
				ORDER BY stockmaster.stockid";
	}
	$result = DB_query($SQL);
	echo '<p class="page_title_text" align="center"><strong>' . _('No Sales Items') . '</strong></p>';
	echo '<form action="PDFNoSalesItems.php"  method="GET">
		<table class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$TableHeader = '<tr>
						<th>' . _('No') . '</th>
						<th>' . _('Location') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Location QOH') . '</th>
						<th>' . _('Total QOH') . '</th>
						<th>' . _('Units') . '</th>
					</tr>';
	echo $TableHeader;
	echo '<input type="hidden" value="' . $_POST['Location'] . '" name="Location" />
			<input type="hidden" value="' . filter_number_format($_POST['NumberOfDays']) . '" name="NumberOfDays" />
			<input type="hidden" value="' . $_POST['Customers'] . '" name="Customers" />';
	$k = 0; //row colour counter
	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$QOHResult = DB_query("SELECT sum(quantity)
				FROM locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE stockid = '" . $myrow['stockid'] . "'" .
				$WhereLocation);
		$QOHRow = DB_fetch_row($QOHResult);
		$QOH = $QOHRow[0];

		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		if ($_POST['Location'][0] == 'All') {
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>',
					$i,
					'All',
					$CodeLink,
					$myrow['description'],
					$QOH, //on hand on ALL locations
					$QOH, // total on hand
					$myrow['units'] //unit
					);
		}else{
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>',
					$i,
					$myrow['locationname'],
					$CodeLink,
					$myrow['description'],
					$myrow['quantity'], //on hand on location selected only
					$QOH, // total on hand
					$myrow['units'] //unit
					);
		}
		$i++;
	}
	echo '</table>';
	echo '<br />
	</form>';
}
include ('includes/footer.php');
?>