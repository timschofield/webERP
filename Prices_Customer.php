<?php

require(__DIR__ . '/includes/session.php');

$Result = DB_query("SELECT debtorsmaster.name,
							debtorsmaster.currcode,
							debtorsmaster.salestype,
							currencies.decimalplaces AS currdecimalplaces
					 FROM debtorsmaster INNER JOIN currencies
					 ON debtorsmaster.currcode=currencies.currabrev
					 WHERE debtorsmaster.debtorno='" . $_SESSION['CustomerID'] . "'");
$MyRow = DB_fetch_array($Result);
$CurrCode = $MyRow['currcode'];
$SalesType = $MyRow['salestype'];
$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
$Name = $MyRow['name'];

$Title = __('Special Prices for') . ' '. htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['StartDate'])){$_POST['StartDate'] = ConvertSQLDate($_POST['StartDate']);}
if (isset($_POST['EndDate'])){$_POST['EndDate'] = ConvertSQLDate($_POST['EndDate']);}

if (isset($_GET['Item'])){
	$Item = $_GET['Item'];
}elseif (isset($_POST['Item'])){
	$Item = $_POST['Item'];
}

if (!isset($Item) OR !isset($_SESSION['CustomerID']) OR $_SESSION['CustomerID']==''){

	prnMsg( __('A customer must be selected from the customer selection screen') . ', '
		. __('then an item must be selected before this page is called') . '. '
			. __('The product selection page should call this page with a valid product code'),'info');
	echo '<br />';
	include('includes/footer.php');
	exit();
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') .
		'" alt="" />' . __('Special Customer Prices') . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_numeric(filter_number_format($_POST['Price'])) OR $_POST['Price']=='') {
		$InputError = 1;
		$Msg = __('The price entered must be numeric');
	}

	if ($_POST['Branch'] !=''){
		$SQL = "SELECT custbranch.branchcode
				FROM custbranch
				WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'
				AND custbranch.branchcode='" . $_POST['Branch'] . "'";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result) ==0){
			$InputError =1;
			$Msg = __('The branch code entered is not currently defined');
		}
	}

	if (! Is_Date($_POST['StartDate'])){
		$InputError =1;
		$Msg = __('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	}
	if ($_POST['EndDate']!='9999-12-31'){
		if (! Is_Date($_POST['EndDate']) AND $_POST['EndDate']!=''){ //EndDate can also be blank for default prices
			$InputError =1;
			$Msg = __('The date this price is be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'];
		}
		if (Date1GreaterThanDate2($_POST['StartDate'],$_POST['EndDate']) AND $_POST['EndDate']!=''){
			$InputError =1;
			$Msg = __('The end date is expected to be after the start date, enter an end date after the start date for this price');
		}
		if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['EndDate']) AND $_POST['EndDate']!=''){
			$InputError =1;
			$Msg = __('The end date is expected to be after today. There is no point entering a new price where the effective date is before today!');
		}
		if (trim($_POST['EndDate'])==''){
			$_POST['EndDate'] = '9999-12-31';
		}
	}


	if ((isset($_POST['Editing']) AND $_POST['Editing']=='Yes') AND mb_strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price

		$SQL = "UPDATE prices SET typeabbrev='" . $SalesType . "',
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

		$Msg = __('Price Updated');
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */
		$SQL = "INSERT INTO prices (stockid,
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
		$Msg = __('Price added') . '.';
	}
	//run the SQL from either of the above possibilites
	if ($InputError!=1){
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no()!=0){
		   if ($Msg==__('Price Updated')){
				$Msg = __('The price could not be updated because') . ' - ' . DB_error_msg();
			} else {
				$Msg = __('The price could not be added because') . ' - ' . DB_error_msg();
			}
		}else {
			ReSequenceEffectiveDates ($Item, $SalesType, $CurrCode, $_SESSION['CustomerID']);
			unset($_POST['EndDate']);
			unset($_POST['StartDate']);
			unset($_POST['Price']);
		}
	}

	prnMsg($Msg);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$SQL="DELETE FROM prices
			WHERE prices.stockid = '". $Item ."'
			AND prices.typeabbrev='". $SalesType ."'
			AND prices.currabrev ='". $CurrCode ."'
			AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
			AND prices.branchcode='" . $_GET['Branch'] . "'
			AND prices.startdate='" . $_GET['StartDate'] . "'
			AND prices.enddate='" . $_GET['EndDate'] . "'";

	$Result = DB_query($SQL);
	prnMsg( __('This price has been deleted') . '!','success');
}


//Always do this stuff
//Show the normal prices in the currency of this customer

$SQL = "SELECT prices.price,
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

$ErrMsg = __('Could not retrieve the normal prices set up because');
$Result = DB_query($SQL, $ErrMsg);

