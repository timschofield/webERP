<?php
/* $Id: Prices_Customer.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

$result = DB_query("SELECT debtorsmaster.name,
							debtorsmaster.currcode,
							debtorsmaster.salestype,
							currencies.decimalplaces AS currdecimalplaces
					 FROM debtorsmaster INNER JOIN currencies
					 ON debtorsmaster.currcode=currencies.currabrev
					 WHERE debtorsmaster.debtorno='" . $_SESSION['CustomerID'] . "'",$db);
$myrow = DB_fetch_array($result);

$Title = _('Special Prices for') . ' '. htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8');

include('includes/header.inc');

if (isset($_GET['Item'])){
	$Item = $_GET['Item'];
}elseif (isset($_POST['Item'])){
	$Item = $_POST['Item'];
}

if (!isset($Item) OR !isset($_SESSION['CustomerID']) OR $_SESSION['CustomerID']==''){

	prnMsg( _('A customer must be selected from the customer selection screen') . ', '
		. _('then an item must be selected before this page is called') . '. '
			. _('The product selection page should call this page with a valid product code'),'info');
	echo '<br />';
	include('includes/footer.inc');
	exit;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . _('Special Customer Prices') . '</p><br />';
echo '<b>' . htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8') . ' ' . _('in') . ' ' . $myrow['currcode'] . '<br />' . ' ' . _('for') . ' ';

$CurrCode = $myrow['currcode'];
$SalesType = $myrow['salestype'];
$CurrDecimalPlaces = $myrow['currdecimalplaces'];

$result = DB_query("SELECT stockmaster.description,
							stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='" . $Item . "'",$db);

$myrow = DB_fetch_row($result);
if (DB_num_rows($result)==0){
	prnMsg( _('The part code entered does not exist in the database') . '. ' . _('Only valid parts can have prices entered against them'),'error');
	$InputError=1;
}
if ($myrow[1]=='K'){
	prnMsg(_('The part selected is a kit set item') .', ' . _('these items explode into their components when selected on an order') . ', ' . _('prices must be set up for the components and no price can be set for the whole kit'),'error');
	exit;
}

echo $Item . ' - ' . $myrow[0] . '</b><br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_numeric(filter_number_format($_POST['Price'])) OR $_POST['Price']=='') {
		$InputError = 1;
		$msg = _('The price entered must be numeric');
	}

	if ($_POST['Branch'] !=''){
		$sql = "SELECT custbranch.branchcode
				FROM custbranch
				WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'
				AND custbranch.branchcode='" . $_POST['Branch'] . "'";

		$result = DB_query($sql,$db);
		if (DB_num_rows($result) ==0){
			$InputError =1;
			$msg = _('The branch code entered is not currently defined');
		}
	}

	if (! Is_Date($_POST['StartDate'])){
		$InputError =1;
		$msg = _('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	}
	if (! Is_Date($_POST['EndDate']) AND $_POST['EndDate']!=''){ //EndDate can also be blank for default prices
		$InputError =1;
		$msg = _('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	}
	if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate']) AND $_POST['EndDate']!=''){
		$InputError =1;
		$msg = _('The end date is expected to be after the start date, enter an end date after the start date for this price');
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['EndDate']) AND $_POST['EndDate']!=''){
		$InputError =1;
		$msg = _('The end date is expected to be after today. There is no point entering a new price where the effective date is before today!');
	}

	if ((isset($_POST['Editing']) AND $_POST['Editing']=='Yes') AND mb_strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price

		$sql = "UPDATE prices SET typeabbrev='" . $SalesType . "',
								currabrev='" . $CurrCode . "',
								price='" . filter_number_format($_POST['Price']) . "',
								branchcode='" . $_POST['Branch'] . "',
								startdate='" . FormatDateForSQL($_POST['StartDate']) . "',
								enddate='" . FormatDateForSQL($_POST['EndDate']) . "'
				WHERE prices.stockid='" . $Item . "'
				AND prices.typeabbrev='" . $SalesType . "'
				AND prices.currabrev='" . $CurrCode . "'
				AND prices.startdate='" . $_POST['OldStartDate'] . "'
				AND prices.enddate='" . $_POST['OldEndDate'] . "'
				AND prices.debtorno='" . $_SESSION['CustomerID'] . "'";

		$msg = _('Price Updated');
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */
		$sql = "INSERT INTO prices (stockid,
								typeabbrev,
								currabrev,
								debtorno,
								price,
								branchcode,
								startdate,
								enddate)
							VALUES ('".$Item."',
								'".$SalesType."',
								'".$CurrCode."',
								'" . $_SESSION['CustomerID'] . "',
								'" . filter_number_format($_POST['Price']) . "',
								'" . $_POST['Branch'] . "',
								'" . FormatDateForSQL($_POST['StartDate']) . "',
								'" . FormatDateForSQL($_POST['EndDate']) . "'
							)";
		$msg = _('Price added') . '.';
	}
	//run the SQL from either of the above possibilites
	if ($InputError!=1){
		$result = DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db)!=0){
		   If ($msg==_('Price Updated')){
				$msg = _('The price could not be updated because') . ' - ' . DB_error_msg($db);
			} else {
				$msg = _('The price could not be added because') . ' - ' . DB_error_msg($db);
			}
		}else {
			ReSequenceEffectiveDates ($Item, $SalesType, $CurrCode, $_SESSION['CustomerID'], $db);
			unset($_POST['EndDate']);
			unset($_POST['StartDate']);
			unset($_POST['Price']);
		}
	}

	prnMsg($msg);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM prices
			WHERE prices.stockid = '". $Item ."'
			AND prices.typeabbrev='". $SalesType ."'
			AND prices.currabrev ='". $CurrCode ."'
			AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
			AND prices.branchcode='" . $_GET['Branch'] . "'
			AND prices.startdate='" . $_GET['StartDate'] . "'
			AND prices.enddate='" . $_GET['EndDate'] . "'";

	$result = DB_query($sql,$db);
	prnMsg( _('This price has been deleted') . '!','success');
}


