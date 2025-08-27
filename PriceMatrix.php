<?php

// The scripts used to provide a Price break matrix for those users who like selling product in quantity break at different constant price.

require(__DIR__ . '/includes/session.php');

$Title = __('Price break matrix Maintenance');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['StartDate'])){$_POST['StartDate'] = ConvertSQLDate($_POST['StartDate']);}
if (isset($_POST['EndDate'])){$_POST['EndDate'] = ConvertSQLDate($_POST['EndDate']);}

$Errors = array();
$i=1;

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	if(isset($_POST['StockID'])){
		$StockID = trim(strtoupper($_POST['StockID']));
	}
	if (!is_numeric(filter_number_format($_POST['QuantityBreak']))){
		prnMsg( __('The quantity break must be entered as a positive number'),'error');
		$InputError =1;
	}

	if (filter_number_format($_POST['QuantityBreak'])<=0){
		prnMsg( __('The quantity of all items on an order in the discount category') . ' ' . $StockID . ' ' . __('at which the price will apply is 0 or less than 0') . '. ' . __('Positive numbers are expected for this entry'),'warn');
		$InputError =1;
	}
	if (!is_numeric(filter_number_format($_POST['Price']))){
		prnMsg( __('The price must be entered as a positive number'),'warn');
		$InputError =1;
	}
	if (!Is_Date($_POST['StartDate'])){
		$InputError = 1;
		prnMsg(__('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (!Is_Date($_POST['EndDate'])){
		$InputError = 1;
		prnMsg(__('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate'])){
			$InputError = 1;
			prnMsg(__('The end date is expected to be after the start date, enter an end date after the start date for this price'),'error');
		}
	}


	if(Is_Date($_POST['EndDate'])){
		$SQLEndDate = FormatDateForSQL($_POST['EndDate']);
	}
	if(Is_Date($_POST['StartDate'])){
		$SQLStartDate = FormatDateForSQL($_POST['StartDate']);
	}
	$SQL = "SELECT COUNT(salestype)
				FROM pricematrix
			WHERE stockid='".$StockID."'
			AND startdate='".$SQLStartDate."'
			AND enddate='".$SQLEndDate."'
		        AND salestype='".$_POST['SalesType']."'
			AND currabrev='".$_POST['CurrAbrev']."'
			AND quantitybreak='".$_POST['QuantityBreak']."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]!=0 AND !isset($_POST['OldTypeAbbrev']) AND !isset($_POST['OldCurrAbrev'])){
		prnMsg(__('This price has already been entered. To change it you should edit it'),'warn');
		$InputError = 1;
	}

	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND mb_strlen($StockID)>1 AND $InputError !=1){

		/* Update existing prices */
		$SQL = "UPDATE pricematrix SET
					salestype='" . $_POST['SalesType'] . "',
					currabrev='" . $_POST['CurrAbrev'] . "',
					price='" . filter_number_format($_POST['Price']) . "',
					startdate='" . $SQLStartDate . "',
					enddate='" . $SQLEndDate . "',
					quantitybreak='" . filter_number_format($_POST['QuantityBreak']) . "'
				WHERE stockid='" . $StockID . "'
				AND startdate='" . $_POST['OldStartDate'] . "'
				AND enddate='" . $_POST['OldEndDate'] . "'
				AND salestype='" . $_POST['OldTypeAbbrev'] . "'
				AND currabrev='" . $_POST['OldCurrAbrev'] . "'
				AND quantitybreak='" . filter_number_format($_POST['OldQuantityBreak']) . "'";

		$ErrMsg = __('Could not be update the existing prices');
		$Result = DB_query($SQL, $ErrMsg);

		ReSequenceEffectiveDates ($StockID, $_POST['SalesType'],$_POST['CurrAbrev'],$_POST['QuantityBreak']);

		prnMsg(__('The price has been updated'),'success');
	} elseif ($InputError != 1) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

		$SQL = "INSERT INTO pricematrix (salestype,
							stockid,
							quantitybreak,
							price,
							currabrev,
							startdate,
							enddate)
					VALUES('" . $_POST['SalesType'] . "',
						'" . $_POST['StockID'] . "',
						'" . filter_number_format($_POST['QuantityBreak']) . "',
						'" . filter_number_format($_POST['Price']) . "',
						'" . $_POST['CurrAbrev'] . "',
						'" . $SQLStartDate . "',
						'" . $SQLEndDate . "')";
		$ErrMsg = __('Failed to insert price data');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg( __('The price matrix record has been added'),'success');
		echo '<br />';
		unset($_POST['StockID']);
		unset($_POST['SalesType']);
		unset($_POST['QuantityBreak']);
		unset($_POST['Price']);
		unset($_POST['CurrAbrev']);
		unset($_POST['StartDate']);
		unset($_POST['EndDate']);
		unset($SQLEndDate);
		unset($SQLStartDate);
	}
} elseif (isset($_GET['Delete']) and $_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$SQL="DELETE FROM pricematrix
		WHERE stockid='" .$_GET['StockID'] . "'
		AND salestype='" . $_GET['SalesType'] . "'
		AND quantitybreak='" . $_GET['QuantityBreak']."'
		AND price='" . $_GET['Price'] . "'
		AND startdate='" . $_GET['StartDate'] . "'
		AND enddate='" . $_GET['EndDate'] . "'";
	$ErrMsg = __('Failed to delete price data');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg( __('The price matrix record has been deleted'),'success');
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($_GET['Edit'])){
	echo '<input type="hidden" name="OldTypeAbbrev" value="' . $_GET['TypeAbbrev'] . '" />';
	echo '<input type="hidden" name="OldCurrAbrev" value="' . $_GET['CurrAbrev'] . '" />';
	echo '<input type="hidden" name="OldStartDate" value="' . $_GET['StartDate'] . '" />';
	echo '<input type="hidden" name="OldEndDate" value="' . $_GET['EndDate'] . '" />';
	echo '<input type="hidden" name="OldQuantityBreak" value="' . $_GET['QuantityBreak'] . '" />';
	$_POST['StartDate'] = $_GET['StartDate'];
	$_POST['TypeAbbrev'] = $_GET['TypeAbbrev'];
	$_POST['Price'] = $_GET['Price'];
	$_POST['CurrAbrev'] = $_GET['CurrAbrev'];
	$_POST['StartDate'] = ConvertSQLDate($_GET['StartDate']);
	$_POST['EndDate'] = ConvertSQLDate($_GET['EndDate']);
       	$_POST['QuantityBreak'] = $_GET['QuantityBreak'];
}
$SQL = "SELECT currabrev FROM currencies";
$Result = DB_query($SQL);
require_once('includes/CurrenciesArray.php');
echo '<fieldset>
		<legend>', __('Price Matrix For'), ' ', $_POST['StockID'], '</legend>';
