<?php
/* $Revision: 1.14 $ */
/* $Id$*/

$PageSecurity = 9;

include('includes/session.inc');

$title = _('Item Prices');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

//initialise no input errors assumed initially before we test
$InputError = 0;


if (isset($_GET['Item'])){
	$Item = trim(strtoupper($_GET['Item']));
}elseif (isset($_POST['Item'])){
	$Item = trim(strtoupper($_POST['Item']));
}

if (!isset($_POST['TypeAbbrev']) OR $_POST['TypeAbbrev']==""){
	$_POST['TypeAbbrev'] = $_SESSION['DefaultPriceList'];
}

if (!isset($_POST['CurrAbrev'])){
	$_POST['CurrAbrev'] = $_SESSION['CompanyRecord']['currencydefault'];
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Search') .
		'" alt="">' . '</img>' . $title.'</p>';

echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</a><br>';


$result = DB_query("SELECT stockmaster.description,
							stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='".$Item."'",$db);
$myrow = DB_fetch_row($result);

if (DB_num_rows($result)==0){
	prnMsg( _('The part code entered does not exist in the database') . '. ' . _('Only valid parts can have prices entered against them'),'error');
	$InputError=1;
}


if (!isset($Item)){
	echo '<p>';
	prnMsg (_('An item must first be selected before this page is called') . '. ' . _('The product selection page should call this page with a valid product code'),'error');
	include('includes/footer.inc');
	exit;
}

$PartDescription = $myrow[0];

if ($myrow[1]=='K'){
	prnMsg(_('The part selected is a kit set item') .', ' . _('these items explode into their components when selected on an order') . ', ' . _('prices must be set up for the components and no price can be set for the whole kit'),'error');
	exit;
}

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	// This gives some date in 1999?? $ZeroDate = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,0,0,0));

	if (!is_double((double) trim($_POST['Price'])) OR $_POST['Price']=="") {
		$InputError = 1;
		prnMsg( _('The price entered must be numeric'),'error');
	}
	if (! Is_Date($_POST['StartDate'])){
		$InputError =1;
		prnMsg (_('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}

	if (FormatDateForSQL($_POST['EndDate'])!='0000-00-00'){
		if (! Is_Date($_POST['EndDate']) AND $_POST['EndDate']!=''){
			$InputError =1;
			prnMsg (_('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		}
		if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate']) AND $_POST['EndDate']!='' AND FormatDateForSQL($_POST['EndDate'])!='0000-00-00'){
			$InputError =1;
			prnMsg (_('The end date is expected to be after the start date, enter an end date after the start date for this price'),'error');
		}
		if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['EndDate']) AND $_POST['EndDate']!='' AND FormatDateForSQL($_POST['EndDate'])!='0000-00-00'){
			$InputError =1;
			prnMsg(_('The end date is expected to be after today. There is no point entering a new price where the effective date is before today!'),'error');
		}
	}
	if (Is_Date($_POST['EndDate'])){
		$SQLEndDate = FormatDateForSQL($_POST['EndDate']);
	} else {
		$SQLEndDate = '0000-00-00';
	}
	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND strlen($Item)>1 AND $InputError !=1) {

		/* Need to see if there is also a price entered that has an end date after the start date of this price and if so we will need to update it so there is no ambiguity as to which price will be used*/


		//editing an existing price
		$sql = "UPDATE prices SET
					typeabbrev='" . $_POST['TypeAbbrev'] . "',
					currabrev='" . $_POST['CurrAbrev'] . "',
					price='" . $_POST['Price'] . "',
					startdate='" . FormatDateForSQL($_POST['StartDate']) . "',
					enddate='" . $SQLEndDate . "'
				WHERE prices.stockid='".$Item."'
				AND startdate='" .$_POST['OldStartDate'] . "'
				AND enddate ='" . $_POST['OldEndDate'] . "'
				AND prices.typeabbrev='" . $_POST['OldTypeAbbrev'] . "'
				AND prices.currabrev='" . $_POST['OldCurrAbrev'] . "'
				AND prices.debtorno=''";

		$ErrMsg = _('Could not be update the existing prices');
		$result = DB_query($sql,$db,$ErrMsg);

		ReSequenceEffectiveDates ($Item, $_POST['TypeAbbrev'], $_POST['CurrAbrev'], $db) ;

		prnMsg(_('The price has been updated'),'success');

	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */

		$sql = "INSERT INTO prices (stockid,
									typeabbrev,
									currabrev,
									startdate,
									enddate,
									price)
							VALUES ('$Item',
								'" . $_POST['TypeAbbrev'] . "',
								'" . $_POST['CurrAbrev'] . "',
								'" . FormatDateForSQL($_POST['StartDate']) . "',
								'" . $SQLEndDate. "',
								'" . $_POST['Price'] . "')";
		$ErrMsg = _('The new price could not be added');
		$result = DB_query($sql,$db,$ErrMsg);

		ReSequenceEffectiveDates ($Item, $_POST['TypeAbbrev'], $_POST['CurrAbrev'], $db) ;
		prnMsg(_('The new price has been inserted'),'success');
	}
	unset($_POST['Price']);
	unset($_POST['StartDate']);
	unset($_POST['EndDate']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM prices
				WHERE prices.stockid = '". $Item ."'
				AND prices.typeabbrev='". $_GET['TypeAbbrev'] ."'
				AND prices.currabrev ='". $_GET['CurrAbrev'] ."'
				AND  prices.startdate = '" .$_GET['StartDate'] . "'
				AND  prices.enddate = '" . $_GET['EndDate'] . "'
				AND prices.debtorno=''";
	$ErrMsg = _('Could not delete this price');
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg( _('The selected price has been deleted'),'success');

}

//Always do this stuff
if ($InputError ==0){
	$sql = "SELECT currencies.currency,
	        	salestypes.sales_type,
			prices.price,
			prices.stockid,
			prices.typeabbrev,
			prices.currabrev,
			prices.startdate,
			prices.enddate
		FROM prices,
			salestypes,
			currencies
		WHERE prices.currabrev=currencies.currabrev
		AND prices.typeabbrev = salestypes.typeabbrev
		AND prices.stockid='".$Item."'
		AND prices.debtorno=''
		ORDER BY prices.currabrev,
			prices.typeabbrev,
			prices.startdate";

	$result = DB_query($sql,$db);

	if (DB_num_rows($result) > 0) {
		echo '<table class=selection>';
		echo '<tr><th colspan=7><form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
		echo _('Pricing for part') . ':<input type=text name="Item" MAXSIZEe=22 VALUE="' . $Item . '" maxlength=20><input type=submit name=NewPart Value="' . _('Review Prices') . '">';
		echo '</th></tr></form>';

		echo '<tr><th>' . _('Currency') .
			'</th><th>' . _('Sales Type') .
			 '</th><th>' . _('Price') .
			 '</th><th>' . _('Start Date') . ' </th>
			 <th>' . _('End Date') . '</th></tr>';

		$k=0; //row colour counter

		while ($myrow = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			if ($myrow['enddate']=='0000-00-00'){
				$EndDateDisplay = _('No End Date');
			} else {
				$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
			}
			/*Only allow access to modify prices if securiy token 5 is allowed */
			if (in_array(5,$_SESSION['AllowedPageSecurityTokens'])) {

				printf("<td>%s</td>
						<td>%s</td>
						<td class=number>%0.2f</td>
						<td>%s</td>
						<td>%s</td>
						<td><a href='%s?%s&Item=%s&TypeAbbrev=%s&CurrAbrev=%s&Price=%s&StartDate=%s&EndDate=%s&Edit=1'>" . _('Edit') . "</td>
						<td><a href='%s?%s&Item=%s&TypeAbbrev=%s&CurrAbrev=%s&StartDate=%s&EndDate=%s&delete=yes' onclick=\"return confirm('" . _('Are you sure you wish to delete this price?') . "');\">" . _('Delete') . '</td></tr>',
						$myrow['currency'],
						$myrow['sales_type'],
						$myrow['price'],
						ConvertSQLDate($myrow['startdate']),
						$EndDateDisplay,
						$_SERVER['PHP_SELF'],
						SID,
						$myrow['stockid'],
						$myrow['typeabbrev'],
						$myrow['currabrev'],
						$myrow['price'],
						$myrow['startdate'],
						$myrow['enddate'],
						$_SERVER['PHP_SELF'],
						SID,
						$myrow['stockid'],
						$myrow['typeabbrev'],
						$myrow['currabrev'],
						$myrow['startdate'],
						$myrow['enddate']);
			} else {
				printf("<td>%s</td>
						<td>%s</td>
						<td class=number>%0.2f</td>
						<td>%s</td>
						<td>%s</td>
						</tr>",
						$myrow['currency'],
						$myrow['sales_type'],
						$myrow['price'],
						ConvertSQLDate($myrow['startdate']),
						$EndDateDisplay);
			}

		}
		//END WHILE LIST LOOP
		echo '</table><p>';
	} else {
		prnMsg(_('There are no prices set up for this part'),'warn');
	}

	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	if (isset($_GET['Edit'])){
		echo '<input type=hidden name="OldTypeAbbrev" VALUE="' . $_GET['TypeAbbrev'] .'">';
		echo '<input type=hidden name="OldCurrAbrev" VALUE="' . $_GET['CurrAbrev'] . '">';
		echo '<input type=hidden name="OldStartDate" VALUE="' . $_GET['StartDate'] . '">';
		echo '<input type=hidden name="OldEndDate" VALUE="' . $_GET['EndDate'] . '">';
		$_POST['CurrAbrev'] = $_GET['CurrAbrev'];
		$_POST['TypeAbbrev'] = $_GET['TypeAbbrev'];
		$_POST['Price'] = $_GET['Price'];
		$_POST['StartDate'] = ConvertSQLDate($_GET['StartDate']);
		if ($_GET['EndDate']=='' OR $_GET['EndDate']=='0000-00-00'){
			$_POST['EndDate'] = '';
		} else {
			$_POST['EndDate'] = ConvertSQLDate($_GET['EndDate']);
		}
	}

	$SQL = 'SELECT currabrev, currency FROM currencies';
	$result = DB_query($SQL,$db);

	echo '<br><table class=selection>';
	echo '<tr><th colspan=5><font color=BLUE size=3><b>' . $Item . ' - ' . $PartDescription . '</b></font></th></tr>';
	echo '<tr><td>' . _('Currency') . ':</td><td><select name="CurrAbrev">';
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['currabrev']==$_POST['CurrAbrev']) {
			echo '<option selected VALUE="';
		} else {
			echo '<option VALUE="';
		}
		echo $myrow['currabrev'] . '">' . $myrow['currency'];
	} //end while loop

	DB_free_result($result);

	echo '</select>	</td></tr><tr><td>' . _('Sales Type Price List') . ':</td><td><select name="TypeAbbrev">';

	$SQL = 'SELECT typeabbrev, sales_type FROM salestypes';
	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['typeabbrev']==$_POST['TypeAbbrev']) {
			echo '<option selected VALUE="';
		} else {
			echo '<option VALUE="';
		}
		echo $myrow['typeabbrev'] . '">' . $myrow['sales_type'];

	} //end while loop

	DB_free_result($result);

	if (!isset($_POST['StartDate'])){
		$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	if (!isset($_POST['EndDate'])){
		$_POST['EndDate'] = DateAdd(date($_SESSION['DefaultDateFormat']),'y',1);
	}
	echo '<tr><td>' . _('Price Effective From Date')  . ':</td>
				<td><input type="Text" class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="StartDate" size=10 maxlength=10 value="' . $_POST['StartDate'] . '"></td></tr>';
	echo '<tr><td>' . _('Price Effective To Date')  . ':</td>
				<td><input type="Text" class=date alt="'.$_SESSION['DefaultDateFormat'].'" name="EndDate" size=10 maxlength=10 value="' . $_POST['EndDate'] . '"></td></tr>';
	echo '<input type=hidden name=Item value='.$Item.'>';
	?>

	</select>
	</td></tr>

	<tr><td><?php echo _('Price'); ?>:</td>
	<td>
	<input type="Text" class=number name="Price" size=12 maxlength=11 value=
	<?php if(isset($_POST['Price'])) {
			echo $_POST['Price'];
		   }?>>

	</td></tr>

	</table>
	<br><div class="centre">
	<input type="Submit" name="submit" value="<?php echo _('Enter') . '/' . _('Amend Price'); ?>">
	</div>

<?php
 }

