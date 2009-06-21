<?php
/* $Revision: 1.14 $ */

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
		$msg = _('The price entered must be numeric');
	}

	if (isset($_POST['OldTypeAbbrev']) AND isset($_POST['OldCurrAbrev']) AND strlen($Item)>1 AND $InputError !=1) {

		//editing an existing price
		$sql = "UPDATE prices SET
				typeabbrev='" . $_POST['TypeAbbrev'] . "',
				currabrev='" . $_POST['CurrAbrev'] . "',
				price=" . $_POST['Price'] . "
			WHERE prices.stockid='$Item'
			AND prices.typeabbrev='" . $_POST['OldTypeAbbrev'] . "'
			AND prices.currabrev='" . $_POST['OldCurrAbrev'] . "'
			AND prices.debtorno=''";

		$msg =  _('This price has been updated') . '.';
	} elseif ($InputError !=1) {

	/*Selected price is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new price form */

		$sql = "INSERT INTO prices (stockid,
						typeabbrev,
						currabrev,
						debtorno,
						price)
				VALUES ('$Item',
					'" . $_POST['TypeAbbrev'] . "',
					'" . $_POST['CurrAbrev'] . "',
					'',
					" . $_POST['Price'] . ")";

		$msg =  _('The new price has been added') . '.';
	}
	//run the SQL from either of the above possibilites only if there were no input errors
	if ($InputError !=1){
		$result = DB_query($sql,$db,'','',false,false);
		if (DB_error_no($db)!=0){
			If ($msg== _('This price has been updated')){
				$msg = _('The price could not be updated because') . ' - ' . DB_error_msg($db);
			} else {
				$msg = _('The price could not be added because') . ' - ' . DB_error_msg($db);
			}
			if ($debug==1){
				prnMsg(_('The SQL that caused the problem was') . ':<br>' . $sql,'error');
			}
		} else {
			unset($_POST['Price']);
		}
	}
	prnMsg($msg);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM prices
		WHERE prices.stockid = '". $Item ."'
		AND prices.typeabbrev='". $_GET['TypeAbbrev'] ."'
		AND prices.currabrev ='". $_GET['CurrAbrev'] ."'
		AND prices.debtorno=''";

	$result = DB_query($sql,$db);
	prnMsg( _('The selected price has been deleted') . '!','success');

}

//Always do this stuff
if ($InputError ==0){
	$sql = "SELECT currencies.currency,
	        	salestypes.sales_type,
			prices.price,
			prices.stockid,
			prices.typeabbrev,
			prices.currabrev
		FROM prices,
			salestypes,
			currencies
		WHERE prices.currabrev=currencies.currabrev
		AND prices.typeabbrev = salestypes.typeabbrev
		AND prices.stockid='$Item'
		AND prices.debtorno=''
		ORDER BY prices.currabrev,
			prices.typeabbrev";

	$result = DB_query($sql,$db);

	echo '<table>';
	echo '<tr><th>' . _('Currency') .
	     '</th><th>' . _('Sales Type') .
			 '</th><th>' . _('Price') .
			 '</th></tr>';

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
				<td align=right>%0.2f</td>
				<td><a href='%s?%s&Item=%s&TypeAbbrev=%s&CurrAbrev=%s&Price=%s&Edit=1'>" . _('Edit') . "</td>
				<td><a href='%s?%s&Item=%s&TypeAbbrev=%s&CurrAbrev=%s&delete=yes' onclick=\"return confirm('" . _('Are you sure you wish to delete this price?') . "');\">" . _('Delete') . '</td></tr>',
				$myrow['currency'],
				$myrow['sales_type'],
				$myrow['price'],
				$_SERVER['PHP_SELF'],
				SID,
				$myrow['stockid'],
				$myrow['typeabbrev'],
				$myrow['currabrev'],
				$myrow['price'],
				$_SERVER['PHP_SELF'],
				SID,
				$myrow['stockid'],
				$myrow['typeabbrev'],
				$myrow['currabrev']);
		} else {
			printf("<td>%s</td>
			        <td>%s</td>
				<td align=right>%0.2f</td></tr>",
				$myrow['currency'],
				$myrow['sales_type'],
				$myrow['price']);
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
		$_POST['CurrAbrev'] = $_GET['CurrAbrev'];
		$_POST['TypeAbbrev'] = $_GET['TypeAbbrev'];
		$_POST['Price'] = $_GET['Price'];
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

	$SQL = "SELECT typeabbrev, sales_type FROM salestypes";
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
?>