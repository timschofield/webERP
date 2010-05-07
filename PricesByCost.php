<?php
/* $Id$ */
// PricesByCost.php -
$PageSecurity = 2;
include ('includes/session.inc');
$title = _('Update of Prices By Cost');
include ('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Inventory') . '" alt="">' . ' ' . _('Update Price By Cost') . '';
if (isset($_POST['submit']) or isset($_POST['update'])) {
	if ($_POST['Margin'] == "") {
		header('Location: PricesByCost.php');
	}
	if ($_POST['Comparator'] == 1) {
		$Comparator = "<=";
	} else {
		$Comparator = ">=";
	} /*end of else Comparator */
	if ($_POST['StockCat'] == 'all') {
		$Category = 'stockmaster.stockid = prices.stockid';
	} else {
		$Category = "stockmaster.stockid = prices.stockid AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	} /*end of else StockCat */
	$sql = 'SELECT 	stockmaster.stockid,
				stockmaster.description,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) as cost,
				prices.price as price, prices.debtorno as customer, prices.branchcode as branch
		FROM stockmaster, prices
		WHERE ' . $Category . '
		AND   prices.price' . $Comparator . '(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * ' . $_POST['Margin'] . "
		AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
		AND prices.currabrev ='" . $_POST['CurrCode'] . "'";
	$result = DB_query($sql, $db);
	$numrow = DB_num_rows($result);
	$sqlcat = "SELECT categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "'";
	$ResultCat = DB_query($sqlcat, $db);
	$Category = DB_fetch_array($ResultCat);
	$sqltype = "SELECT sales_type
				FROM salestypes
				WHERE typeabbrev='" . $_POST['SalesType'] . "'";
	$ResultType = DB_query($sqltype, $db);
	$Type = DB_fetch_array($ResultType);
	if (isset($Category[0])) {
		$Cat = $Category[0];
	} else {
		$Cat = 'All Category';
	} /*end of else Category */
	echo '<div class="page_help_text">' . _('Items in category ') . '' . $Cat . '' . _(' With Price ') . '' . $Comparator . '' . $_POST['Margin'] . '' . _('  times ') . '' . _('Cost in Price List ') . '' . $Type['0'] . '</div><br><br>';
	if ($numrow != 0) {
		echo '<table>';
		echo '<tr><th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Cost') . '</th>
						<th>' . _('Current Margin') . '</th>
						<th>' . _('Price Proposed') . '</th>
						<th>' . _('Price in pricelist') . '</th>
					<tr>';
		$k = 0; //row colour counter
		echo '<form action="PricesByCost.php" method="POST" name="' . _('update') . '">';
		while ($myrow = DB_fetch_array($result)) {
			//update database if update pressed
			if ($_POST['submit'] == 'Update') {
				//Update Prices
				$SQLUpdate = "UPDATE prices
						SET price = '" . $_POST[$myrow['0']] . "'
						WHERE `prices`.`stockid` = '" . $myrow['0'] . "'
						AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
						AND prices.currabrev ='" . $_POST['CurrCode'] . "'
						AND prices.debtorno ='" . $myrow['customer'] . "'
						AND prices.branchcode ='" . $myrow['branch'] . "'";
				$Resultup = DB_query($SQLUpdate, $db);
			}
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			//get cost
			if ($myrow['cost'] == "") {
				$Cost = "0";
			} else {
				$Cost = $myrow['cost'];
			} /*end of else Cost */
			//get qty price
			if (isset($_POST[$myrow['0']])) {
				$price = $_POST[$myrow['0']];
			} else {
				$price = $myrow['price'];
			} /*end of else price	*/
			//variable for update data
			echo '
				<input type="hidden" value=' . $_POST['StockCat'] . ' name=' . _('StockCat') . ' />
				<input type="hidden" value=' . $_POST['Margin'] . ' name=' . _('Margin') . ' />
				<input type="hidden" value=' . $_POST['CurrCode'] . ' name=' . _('CurrCode') . ' />
				<input type="hidden" value=' . $_POST['Comparator'] . ' name=' . _('Comparator') . ' />
				<input type="hidden" value=' . $_POST['SalesType'] . ' name=' . _('SalesType') . ' />
				<input type="hidden" value=' . $myrow['0'] . ' name=' . _('Id') . ' />
				<input type="hidden" value=' . $_POST['Price'] . ' name=' . _('Price') . ' />
				';
			//variable for current margin
			$currentmargin = $price / $Cost;
			//variable for proposed
			$proposed = $Cost * $_POST['Margin'];
			echo '   <td>' . $myrow['0'] . '</td>
						<td>' . $myrow['1'] . '</td>
						<td>' . $myrow['customer'] . '</td>
						<td>' . $myrow['branch'] . '</td>
						<td class="number">' . number_format($Cost, 2) . '</td>
						<td class="number">' . number_format($currentmargin, 2) . '</td>
						<td class="number">' . number_format($proposed, 2) . '</td>
						<td><input type="text" class="number" name="' . $myrow['0'] . '" MAXLENGTH =14 size=15 value="' . $price . '"></td>
					</tr> ';
		} //end of looping
		echo '<tr>
			<td style="text-align:right" colspan=4><input type=submit name=submit value=' . _("Update") . '></td>
			<td style="text-align:left" colspan=3><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><input type=submit  value=' . _("Back") . '><a/></td>
			 </tr></form>';
	} else {
		echo '<p><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Back') . '<a/></div><p>';
	}
} else { /*The option to submit was not hit so display form */
	echo '<div class="page_help_text">' . _('Use this report to display price list with the cost.') . '</div><br>';
	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	$SQL = 'SELECT categoryid, categorydescription
	      FROM stockcategory
		  ORDER BY categorydescription';
	$result1 = DB_query($SQL, $db);
	echo '<tr>
			<td>' . _('Category') . ':</td>
			<td><select name="StockCat">';
	echo '<option value="all">' . _('All Categories') . '';
	while ($myrow1 = DB_fetch_array($result1)) {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
	}
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Price') . '
				<select name="Comparator">';
	echo '<option value="1">' . _('Less than or equal to') . '';
	echo '<option value="2">' . _('Greater than or equal to') . '';
	if ($_SESSION['WeightedAverageCosting']==1) {
		echo '</select>'.' '. _('Average Cost') . ' x </td>';
	} else {
		echo '</select>'.' '. _('Standard Cost') . ' x </td>';
	}
	echo '<td>
				<input type="text" class="number" name="Margin" MAXLENGTH =10 size=11 value=0></td></tr>';
	$result = DB_query('SELECT typeabbrev, sales_type FROM salestypes ', $db);
	echo '<tr><td>' . _('Sales Type') . '/' . _('Price List') . ":</td>
		<td><select name='SalesType'>";
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['SalesType'] == $myrow['typeabbrev']) {
			echo "<option selected value='" . $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
		} else {
			echo "<option value='" . $myrow['typeabbrev'] . "'>" . $myrow['sales_type'];
		}
	} //end while loop
	DB_data_seek($result, 0);
	$result = DB_query('SELECT currency, currabrev FROM currencies', $db);
	echo '</select></td></tr>
		<tr><td>' . _('Currency') . ":</td>
		<td><select name='CurrCode'>";
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['CurrCode']) and $_POST['CurrCode'] == $myrow['currabrev']) {
			echo '<option selected value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		} else {
			echo '<option value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		}
	} //end while loop
	DB_data_seek($result, 0);
	echo '</select></td></tr>';
	echo "</table></br><p><div class='centre'><input type=submit name='submit' value='" . _('Submit') . "'></div></p>";
} /*end of else not submit */
include ('includes/footer.inc');
?>