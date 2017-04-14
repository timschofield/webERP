<?php

//The scripts used to provide a Price break matrix for those users who like selling product in quantity break at different constant price. 

include('includes/session.php');
$Title = _('Price break matrix Maintenance');
include('includes/header.php');

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
$i=1;

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	if(isset($_POST['StockID'])){
		$StockID = trim(strtoupper($_POST['StockID']));
	}
	if (!is_numeric(filter_number_format($_POST['QuantityBreak']))){
		prnMsg( _('The quantity break must be entered as a positive number'),'error');
		$InputError =1;
	}

	if (filter_number_format($_POST['QuantityBreak'])<=0){
		prnMsg( _('The quantity of all items on an order in the discount category') . ' ' . $StockID . ' ' . _('at which the price will apply is 0 or less than 0') . '. ' . _('Positive numbers are expected for this entry'),'warn');
		$InputError =1;
	}
	if (!is_numeric(filter_number_format($_POST['Price']))){
		prnMsg( _('The price must be entered as a positive number'),'warn');
		$InputError =1;
	}
	if (!Is_Date($_POST['StartDate'])){
		$InputError = 1;
		prnMsg(_('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (!Is_Date($_POST['EndDate'])){
		$InputError = 1;
		prnMsg(_('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate'])){
			$InputError = 1;
			prnMsg(_('The end date is expected to be after the start date, enter an end date after the start date for this price'),'error');
		}
	}
	

	if(Is_Date($_POST['EndDate'])){
		$SQLEndDate = FormatDateForSQL($_POST['EndDate']);
	}
	if(Is_Date($_POST['StartDate'])){
		$SQLStartDate = FormatDateForSQL($_POST['StartDate']);
	}
	$sql = "SELECT COUNT(salestype)
				FROM pricematrix
			WHERE stockid='".$StockID."'
			AND startdate='".$SQLStartDate."'
			AND enddate='".$SQLEndDate."'
		        AND salestype='".$_POST['SalesType']."'
			AND currabrev='".$_POST['CurrAbrev']."'
			AND quantitybreak='".$_POST['QuantityBreak']."'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]!=0 AND !isset($_POST['OldTypeAbbrev']) AND !isset($_POST['OldCurrAbrev'])){
		prnMsg(_('This price has already been entered. To change it you should edit it'),'warn');
		$InputError = 1;
	}

	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND mb_strlen($StockID)>1 AND $InputError !=1){

		/* Update existing prices */
		$sql = "UPDATE pricematrix SET 
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

		$ErrMsg = _('Could not be update the existing prices');
		$result = DB_query($sql,$ErrMsg);

		ReSequenceEffectiveDates ($StockID, $_POST['SalesType'],$_POST['CurrAbrev'],$_POST['QuantityBreak'],$db);

		prnMsg(_('The price has been updated'),'success');
	} elseif ($InputError != 1) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

		$sql = "INSERT INTO pricematrix (salestype,
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
		$ErrMsg = _('Failed to insert price data');
		$result = DB_query($sql,$ErrMsg);
		prnMsg( _('The price matrix record has been added'),'success');
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

	$sql="DELETE FROM pricematrix
		WHERE stockid='" .$_GET['StockID'] . "'
		AND salestype='" . $_GET['SalesType'] . "'
		AND quantitybreak='" . $_GET['QuantityBreak']."'
		AND price='" . $_GET['Price'] . "'
		AND startdate='" . $_GET['StartDate'] . "'
		AND enddate='" . $_GET['EndDate'] . "'";
	$ErrMsg = _('Failed to delete price data');
	$result = DB_query($sql,$ErrMsg);
	prnMsg( _('The price matrix record has been deleted'),'success');
	echo '<br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
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
$result = DB_query($SQL);
require_once('includes/CurrenciesArray.php');
echo '<table class="selection">';
echo '<tr><td>' . _('Currency') . ':</td>
	<td><select name="CurrAbrev">';
while ($myrow = DB_fetch_array($result)){
	echo '<option';
	if (isset($_POST['CurrAbrev']) AND $myrow['currabrev']==$_POST['CurrAbrev']){
		echo ' selected="selected"';
	}
	echo ' value="' . $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . '</option>';
} // End while loop
DB_free_result($result);
echo '</select></td>';

$sql = "SELECT typeabbrev,
		sales_type
		FROM salestypes";

$result = DB_query($sql);

echo '<tr><td>' . _('Customer Price List') . ' (' . _('Sales Type') . '):</td><td>';

echo '<select tabindex="1" name="SalesType">';

while ($myrow = DB_fetch_array($result)){
	if (isset($_POST['SalesType']) and $myrow['typeabbrev']==$_POST['SalesType']){
		echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
	} else {
		echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
	}
}

