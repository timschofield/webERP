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


echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</a><br>';


$result = DB_query("SELECT stockmaster.description, stockmaster.mbflag FROM stockmaster WHERE stockmaster.stockid='$Item'",$db);
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

echo '<br><font color=BLUE size=3><b>' . $Item . ' - ' . $myrow[0] . '</b></font> ';

echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
echo _('Pricing for part') . ':<input type=text name="Item" MAXSIZEe=22 VALUE="' . $Item . '" maxlength=20><input type=submit name=NewPart Value="' . _('Review Prices') . '">';
echo '<hr>';

if ($myrow[1]=="K"){
	prnMsg(_('The part selected is a kit set item') .', ' . _('these items explode into their components when selected on an order') . ', ' . _('prices must be set up for the components and no price can be set for the whole kit'),'error');
	exit;
}

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_double((double) trim($_POST['Price'])) OR $_POST['Price']=="") {
		$InputError = 1;
		prnMsg( _('The price entered must be numeric'),'error');
	}
	if (! Is_Date($_POST['StartDate'])){
		$InputError =1;
		prnMsg (_('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (! Is_Date($_POST['EndDate'])){
		$InputError =1;
		prnMsg (_('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate'])){
		$InputError =1;
		prnMsg (_('The end date is expected to be after the start date, enter an end date after the start date for this price'),'error');
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['EndDate'])){
		$InputError =1;
		prnMsg(_('The end date is expected to be after today. There is no point entering a new price where the effective date is before today!'),'error');
	}
	
	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND strlen($Item)>1 AND $InputError !=1) {

		/* Need to see if there is also a price entered that has an end date after the start date of this price and if so we will need to update it so there is no ambiguity as to which price will be used*/
		
		//editing an existing price
		$sql = "UPDATE prices SET
									typeabbrev='" . $_POST['TypeAbbrev'] . "',
									currabrev='" . $_POST['CurrAbrev'] . "',
									price=" . $_POST['Price'] . ",
									startdate='" . FormatDateForSQL($_POST['StartDate']) . "',
									enddate='" . FormatDateForSQL($_POST['EndDate']) . "'
								WHERE prices.stockid='$Item'
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
										'" . FormatDateForSQL($_POST['EndDate']). "',
										" . $_POST['Price'] . ")";
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
		AND prices.stockid='$Item'
		AND prices.debtorno=''
		ORDER BY prices.currabrev,
			prices.typeabbrev,
			prices.startdate";

	$result = DB_query($sql,$db);

	echo '<table>';
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
						ConvertSQLDate($myrow['enddate']),
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
				ConvertSQLDate($myrow['enddate']));
		}

	}
	//END WHILE LIST LOOP
	echo '</table><p>';

	if (DB_num_rows($result) == 0) {
		prnMsg(_('There are no prices set up for this part'),'warn');
	}

	if (isset($_GET['Edit'])){
		echo '<input type=hidden name="OldTypeAbbrev" VALUE="' . $_GET['TypeAbbrev'] .'">';
		echo '<input type=hidden name="OldCurrAbrev" VALUE="' . $_GET['CurrAbrev'] . '">';
		echo '<input type=hidden name="OldStartDate" VALUE="' . $_GET['StartDate'] . '">';
		echo '<input type=hidden name="OldEndDate" VALUE="' . $_GET['EndDate'] . '">';
		$_POST['CurrAbrev'] = $_GET['CurrAbrev'];
		$_POST['TypeAbbrev'] = $_GET['TypeAbbrev'];
		$_POST['Price'] = $_GET['Price'];
		$_POST['StartDate'] = ConvertSQLDate($_GET['StartDate']);
		$_POST['EndDate'] = ConvertSQLDate($_GET['EndDate']);
	}

	$SQL = "SELECT currabrev, currency FROM currencies";
	$result = DB_query($SQL,$db);

	echo '<table><tr><td>' . _('Currency') . ':</td><td><select name="CurrAbrev">';
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
		$_POST['EndDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,12,31,(Date('y')+20)));
	}
	echo '<tr><td>' . _('Price Effective From Date')  . ':</td>
				<td><input type=text name="StartDate" value="' . $_POST['StartDate'] . '"></td></tr>';
	echo '<tr><td>' . _('Price Effective To Date')  . ':</td>
				<td><input type=text name="EndDate" value="' . $_POST['EndDate'] . '">';
				
	?>

	</select>
	</td></tr>

	<tr><td><?php echo _('Price'); ?>:</td>
	<td>
	<input type="Text" class=number name="Price" size=12 maxlength=11 value=
	<?php if(isset($_POST['Price'])) {echo $_POST['Price'];}?>>

	</td></tr>

	</table>
	<div class="centre">
	<input type="Submit" name="submit" value="<?php echo _('Enter') . '/' . _('Amend Price'); ?>">
	</div>

<?php
 }

echo '</form>';
include('includes/footer.inc');


function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $db) {
	
		$SQL = "SELECT price, 
										startdate,
										enddate 
						FROM prices 
						WHERE debtorno='' 
						AND stockid='" . $Item . "'
						AND currabrev='" . $CurrAbbrev . "'
						AND typeabbrev='" . $PriceList . "'
						ORDER BY startdate, enddate";
		$result = DB_query($SQL,$db);
		$NextStartDate = Date($_SESSION['DefaultDateFormat']);
		unset($EndDate);
		unset($NextStartDate);
		while ($myrow = DB_fetch_array($result)){
			
			if (isset($NextStartDate)){
				if (Date1GreaterThanDate2(ConvertSQLDate($myrow['startdate']),$NextStartDate)){
					$NextStartDate = ConvertSQLDate($myrow['startdate']);
					if (isset($EndDate)) {
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
		}
}

?>