//Always do this stuff
//Show the normal prices in the currency of this customer

$sql = "SELECT prices.price,
				prices.currabrev,
               prices.typeabbrev,
               prices.startdate,
               prices.enddate
		FROM prices
		WHERE  prices.stockid='" . $Item . "'
		AND prices.typeabbrev='". $SalesType ."'
		AND prices.currabrev ='". $CurrCode ."'
		AND prices.debtorno=''
		ORDER BY currabrev,
						typeabbrev,
						startdate";

$ErrMsg = _('Could not retrieve the normal prices set up because');
$DbgMsg = _('The SQL used to retrieve these records was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<table><tr><td valign="top">';
echo '<table class="selection">';

if (DB_num_rows($result) == 0) {
	echo '<tr><td>' . _('There are no default prices set up for this part') . '</td></tr>';
} else {
	echo '<tr><th>' . _('Normal Price') . '</th></tr>';
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['enddate']=='0000-00-00'){
			$EndDateDisplay = _('No End Date');
		} else {
			$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
		}
		printf('<tr class="EvenTableRows">
				<td class="number">%s</td>
				<td class="date">%s</td>
				<td class="date">%s</td></tr>',
				locale_number_format($myrow['price'],$CurrDecimalPlaces),
				ConvertSQLDate($myrow['startdate']),
				$EndDateDisplay);
	}
}

echo '</table></td><td valign="top">';

//now get the prices for the customer selected

$sql = "SELECT prices.price,
               prices.branchcode,
			   custbranch.brname,
			   prices.startdate,
			   prices.enddate
		FROM prices LEFT JOIN custbranch
		ON prices.branchcode= custbranch.branchcode
		WHERE prices.typeabbrev = '".$SalesType."'
		AND prices.stockid='".$Item."'
		AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
		AND prices.currabrev='".$CurrCode."'
		AND (custbranch.debtorno='" . $_SESSION['CustomerID'] . "' OR
						custbranch.debtorno IS NULL)
		ORDER BY prices.branchcode,
				prices.startdate";