echo '<field>
		<label for="CurrAbrev">' . __('Currency') . ':</label>
		<select name="CurrAbrev">';
while ($MyRow = DB_fetch_array($Result)){
	echo '<option';
	if (isset($_POST['CurrAbrev']) AND $MyRow['currabrev']==$_POST['CurrAbrev']){
		echo ' selected="selected"';
	}
	echo ' value="' . $MyRow['currabrev'] . '">' . $CurrencyName[$MyRow['currabrev']] . '</option>';
} // End while loop
DB_free_result($Result);
echo '</select>';

$SQL = "SELECT typeabbrev,
		sales_type
		FROM salestypes";

$Result = DB_query($SQL);

echo '<field>
		<label for="SalesType">' . __('Customer Price List') . ' (' . __('Sales Type') . '):</label>';
echo '<select tabindex="1" name="SalesType">';

while ($MyRow = DB_fetch_array($Result)){
	if (isset($_POST['SalesType']) and $MyRow['typeabbrev']==$_POST['SalesType']){
		echo '<option selected="selected" value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
	}
}

echo '</select>
	</field>';
if(isset($_GET['StockID'])){
	$StockID = trim($_GET['StockID']);
}elseif(isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}elseif(!isset($StockID)){
	prnMsg(__('You must select a stock item first before set a price maxtrix'),'error');
	include('includes/footer.php');
	exit();
}
echo '<input type="hidden" name="StockID" value="' . $StockID . '" />';
if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = GetMySQLMaxDate();
}
if (!isset($_POST['QuantityBreak'])) {
	$_POST['QuantityBreak'] = 0;
}
if (!isset($_POST['Price'])) {
	$_POST['Price'] = 0;
}
echo '<field>
		<label for="StartDate">'. __('Price Effective From Date') . ':</label>
		<input type="date" name="StartDate" required="required" size="11" maxlength="10" title="" value="' . FormatDateForSQL($_POST['StartDate']) . '" />
		<fieldhelp>' . __('Enter the date from which this price should take effect.') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="EndDate">' . __('Price Effective To Date') . ':</label>
		<input type="date" name="EndDate" size="11" maxlength="10" title="" value="' . FormatDateForSQL($_POST['EndDate']) . '" />
		<fieldhelp>' . __('Enter the date to which this price should be in effect to, or leave empty if the price should continue indefinitely') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="QuantityBreak">' . __('Quantity Break') . '</label>
		<input class="integer' . (in_array('QuantityBreak',$Errors) ? ' inputerror' : '') . '" tabindex="3" required="required" type="number" name="QuantityBreak" size="10" value="'. $_POST['QuantityBreak'].'" maxlength="10" />
	</field>
	<field>
		<label for="Price">' . __('Price') . ' :</label>
		<input class="number' . (in_array('Price',$Errors) ? ' inputerror' : '') . '" tabindex="4" type="text" required="required" name="Price" value="'.$_POST['Price'].'" title="' . __('The price to apply to orders where the quantity exceeds the specified quantity') . '" size="5" maxlength="5" />
	</field>
	</fieldset>
	<div class="centre">
		<input tabindex="5" type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>';