echo '<table class="selection">';

if (DB_num_rows($Result) == 0) {
	prnMsg(  __('There are no default prices set up for this part'), 'info');
} else {
	echo '<tr><th>' . __('Normal Price') . '</th></tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['enddate']=='9999-12-31'){
			$EndDateDisplay = __('No End Date');
		} else {
			$EndDateDisplay = ConvertSQLDate($MyRow['enddate']);
		}
		echo '<tr class="striped_row">
				<td class="number">', locale_number_format($MyRow['price'],$CurrDecimalPlaces), '</td>
				<td type="date">', ConvertSQLDate($MyRow['startdate']), '</td>
				<td type="date">', $EndDateDisplay, '</td>
			</tr>';
	}
}

echo '</table>';

//now get the prices for the customer selected

$SQL = "SELECT prices.price,
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

$ErrMsg = __('Could not retrieve the special prices set up because');
$Result = DB_query($SQL, $ErrMsg);

echo '<table class="selection">';

if (DB_num_rows($Result) == 0) {
	prnMsg( __('There are no special prices set up for this part'), 'warn');
} else {
/*THERE IS ALREADY A spl price setup */
	echo '<tr>
			<th>' . __('Special Price') . '</th>
			<th>' . __('Branch') . '</th>
			<th>' . __('Units') . '</th>
			<th>' . __('Conversion') . '<br />' . __('Factor') . '</th>
			<th>' . __('Start Date') . '</th>
			<th>' . __('End Date') . '</th>
			<th colspan="2"></th>
		</tr>';

	while ($MyRow = DB_fetch_array($Result)) {

	if ($MyRow['branchcode']==''){
		$Branch = __('All Branches');
	} else {
		$Branch = $MyRow['brname'];
	}
	if ($MyRow['enddate']=='9999-12-31'){
		$EndDateDisplay = __('No End Date');
	} else {
		$EndDateDisplay = ConvertSQLDate($MyRow['enddate']);
	}
	$StockSQL = "SELECT units,
						conversionfactor
					FROM stockmaster
					LEFT JOIN custitem
					ON stockmaster.stockid=custitem.stockid
					WHERE stockmaster.stockid='".$Item."'
					AND custitem.debtorno='" . $_SESSION['CustomerID'] . "'";
	$StockResult = DB_query($StockSQL);
	if (DB_num_rows($StockResult) == 0) {
		$StockRow['units'] = '';
		$StockRow['conversionfactor'] = 1;
	}
		$StockRow = DB_fetch_array($StockResult);
		echo '<tr style="background-color:#CCCCCC">
				<td class="number">' . locale_number_format($MyRow['price'],$CurrDecimalPlaces) . '</td>
				<td>' . $Branch . '</td>
				<td>' . $StockRow['units'] . '</td>
				<td class="number">' . $StockRow['conversionfactor'] . '</td>
				<td>' . ConvertSQLDate($MyRow['startdate']) . '</td>
				<td>' . $EndDateDisplay . '</td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?Item='.$Item.'&amp;Price='.$MyRow['price'].'&amp;Branch='.$MyRow['branchcode'].
					'&amp;StartDate='.$MyRow['startdate'].'&amp;EndDate='.$MyRow['enddate'].'&amp;Edit=1">' . __('Edit') . '</a></td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?Item='.$Item.'&amp;Branch='.$MyRow['branchcode'].'&amp;StartDate='.$MyRow['startdate'] .'&amp;EndDate='.$MyRow['enddate'].'&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this price?') . '\');">' . __('Delete') . '</a></td>
			</tr>';


	}
//END WHILE LIST LOOP
}

echo '</table></td></tr></table>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
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
	$_POST['EndDate'] = Date($_SESSION['DefaultDateFormat']);
}

$SQL = "SELECT branchcode,
				brname
		FROM custbranch
		WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
$Result = DB_query($SQL);

echo '<fieldset>';
echo '<legend><b>' . htmlspecialchars($Name, ENT_QUOTES, 'UTF-8') . ' ' . __('in') . ' ' . $CurrCode . '' . ' ' . __('for') . ' ';