$ErrMsg = _('Could not retrieve the special prices set up because');
$DbgMsg = _('The SQL used to retrieve these records was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

echo '<table class="selection">';

if (DB_num_rows($result) == 0) {
	echo '<tr><td>' . _('There are no special prices set up for this part') . '</td></tr>';
} else {
/*THERE IS ALREADY A spl price setup */
	echo '<tr>
			<th>' . _('Special Price') . '</th>
			<th>' . _('Branch') . '</th>
		</tr>';

	while ($myrow = DB_fetch_array($result)) {

	if ($myrow['branchcode']==''){
		$Branch = _('All Branches');
	} else {
		$Branch = $myrow['brname'];
	}
	if ($myrow['enddate']=='0000-00-00'){
		$EndDateDisplay = _('No End Date');
	} else {
		$EndDateDisplay = ConvertSQLDate($myrow['enddate']);
	}
	echo '<tr style="background-color:#CCCCCC">
			<td class="number">' . locale_number_format($myrow['price'],$CurrDecimalPlaces) . '</td>
			<td>' . $Branch . '</td>
			<td>' . $myrow['units'] . '</td>
			<td class="number">' . $myrow['conversionfactor'] . '</td>
			<td>' . ConvertSQLDate($myrow['startdate']) . '</td>
			<td>' . $EndDateDisplay . '</td>
	 		<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?Item='.$Item.'&amp;Price='.$myrow['price'].'&amp;Branch='.$myrow['branchcode'].
				'&amp;StartDate='.$myrow['startdate'].'&amp;EndDate='.$myrow['enddate'].'&amp;Edit=1">' . _('Edit') . '</a></td>
			<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?Item='.$Item.'&amp;Branch='.$myrow['branchcode'].'&amp;StartDate='.$myrow['startdate'] .'&amp;EndDate='.$myrow['enddate'].'&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this price?') . '\');">' . _('Delete') . '</a></td>
		</tr>';

	}
//END WHILE LIST LOOP
}

echo '</table></td></tr></table><br />';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="Item" value="' . $Item . '" />';

if (isset($_GET['Edit']) and $_GET['Edit']==1){
	echo '<input type="hidden" name="Editing" value="Yes" />';
	echo '<input type="hidden" name="OldStartDate" value="' . $_GET['StartDate'] .'" />';
	echo '<input type="hidden" name="OldEndDate" value="' .  $_GET['EndDate'] . '" />';
	$_POST['Price']=$_GET['Price'];
	$_POST['Branch']=$_GET['Branch'];
	$_POST['StartDate'] = ConvertSQLDate($_GET['StartDate']);
	if (Is_Date($_GET['EndDate'])){
		$_POST['EndDate'] = ConvertSQLDate($_GET['EndDate']);
	} else {
		$_POST['EndDate']='';
	}
}
if (!isset($_POST['Branch'])) {
	$_POST['Branch']='';
}
if (!isset($_POST['Price'])) {
	$_POST['Price']=0;
}

if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = '';
}

$sql = "SELECT branchcode,
				brname
		FROM custbranch
		WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
$result = DB_query($sql, $db);

echo '<table class="selection">
		<tr>
			<td>' . _('Branch') . ':</td>
			<td><select name="Branch">';
if ($myrow['branchcode']=='') {
	echo '<option selected="selected" value="">' . _('All branches') . '</option>';
} else {
	echo '<option value="">' . _('All branches') . '</option>';
}

while ($myrow=DB_fetch_array($result)) {
	if ($myrow['branchcode']==$_GET['Branch']) {
		echo '<option selected="selected" value="'.$myrow['branchcode'].'">' . htmlspecialchars($myrow['brname'], ENT_QUOTES, 'UTF-8') . '</option>';
	} else {
		echo '<option value="'.$myrow['branchcode'].'">' . htmlspecialchars($myrow['brname'], ENT_QUOTES, 'UTF-8') . '</option>';
	}
}
echo '</select></td></tr>';
echo '<tr>
		<td>' . _('Start Date') . ':</td>
		<td><input type="text" name="StartDate" class="date" alt="'.$_SESSION['DefaultDateFormat']. '" size="11" maxlength="10" value="' . $_POST['StartDate'] . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('End Date') . ':</td>
		<td><input type="text" name="EndDate" class="date" alt="'.$_SESSION['DefaultDateFormat']. '" size="11" maxlength="10" value="' . $_POST['EndDate'] . '" /></td></tr>';