$SQL = "SELECT sales_type,
			salestype,
			stockid,
			startdate,
			enddate,
			quantitybreak,
			price,
			currencies.currabrev,
			currencies.currency,
			currencies.decimalplaces AS currdecimalplaces
		FROM pricematrix INNER JOIN salestypes
			ON pricematrix.salestype=salestypes.typeabbrev
		INNER JOIN currencies
		ON pricematrix.currabrev=currencies.currabrev
		WHERE pricematrix.stockid='" . $StockID . "'
		ORDER BY pricematrix.currabrev,
			salestype,
			stockid,
			quantitybreak";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table class="selection">';
	echo '<tr>
			<th>' . __('Currency') . '</th>
			<th>' . __('Sales Type') . '</th>
			<th>' . __('Price Effective From Date') . '</th>
			<th>' . __('Price Effective To Date') .'</th>
			<th>' . __('Quantity Break') . '</th>
			<th>' . __('Sell Price') . '</th>
			<th colspan="2"></th>
		</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=yes&amp;SalesType=' . $MyRow['salestype'] . '&amp;StockID=' . $MyRow['stockid'] . '&amp;QuantityBreak=' . $MyRow['quantitybreak'].'&amp;Price=' . $MyRow['price'] . '&amp;currabrev=' . $MyRow['currabrev'].'&amp;StartDate='.$MyRow['startdate'].'&amp;EndDate='.$MyRow['enddate'];
		$EditURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Edit=yes&amp;StockID=' . $MyRow['stockid'] . '&amp;TypeAbbrev=' . $MyRow['salestype'] . '&amp;CurrAbrev=' . $MyRow['currabrev'] . '&amp;Price=' . locale_number_format($MyRow['price'], $MyRow['currdecimalplaces']) . '&amp;StartDate=' . $MyRow['startdate'] . '&amp;EndDate=' . $MyRow['enddate'].'&amp;QuantityBreak=' . $MyRow['quantitybreak'];

		if (in_array(5, $_SESSION['AllowedPageSecurityTokens'])){
			echo '<tr class="striped_row">
					<td>', $MyRow['currency'], '</td>
					<td>', $MyRow['sales_type'], '</td>
					<td>', ConvertSQLDate($MyRow['startdate']), '</td>
					<td>', ConvertSQLDate($MyRow['enddate']), '</td>
					<td class="number">', $MyRow['quantitybreak'], '</td>
					<td class="number">', locale_number_format($MyRow['price'], $MyRow['currdecimalplaces']), '</td>
					<td><a href="', $DeleteURL, '" onclick="return confirm(\'' . __('Are you sure you wish to delete this discount matrix record?') . '\');">' . __('Delete') . '</a></td>
					<td><a href="', $EditURL, '">'.__('Edit').'</a></td>
				</tr>';
		} else {
			echo '<tr class="striped_row">
					<td>', $MyRow['currency'], '</td>
					<td>', $MyRow['sales_type'], '</td>
					<td>', ConvertSQLDate($MyRow['startdate']), '</td>
					<td>', ConvertSQLDate($MyRow['enddate']), '</td>
					<td class="number">', $MyRow['quantitybreak'], '</td>
					<td class="number">', locale_number_format($MyRow['price'], $MyRow['currdecimalplaces']), '</td>
				</tr>';

		}

	}
}