echo '</select></td></tr>';
if(isset($_GET['StockID'])){
	$StockID = trim($_GET['StockID']);
}elseif(isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}elseif(!isset($StockID)){
	prnMsg(_('You must select a stock item first before set a price maxtrix'),'error');
	include('includes/footer.php');
	exit;
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
echo '<tr><td>'. _('Price Effective From Date') . ':</td>
	<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="StartDate" required="required" size="10" maxlength="10" title="' . _('Enter the date from which this price should take effect.') . '" value="' . $_POST['StartDate'] . '" /></td></tr>';
echo '<tr><td>' . _('Price Effective To Date') . ':</td>
			<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="EndDate" size="10" maxlength="10" title="' . _('Enter the date to which this price should be in effect to, or leave empty if the price should continue indefinitely') . '" value="' . $_POST['EndDate'] . '" />';


echo '<tr>
		<td>' . _('Quantity Break') . '</td>
		<td><input class="integer' . (in_array('QuantityBreak',$Errors) ? ' inputerror' : '') . '" tabindex="3" required="required" type="number" name="QuantityBreak" size="10" value="'. $_POST['QuantityBreak'].'" maxlength="10" /></td>
	</tr>
	<tr>
		<td>' . _('Price') . ' :</td>
		<td><input class="number' . (in_array('Price',$Errors) ? ' inputerror' : '') . '" tabindex="4" type="text" required="required" name="Price" value="'.$_POST['Price'].'" title="' . _('The price to apply to orders where the quantity exceeds the specified quantity') . '" size="5" maxlength="5" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
	<br />';

$sql = "SELECT sales_type,
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

$result = DB_query($sql);

echo '<table class="selection">';
echo '<tr>
		<th>' . _('Currency') . '</th>
		<th>' . _('Sales Type') . '</th>
		<th>' . _('Price Effective From Date') . '</th>
		<th>' . _('Price Effective To Date') .'</th>
		<th>' . _('Quantity Break') . '</th>
		<th>' . _('Sell Price') . '</th>
	</tr>';

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=yes&amp;SalesType=' . $myrow['salestype'] . '&amp;StockID=' . $myrow['stockid'] . '&amp;QuantityBreak=' . $myrow['quantitybreak'].'&amp;Price=' . $myrow['price'] . '&amp;currabrev=' . $myrow['currabrev'].'&amp;StartDate='.$myrow['startdate'].'&amp;EndDate='.$myrow['enddate'];
	$EditURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Edit=yes&amp;StockID=' . $myrow['stockid'] . '&amp;TypeAbbrev=' . $myrow['salestype'] . '&amp;CurrAbrev=' . $myrow['currabrev'] . '&amp;Price=' . locale_number_format($myrow['price'], $myrow['currdecimalplaces']) . '&amp;StartDate=' . $myrow['startdate'] . '&amp;EndDate=' . $myrow['enddate'].'&amp;QuantityBreak=' . $myrow['quantitybreak'];

    if (in_array(5, $_SESSION['AllowedPageSecurityTokens'])){
	    printf('<td>%s</td>
		    	<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td><a href="%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this discount matrix record?') . '\');">' . _('Delete') . '</a></td>
			<td><a href="%s">'._('Edit').'</a></td>
			</tr>',
			$myrow['currency'],
			$myrow['sales_type'],
			ConvertSQLDate($myrow['startdate']),
			ConvertSQLDate($myrow['enddate']),
			$myrow['quantitybreak'],
			$myrow['price'] ,
			$DeleteURL,
			$EditURL);
    }else {
	    printf('<td>%s</td>
		    	<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>',
			$myrow['currency'],
			$myrow['sales_type'],
			ConvertSQLDate($myrow['startdate']),
			ConvertSQLDate($myrow['enddate']),
			$myrow['quantitybreak'],
			$myrow['price']);

    }

}

echo '</table>
      </div>
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
function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $QuantityBreak,$db) {

	/*This is quite complicated - the idea is that prices set up should be unique and there is no way two prices could be returned as valid - when getting a price in includes/GetPrice.inc the logic is to first look for a price of the salestype/currency within the effective start and end dates - then if not get the price with a start date prior but a blank end date (the default price). We would not want two prices where one price falls inside another effective date range except in the case of a blank end date - ie no end date - the default price for the currency/salestype.
	I first thought that we would need to update the previous default price (blank end date), when a new default price is entered, to have an end date of the startdate of this new default price less 1 day - but this is  converting a default price into a special price which could result in having two special prices over the same date range - best to leave it unchanged and use logic in the GetPrice.inc to ensure the correct default price is returned
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
		$result = DB_query($SQL);

		while ($myrow = DB_fetch_array($result)){
			if (isset($NextStartDate)){
				if (Date1GreaterThanDate2(ConvertSQLDate($myrow['startdate']),$NextStartDate)){
					$NextStartDate = ConvertSQLDate($myrow['startdate']);
					//Only if the previous enddate is after the new start date do we need to look at updates
					if (Date1GreaterThanDate2(ConvertSQLDate($EndDate),ConvertSQLDate($myrow['startdate']))) {
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
					$NextStartDate = ConvertSQLDate($myrow['startdate']);
			}
			$StartDate = $myrow['startdate'];
			$EndDate = $myrow['enddate'];
			$Price = $myrow['price'];
		} // end of loop around all prices

		

} // end function ReSequenceEffectiveDates

?>