echo '<tr><td>' . _('Price') . ':</td>
          <td><input type="text" class="number" name="Price" size="11" maxlength="10" value="' . locale_number_format($_POST['Price'],2) . '" /></td>
		</tr>
	</table>';


echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
        </div>
		</form>';

include('includes/footer.inc');
exit;

function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $CustomerID, $db) {

	/*This is quite complicated - the idea is that prices set up should be unique and there is no way two prices could be returned as valid - when getting a price in includes/GetPrice.inc the logic is to first look for a price of the salestype/currency within the effective start and end dates - then if not get the price with a start date prior but a blank end date (the default price). We would not want two prices where the effective dates fall between an existing price so it is necessary to update enddates of prices  - with me - I am just hanging on here myself

	 Prices with no end date are default prices and need to be ignored in this resquence*/

	$SQL = "SELECT branchcode,
					startdate,
					enddate
					FROM prices
					WHERE debtorno='" . $CustomerID . "'
					AND stockid='" . $Item . "'
					AND currabrev='" . $CurrAbbrev . "'
					AND typeabbrev='" . $PriceList . "'
					AND enddate<>''
					ORDER BY
					branchcode,
					startdate,
					enddate";

	$result = DB_query($SQL,$db);

	unset($BranchCode);

	while ($myrow = DB_fetch_array($result)){
		if (!isset($BranchCode)){
			unset($NextDefaultStartDate); //a price with a blank end date
			unset($NextStartDate);
			unset($EndDate);
			unset($StartDate);
			$BranchCode = $myrow['branchcode'];
		}
		if (isset($NextStartDate)){
			if (Date1GreaterThanDate2(ConvertSQLDate($myrow['startdate']),$NextStartDate)){
				$NextStartDate = ConvertSQLDate($myrow['startdate']);
				if (Date1GreaterThanDate2(ConvertSQLDate($EndDate),ConvertSQLDate($myrow['startdate']))) {
					/*Need to make the end date the new start date less 1 day */
					$SQL = "UPDATE prices SET enddate = '" . FormatDateForSQL(DateAdd($NextStartDate,'d',-1))  . "'
									WHERE stockid ='" .$Item . "'
									AND currabrev='" . $CurrAbbrev . "'
									AND typeabbrev='" . $PriceList . "'
									AND startdate ='" . $StartDate . "'
									AND enddate = '" . $EndDate . "'
									AND debtorno ='" . $CustomerID . "'
									AND branchcode='" . $BranchCode . "'";
					$UpdateResult = DB_query($SQL,$db);
				}
			} //end of if startdate  after NextStartDate - we have a new NextStartDate
		} //end of if set NextStartDate
			else {
				$NextStartDate = ConvertSQLDate($myrow['startdate']);
		}
		$StartDate = $myrow['startdate'];
		$EndDate = $myrow['enddate'];
	}

	//Now look for duplicate prices with no end
	$SQL = "SELECT price,
					startdate,
					enddate
				FROM prices
				WHERE debtorno=''
				AND stockid='" . $Item . "'
				AND currabrev='" . $CurrAbbrev . "'
				AND typeabbrev='" . $PriceList . "'
				AND debtorno ='" . $CustomerID . "'
				AND branchcode=''
				AND enddate ='0000-00-00'
				ORDER BY startdate";
	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($OldStartDate)){
		/*Need to make the end date the new start date less 1 day */
			$NewEndDate = FormatDateForSQL(DateAdd(ConvertSQLDate($myrow['startdate']),'d',-1));
			$SQL = "UPDATE prices SET enddate = '" . $NewEndDate  . "'
						WHERE stockid ='" .$Item . "'
						AND currabrev='" . $CurrAbbrev . "'
						AND typeabbrev='" . $PriceList . "'
						AND startdate ='" . $OldStartDate . "'
						AND debtorno ='" . $CustomerID . "'
						AND branchcode=''
						AND enddate = '0000-00-00'
						AND debtorno =''";
			$UpdateResult = DB_query($SQL,$db);
		}
		$OldStartDate = $myrow['startdate'];
	} // end of loop around duplicate no end date prices
}
?>
