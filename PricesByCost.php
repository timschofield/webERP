<?php

require(__DIR__ . '/includes/session.php');

if (isset($_POST['submit']) OR isset($_POST['update']) && (@$_POST['Margin'] == '')) {
	header('Location: ' . htmlspecialchars_decode($RootPath) . '/PricesByCost.php');
	exit();
}

$Title = __('Update of Prices By A Multiple Of Cost');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Update Price By Cost') . '</p>';

if (isset($_POST['submit']) OR isset($_POST['update'])) {
	if ($_POST['Comparator'] == 1) {
		$Comparator = '<=';
	} else {
		$Comparator = '>=';
	} /*end of else Comparator */
	if ($_POST['StockCat'] != 'all') {
		$Category = " AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	} else {
		$Category ='';
	}/*end of else StockCat */

	$SQL = "SELECT 	stockmaster.stockid,
					stockmaster.description,
					prices.debtorno,
					prices.branchcode,
					(stockmaster.actualcost) as cost,
					prices.price as price,
					prices.debtorno AS customer,
					prices.branchcode AS branch,
					prices.startdate,
					prices.enddate,
					currencies.decimalplaces,
					currencies.rate
				FROM stockmaster INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
				INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
				WHERE stockmaster.discontinued = 0
					" . $Category . "
					AND   prices.price" . $Comparator . "(stockmaster.actualcost) * '" . filter_number_format($_POST['Margin']) . "'
					AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
					AND prices.currabrev ='" . $_POST['CurrCode'] . "'
					AND prices.enddate >= CURRENT_DATE";
	$Result = DB_query($SQL);
	$NumRow = DB_num_rows($Result);

	if ($_POST['submit'] == 'Update') {
			//Update Prices
		$PriceCounter =0;
		while ($MyRow = DB_fetch_array($Result)) {
			/*The logic here goes like this:
			 * 1. If the price at the same start and end date already exists then do nowt!!
			 * 2. If not then check if a price with the start date of today already exists - then we should be updating it
			 * 3. If not either of the above then insert the new price
			*/
			$SQLTestExists = "SELECT price FROM prices
								WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
								AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
								AND prices.currabrev ='" . $_POST['CurrCode'] . "'
								AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
								AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
								AND prices.startdate ='" . $_POST['StartDate_' . $PriceCounter] . "'
								AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'
								AND prices.price ='" . filter_number_format($_POST['Price_' . $PriceCounter]) . "'";
			$TestExistsResult = DB_query($SQLTestExists);
			if (DB_num_rows($TestExistsResult)==0){ //the price doesn't currently exist
				//now check to see if a new price has already been created from start date of today

				$SQLTestExists = "SELECT price FROM prices
									WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
										AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
										AND prices.currabrev ='" . $_POST['CurrCode'] . "'
										AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
										AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
										AND prices.startdate = CURRENT_DATE";
				$TestExistsResult = DB_query($SQLTestExists);
				if (DB_num_rows($TestExistsResult)==1){
					 //then we are updating
					$SQLUpdate = "UPDATE prices	SET price = '" . filter_number_format($_POST['Price_' . $PriceCounter]) . "'
									WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
										AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
										AND prices.currabrev ='" . $_POST['CurrCode'] . "'
										AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
										AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
										AND prices.startdate = CURRENT_DATE
										AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'";
				$ResultUpdate = DB_query($SQLUpdate);
				} else { //there is not a price already starting today so need to create one
					//update the old price to have an end date of yesterday too
					$SQLUpdate = "UPDATE prices	SET enddate = '" . FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1)) . "'
									WHERE stockid = '" . $_POST['StockID_' . $PriceCounter] . "'
										AND prices.typeabbrev ='" . $_POST['SalesType'] . "'
										AND prices.currabrev ='" . $_POST['CurrCode'] . "'
										AND prices.debtorno ='" . $_POST['DebtorNo_' . $PriceCounter] . "'
										AND prices.branchcode ='" . $_POST['BranchCode_' . $PriceCounter] . "'
										AND prices.startdate ='" . $_POST['StartDate_' . $PriceCounter] . "'
										AND prices.enddate ='" . $_POST['EndDate_' . $PriceCounter] . "'";
					$Result = DB_query($SQLUpdate);
					//we need to add a new price from today
					$SQLInsert = "INSERT INTO prices (	stockid,
														price,
														typeabbrev,
														currabrev,
														debtorno,
														branchcode,
														startdate
													) VALUES (
														'" . $_POST['StockID_' . $PriceCounter] . "',
														'" . filter_number_format($_POST['Price_' . $PriceCounter]) . "',
														'" . $_POST['SalesType'] . "',
														'" . $_POST['CurrCode'] . "',
														'" . $_POST['DebtorNo_' . $PriceCounter] . "',
														'" . $_POST['BranchCode_' . $PriceCounter] . "',
														CURRENT_DATE
													)";
					$ResultInsert = DB_query($SQLInsert);
				}
			}
			$PriceCounter++;
		}//end while loop
		DB_free_result($Result); //clear the old result
		$Result = DB_query($SQL); //re-run the query with the updated prices
		$NumRow = DB_num_rows($Result); // get the new number - should be the same!!
	}

	$SQLcat = "SELECT categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "'";
	$ResultCat = DB_query($SQLcat);
	$CategoryRow = DB_fetch_array($ResultCat);

	$SQLtype = "SELECT sales_type
				FROM salestypes
				WHERE typeabbrev='" . $_POST['SalesType'] . "'";
	$ResultType = DB_query($SQLtype);
	$SalesTypeRow = DB_fetch_array($ResultType);

	if (isset($CategoryRow['categorgdescription'])) {
		$CategoryText = $CategoryRow['categorgdescription'] . ' ' . __('category');
	} else {
		$CategoryText = __('all Categories');
	} /*end of else Category */

	echo '<div class="page_help_text">' . __('Items in') . ' ' . $CategoryText . ' ' . __('With Prices') . ' ' . $Comparator . '' . $_POST['Margin'] . ' ' . __('times') . ' ' . __('Cost in Price List') . ' ' . $SalesTypeRow['sales_type'] . '</div><br /><br />';

	if ($NumRow > 0) { //the number of prices returned from the main prices query is
		echo '<form action="' .htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" method="post" id="update">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo'<input type="hidden" value="' . $_POST['StockCat'] . '" name="StockCat" />
			<input type="hidden" value="' . $_POST['Margin'] . '" name="Margin" />
			<input type="hidden" value="' . $_POST['CurrCode'] . '" name="CurrCode" />
			<input type="hidden" value="' . $_POST['Comparator'] . '" name="Comparator" />
			<input type="hidden" value="' . $_POST['SalesType'] . '" name="SalesType" />';

		echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Code') . '</th>
					<th class="SortedColumn">' . __('Description') . '</th>
					<th class="SortedColumn">' . __('Customer') . '</th>
					<th class="SortedColumn">' . __('Branch') . '</th>
					<th class="SortedColumn">' . __('Start Date') . '</th>
					<th class="SortedColumn">' . __('End Date') . '</th>
					<th class="SortedColumn">' . __('Cost') . '</th>
					<th class="SortedColumn">' . __('GP %') . '</th>
					<th class="SortedColumn">' . __('Price Proposed') . '</th>
					<th class="SortedColumn">' . __('List Price') . '</th>
				<tr>
			</thead>
			<tbody>';

		$PriceCounter =0;
		while ($MyRow = DB_fetch_array($Result)) {

			//get cost
			if ($MyRow['cost'] == '') {
				$Cost = 0;
			} else {
				$Cost = $MyRow['cost'];
			} /*end of else Cost */

			//variables for update
			echo '<input type="hidden" value="' . $MyRow['stockid'] . '" name="StockID_' . $PriceCounter .'" />
				<input type="hidden" value="' . $MyRow['debtorno'] . '" name="DebtorNo_' . $PriceCounter .'" />
				<input type="hidden" value="' . $MyRow['branchcode'] . '" name="BranchCode_' . $PriceCounter .'" />
				<input type="hidden" value="' . $MyRow['startdate'] . '" name="StartDate_' . $PriceCounter .'" />
				<input type="hidden" value="' . $MyRow['enddate'] . '" name="EndDate_' . $PriceCounter .'" />';
			//variable for current margin
			if ($MyRow['price'] != 0){
				$CurrentGP = (($MyRow['price']/$MyRow['rate'])-$Cost)*100 / ($MyRow['price']/$MyRow['rate']);
			} else {
				$CurrentGP = 0;
			}
			//variable for proposed
			$ProposedPrice = $Cost * filter_number_format($_POST['Margin']);
			if ($MyRow['enddate']=='9999-12-31'){
				$EndDateDisplay = __('No End Date');
			} else {
				$EndDateDisplay = ConvertSQLDate($MyRow['enddate']);
			}
			echo '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $MyRow['customer'] . '</td>
					<td>' . $MyRow['branch'] . '</td>
					<td class="date">' . ConvertSQLDate($MyRow['startdate']) . '</td>
					<td class="date">' . $EndDateDisplay . '</td>
					<td class="number">' . locale_number_format($Cost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CurrentGP, 1) . '%</td>
					<td class="number">' . locale_number_format($ProposedPrice, $MyRow['decimalplaces']) . '</td>
					<td><input type="text" class="number" name="Price_' . $PriceCounter . '" maxlength="14" size="10" value="' . locale_number_format($MyRow['price'],$MyRow['decimalplaces']) . '" /></td>
				</tr> ';
			$PriceCounter++;
		} //end of looping

		echo '</tbody>
			<tfoot>
				<tr>
			<td class="number" colspan="4"><input type="submit" name="submit" value="' . __('Update') . '" onclick="return confirm(\'' . __('If the prices above do not have a commencement date as today, this will create new prices with commencement date of today at the entered figures and update the existing prices with historical start dates to have an end date of yesterday. Are You Sure?') . '\');" /></td>
			<td class="text" colspan="3"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '"><input type="submit" value="' . __('Back') . '" /></a></td>
			 </tr>
			</tfoot>
			</table>
			</form>';
	} else {
		prnMsg(__('There were no prices meeting the criteria specified to review'),'info');
		echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Back') . '<a/></div>';
	}
} else { /*The option to submit was not hit so display form */
	echo '<div class="page_help_text">' . __('Prices can be displayed based on their relation to cost') . '</div><br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Bulk Price Update'), '</legend>';

	$SQL = "SELECT categoryid, categorydescription
			  FROM stockcategory
			  ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	echo '<field>
			<label for="StockCat">' . __('Category') . ':</label>
			<select name="StockCat">';
	echo '<option value="all">' . __('All Categories') . '</option>';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="Margin">' . __('Price') . '</label>
			<select name="Comparator">
				<option value="1">' . __('Less than or equal to') . '</option>
				<option value="2">' . __('Greater than or equal to') . '</option>';
	if ($_SESSION['WeightedAverageCosting']==1) {
		echo '</select>' . ' '. __('Average Cost') . ' x ';
	} else {
		echo '</select>' . ' '. __('Standard Cost') . ' x ';
	}
	if (!isset($_POST['Margin'])){
		$_POST['Margin']=1;
	}
	echo '<input type="text" class="number" name="Margin" maxlength="8" size="8" value="' .$_POST['Margin'] . '" />
		</field>';
	$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
	echo '<field>
			<label for="SalesType">' . __('Sales Type') . '/' . __('Price List') . ':</label>
			<select name="SalesType">';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($_POST['SalesType'] == $MyRow['typeabbrev']) {
			echo '<option selected="selected" value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
		}
	} //end while loop
	DB_data_seek($Result, 0);
	$Result = DB_query("SELECT currency, currabrev FROM currencies");
	echo '</select>
		</field>';

	echo '<field>
			<label for="CurrCode">' . __('Currency') . ':</label>
			<select name="CurrCode">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['CurrCode']) and $_POST['CurrCode'] == $MyRow['currabrev']) {
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		}
	} //end while loop
	DB_data_seek($Result, 0);
	echo '</select>
		</field>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Submit') . '" /></div>
		</div>
	</form>';
} /*end of else not submit */
include('includes/footer.php');
