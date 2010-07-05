<?php
/* $Id: PricesByCost.php 3475 2010-05-29 00:00:42Z daintree $ */
// PricesByCost.php -
$PageSecurity = 11;
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
	if ($_POST['StockCat'] != 'all') {
		$Category = " AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	} else {
		$Category ='';
	}/*end of else StockCat */

	$sql = "SELECT 	stockmaster.stockid,
				stockmaster.description,
				prices.debtorno,
				prices.branchcode,
				(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) as cost,
				prices.price as price, prices.debtorno as customer, prices.branchcode as branch,
				prices.startdate,
				prices.enddate
			FROM stockmaster, prices
			WHERE stockmaster.stockid=prices.stockid" . $Category . "
			AND   prices.price" . $Comparator . "(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) * '" . $_POST['Margin'] . "'
			AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
			AND prices.currabrev ='" . $_POST['CurrCode'] . "'
			AND (prices.enddate>='" . Date('Y-m-d') . "' OR prices.enddate='0000-00-00')";
	$result = DB_query($sql, $db);
	$numrow = DB_num_rows($result);
echo $sql;
	if ($_POST['submit'] == 'Update') {
			//Update Prices
		$PriceCounter =0;
		while ($myrow = DB_fetch_array($result)) {
			//update database if update pressed
			$SQLUpdate = "UPDATE prices
								SET price = '" . $_POST['Price_' . $PriceCounter] . "'
							WHERE `prices`.`stockid` = '" . $_POST['StockID_' . $PriceCounter] . "'
							AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
							AND prices.currabrev ='" . $_POST['CurrCode'] . "'
							AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
							AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
							AND prices.startdate ='" . $_POST['StartDate_' . $PriceCounter] . "'
							AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'";
			$ResultUpdate = DB_query($SQLUpdate, $db);
			$PriceCounter++;
		}
		DB_free_result($result); //clear the old result
		$result = DB_query($sql, $db); //re-run the query with the updated prices
		$numrow = DB_num_rows($result); // get the new number - should be the same!!
	}

	$sqlcat = "SELECT categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "'";
	$ResultCat = DB_query($sqlcat, $db);
	$CategoryRow = DB_fetch_array($ResultCat);

	$sqltype = "SELECT sales_type
				FROM salestypes
				WHERE typeabbrev='" . $_POST['SalesType'] . "'";
	$ResultType = DB_query($sqltype, $db);
	$SalesTypeRow = DB_fetch_array($ResultType);

	if (isset($CategoryRow['categorgdescription'])) {
		$CategoryText = _('the') . ' ' . $CategoryRow['categorgdescription'] . ' ' . _('category');
	} else {
		$CategoryText = _('all Categories');
	} /*end of else Category */

	echo '<div class="page_help_text">' . _('Items in') . ' ' . $CategoryText . ' ' . _('With Prices') . ' ' . $Comparator . '' . $_POST['Margin'] . ' ' . _('times') . ' ' . _('Cost in Price List') . ' ' . $SalesTypeRow['sales_type'] . '</div><br><br>';

	if ($numrow > 0) { //the number of prices returned from the main prices query is
		echo '<table class=selection>';
		echo '<tr><th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Customer') . '</th>
						<th>' . _('Branch') . '</th>
						<th>' . _('Start Date') . '</th>
						<th>' . _('End Date') . '</th>
						<th>' . _('Cost') . '</th>
						<th>' . _('GP %') . '</th>
						<th>' . _('Price Proposed') . '</th>
						<th>' . _('List Price') . '</th>
					<tr>';
		$k = 0; //row colour counter
		echo '<form action="' .$_SERVER['PHP_SELF'] .'" method="POST" name="update">';
		echo'<input type="hidden" value=' . $_POST['StockCat'] . ' name="StockCat">
			<input type="hidden" value=' . $_POST['Margin'] . ' name="Margin">
			<input type="hidden" value=' . $_POST['CurrCode'] . ' name="CurrCode">
			<input type="hidden" value=' . $_POST['Comparator'] . ' name="Comparator">
			<input type="hidden" value=' . $_POST['SalesType'] . ' name="SalesType">';

		$PriceCounter =0;
		while ($myrow = DB_fetch_array($result)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			//get cost
			if ($myrow['cost'] == '') {
				$Cost = 0;
			} else {
				$Cost = $myrow['cost'];
			} /*end of else Cost */

			//variables for update
			echo '<input type="hidden" value=' . $myrow['stockid'] . ' name="StockID_' . $PriceCounter .'">
					<input type="hidden" value=' . $myrow['debtorno'] . ' name="DebtorNo_' . $PriceCounter .'">
					<input type="hidden" value=' . $myrow['branchcode'] . ' name="BranchCode_' . $PriceCounter .'">
					<input type="hidden" value=' . $myrow['startdate'] . ' name="StartDate_' . $PriceCounter .'">
					<input type="hidden" value=' . $myrow['enddate'] . ' name="EndDate_' . $PriceCounter .'">';
			//variable for current margin
			if ($myrow['price'] != 0){
				$CurrentGP = ($myrow['price']-$Cost)*100 / $myrow['price'];
			} else {
				$CurrentGP = 0;
			}
			//variable for proposed
			$Proposed = $Cost * $_POST['Margin'];
			if ($myrow['enddate']=='0000-00-00'){
				$EndDateDisplay = _('No End Date');
			} else {
				$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
			}
			echo '   <td>' . $myrow['stockid'] . '</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $myrow['customer'] . '</td>
						<td>' . $myrow['branch'] . '</td>
						<td>' . ConvertSQLDate($myrow['startdate']) . '</td>
						<td>' . $EndDateDisplay . '</td>
						<td class="number">' . number_format($Cost, 2) . '</td>
						<td class="number">' . number_format($CurrentGP, 1) . '%</td>
						<td class="number">' . number_format($Proposed, 2) . '</td>
						<td><input type="text" class="number" name="Price_' . $PriceCounter . '" maxlength=14 size=10 value="' . $myrow['price'] . '"></td>
					</tr> ';
			$PriceCounter++;
		} //end of looping
		echo '<tr>
			<td style="text-align:right" colspan=4><input type=submit name=submit value=' . _('Update') . '></td>
			<td style="text-align:left" colspan=3><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><input type=submit  value=' . _('Back') . '><a/></td>
			 </tr></form>';
	} else {
		prnMsg(_('There were no prices meeting the criteria specified to review'),'info');
		echo '<p><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Back') . '<a/></div><p>';
	}
} else { /*The option to submit was not hit so display form */
	echo '<div class="page_help_text">' . _('Use this report to display price list with the cost.') . '</div><br>';
	echo '</br></br><form action="' . $_SERVER['PHP_SELF'] . '" method="post"><table class=selection>';

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
	if (!isset($_POST['Margin'])){
		$_POST['Margin']=1;
	}
	echo '<td>
				<input type="text" class="number" name="Margin" MAXLENGTH =8 size=8 value=' .$_POST['Margin'] . '></td></tr>';
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