echo '</form>';
include('includes/footer.inc');


function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $db) {

	/*This is quite complicated - the idea is that prices set up should be unique and there is no way two prices could be returned as valid - when getting a price in includes/GetPrice.inc the logic is to first look for a price of the salestype/currency within the effective start and end dates - then if not get the price with a start date prior but a blank end date (the default price). We would not want two prices where one price falls inside another effective date range except in the case of a blank end date - ie no end date - the default price for the currency/salestype.
	I first thought that we would need to update the previous default price (blank end date), when a new default price is entered, to have an end date of the startdate of this new default price less 1 day - but this is  converting a default price into a special price which could result in having two special prices over the same date range - best to leave it unchanged and use logic in the GetPrice.inc to ensure the correct default price is returned
	*/
	//this is just the case where debtorno='' - see the Prices_Customer.php script for customer special prices
		$SQL = "SELECT price,
						startdate,
						enddate
						FROM prices
						WHERE debtorno=''
						AND stockid='" . $Item . "'
						AND currabrev='" . $CurrAbbrev . "'
						AND typeabbrev='" . $PriceList . "'
						AND enddate <>'0000-00-00'
						ORDER BY startdate, enddate";
		$result = DB_query($SQL,$db);
		$NextStartDate = Date($_SESSION['DefaultDateFormat']);
		unset($EndDate);
		unset($NextStartDate);
		while ($myrow = DB_fetch_array($result)){
			if (isset($NextStartDate)){
				if (Date1GreaterThanDate2(ConvertSQLDate($myrow['startdate']),$NextStartDate)){
					$NextStartDate = ConvertSQLDate($myrow['startdate']);
					//Only if the previous enddate is after the new start date do we need to look at updates
					if (Date1GreaterThanDate2(ConvertSQLDate($EndDate),ConvertSQLDate($myrow['startdate']))) {
						/*Need to make the end date the new start date less 1 day */
						$SQL = "UPDATE prices SET enddate = '" . FormatDateForSQL(DateAdd($NextStartDate,'d',-1))  . "'
										WHERE stockid ='" .$Item . "'
										AND currabrev='" . $CurrAbbrev . "'
										AND typeabbrev='" . $PriceList . "'
										AND startdate ='" . $StartDate . "'
										AND enddate = '" . $EndDate . "'
										AND debtorno =''";
						$UpdateResult = DB_query($SQL,$db);
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