$Result = DB_query("SELECT stockmaster.description,
							stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='" . $Item . "'");

$MyRow = DB_fetch_row($Result);
if (DB_num_rows($Result)==0){
	prnMsg( __('The part code entered does not exist in the database') . '. ' . __('Only valid parts can have prices entered against them'),'error');
	$InputError=1;
}
if ($MyRow[1]=='K'){
	prnMsg(__('The part selected is a kit set item') .', ' . __('these items explode into their components when selected on an order') . ', ' . __('prices must be set up for the components and no price can be set for the whole kit'),'error');
	exit();
}

echo $Item . ' - ' . $MyRow[0] . '</b></legend>';

echo '<field>
		<label for="Branch">' . __('Branch') . ':</label>
		<select name="Branch">';
if (isset($MyRow['branchcode']) and $MyRow['branchcode']=='') {
	echo '<option selected="selected" value="">' . __('All Branches') . '</option>';
} else {
	echo '<option value="">' . __('All Branches') . '</option>';
}

while ($MyRow=DB_fetch_array($Result)) {
	if ($MyRow['branchcode']==$_GET['Branch']) {
		echo '<option selected="selected" value="'.$MyRow['branchcode'].'">' . htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8') . '</option>';
	} else {
		echo '<option value="'.$MyRow['branchcode'].'">' . htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8') . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="StartDate">' . __('Start Date') . ':</label>
		<input name="StartDate" type="date" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['StartDate']) . '" />
	</field>';
echo '<field>
		<label for="EndDate">' . __('End Date') . ':</label>
		<input name="EndDate" type="date" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['EndDate']) . '" />
	</field>';

echo '<field>
		<label for="Price">' . __('Price') . ':</label>
		<input type="text" class="number" name="Price" size="11" maxlength="10" value="' . locale_number_format($_POST['Price'],2) . '" />
	</field>
</fieldset>';


echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>
</form>';

include('includes/footer.php');
exit();

function ReSequenceEffectiveDates ($Item, $PriceList, $CurrAbbrev, $CustomerID) {

	/*This is quite complicated - the idea is that prices set up should be unique and there is no way two prices could be returned as valid - when getting a price in includes/GetPrice.php the logic is to first look for a price of the salestype/currency within the effective start and end dates - then if not get the price with a start date prior but a blank end date (the default price). We would not want two prices where the effective dates fall between an existing price so it is necessary to update enddates of prices  - with me - I am just hanging on here myself

	 Prices with no end date are default prices and need to be ignored in this resquence*/

	$SQL = "SELECT branchcode,
					startdate,
					enddate
					FROM prices
					WHERE debtorno='" . $CustomerID . "'
					AND stockid='" . $Item . "'
					AND currabrev='" . $CurrAbbrev . "'
					AND typeabbrev='" . $PriceList . "'
					ORDER BY
					branchcode,
					startdate,
					enddate";
	$Result = DB_query($SQL);

	unset($BranchCode);

	while ($MyRow = DB_fetch_array($Result)){
		if (!isset($BranchCode)){
			unset($NextDefaultStartDate); //a price with a blank end date
			unset($NextStartDate);
			unset($EndDate);
			unset($StartDate);
			$BranchCode = $MyRow['branchcode'];
		}
		if (isset($NextStartDate)){
			if (Date1GreaterThanDate2(ConvertSQLDate($MyRow['startdate']),$NextStartDate)){
				$NextStartDate = ConvertSQLDate($MyRow['startdate']);
				if (Date1GreaterThanDate2(ConvertSQLDate($EndDate),ConvertSQLDate($MyRow['startdate']))) {
					/*Need to make the end date the new start date less 1 day */
					$SQL = "UPDATE prices SET enddate = '" . FormatDateForSQL(DateAdd($NextStartDate,'d',-1))  . "'
									WHERE stockid ='" .$Item . "'
									AND currabrev='" . $CurrAbbrev . "'
									AND typeabbrev='" . $PriceList . "'
									AND startdate ='" . $StartDate . "'
									AND enddate = '" . $EndDate . "'
									AND debtorno ='" . $CustomerID . "'
									AND branchcode='" . $BranchCode . "'";
					$UpdateResult = DB_query($SQL);
				}
			} //end of if startdate  after NextStartDate - we have a new NextStartDate
		} //end of if set NextStartDate
			else {
				$NextStartDate = ConvertSQLDate($MyRow['startdate']);
		}
		$StartDate = $MyRow['startdate'];
		$EndDate = $MyRow['enddate'];
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
				AND enddate ='9999-12-31'
				ORDER BY startdate";
	$Result = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($OldStartDate)){
		/*Need to make the end date the new start date less 1 day */
			$NewEndDate = FormatDateForSQL(DateAdd(ConvertSQLDate($MyRow['startdate']),'d',-1));
			$SQL = "UPDATE prices SET enddate = '" . $NewEndDate  . "'
						WHERE stockid ='" .$Item . "'
						AND currabrev='" . $CurrAbbrev . "'
						AND typeabbrev='" . $PriceList . "'
						AND startdate ='" . $OldStartDate . "'
						AND debtorno ='" . $CustomerID . "'
						AND branchcode=''
						AND enddate = '9999-12-31'
						AND debtorno =''";
			$UpdateResult = DB_query($SQL);
		}
		$OldStartDate = $MyRow['startdate'];
	} // end of loop around duplicate no end date prices
}