echo '</table>
	  </form>';

include('includes/footer.php');

function GetMySQLMaxDate () {
	switch ($_SESSION['DefaultDateFormat']){
		case 'd/m/Y':
			return '31/12/9999';
		case 'd.m.Y':
			return '31.12.9999';
		case 'm/d/Y':
			return '12/31/9999';
		case 'Y-m-d':
			return '9999-12-31';
		case 'Y/m/d':
			return '9999/12/31';
	}
}
function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $QuantityBreak) {

	/*This is quite complicated - the idea is that prices set up should be unique and there is no way two prices could be returned as valid - when getting a price in includes/GetPrice.php the logic is to first look for a price of the salestype/currency within the effective start and end dates - then if not get the price with a start date prior but a blank end date (the default price). We would not want two prices where one price falls inside another effective date range except in the case of a blank end date - ie no end date - the default price for the currency/salestype.
	I first thought that we would need to update the previous default price (blank end date), when a new default price is entered, to have an end date of the startdate of this new default price less 1 day - but this is  converting a default price into a special price which could result in having two special prices over the same date range - best to leave it unchanged and use logic in the GetPrice.php to ensure the correct default price is returned
	*
	* After further discussion (Ricard) if the new price has a blank end date - i.e. no end then the pre-existing price with no end date should be changed to have an end date just prior to the new default (no end date) price commencing
	*/
	//this is just the case where debtorno='' - see the Prices_Customer.php script for customer special prices
		$SQL = "SELECT price,
						startdate,
						enddate
				FROM pricematrix
				WHERE stockid='" . $Item . "'
				AND currabrev='" . $CurrAbbrev . "'
				AND salestype='" . $PriceList . "'
				AND quantitybreak='".$QuantityBreak."'
				ORDER BY startdate, enddate";
		$Result = DB_query($SQL);

		while ($MyRow = DB_fetch_array($Result)){
			if (isset($NextStartDate)){
				if (Date1GreaterThanDate2(ConvertSQLDate($MyRow['startdate']),$NextStartDate)){
					$NextStartDate = ConvertSQLDate($MyRow['startdate']);
					//Only if the previous enddate is after the new start date do we need to look at updates
					if (Date1GreaterThanDate2(ConvertSQLDate($EndDate),ConvertSQLDate($MyRow['startdate']))) {
						/*Need to make the end date the new start date less 1 day */
						$SQL = "UPDATE pricematrix SET enddate = '" . FormatDateForSQL(DateAdd($NextStartDate,'d',-1))  . "'
										WHERE stockid ='" .$Item . "'
										AND currabrev='" . $CurrAbbrev . "'
										AND salestype='" . $PriceList . "'
										AND startdate ='" . $StartDate . "'
										AND enddate = '" . $EndDate . "'
										AND quantitybreak ='" . $QuantityBreak . "'";
						$UpdateResult = DB_query($SQL);
					}
				} //end of if startdate  after NextStartDate - we have a new NextStartDate
			} //end of if set NextStartDate
				else {
					$NextStartDate = ConvertSQLDate($MyRow['startdate']);
			}
			$StartDate = $MyRow['startdate'];
			$EndDate = $MyRow['enddate'];
			$Price = $MyRow['price'];
		} // end of loop around all prices

} // end function ReSequenceEffectiveDates
