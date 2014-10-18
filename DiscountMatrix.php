<?php

/* $Id: DiscountMatrix.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Discount Matrix Maintenance');
include('includes/header.inc');

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
$i=1;

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if (!is_numeric(filter_number_format($_POST['QuantityBreak']))){
		prnMsg( _('The quantity break must be entered as a positive number'),'error');
		$InputError =1;
		$Errors[$i] = 'QuantityBreak';
		$i++;
	}

	if (filter_number_format($_POST['QuantityBreak'])<=0){
		prnMsg( _('The quantity of all items on an order in the discount category') . ' ' . $_POST['DiscountCategory'] . ' ' . _('at which the discount will apply is 0 or less than 0') . '. ' . _('Positive numbers are expected for this entry'),'warn');
		$InputError =1;
		$Errors[$i] = 'QuantityBreak';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DiscountRate']))){
		prnMsg( _('The discount rate must be entered as a positive number'),'warn');
		$InputError =1;
		$Errors[$i] = 'DiscountRate';
		$i++;
	}
	if (filter_number_format($_POST['DiscountRate'])<=0 OR filter_number_format($_POST['DiscountRate'])>100){
		prnMsg( _('The discount rate applicable for this record is either less than 0% or greater than 100%') . '. ' . _('Numbers between 1 and 100 are expected'),'warn');
		$InputError =1;
		$Errors[$i] = 'DiscountRate';
		$i++;
	}

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if ($InputError !=1) {

		$sql = "INSERT INTO discountmatrix (salestype,
							discountcategory,
							quantitybreak,
							discountrate)
					VALUES('" . $_POST['SalesType'] . "',
						'" . $_POST['DiscountCategory'] . "',
						'" . filter_number_format($_POST['QuantityBreak']) . "',
						'" . (filter_number_format($_POST['DiscountRate'])/100) . "')";

		$result = DB_query($sql,$db);
		prnMsg( _('The discount matrix record has been added'),'success');
		echo '<br />';
		unset($_POST['DiscountCategory']);
		unset($_POST['SalesType']);
		unset($_POST['QuantityBreak']);
		unset($_POST['DiscountRate']);
	}
} elseif (isset($_GET['Delete']) and $_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="DELETE FROM discountmatrix
		WHERE discountcategory='" .$_GET['DiscountCategory'] . "'
		AND salestype='" . $_GET['SalesType'] . "'
		AND quantitybreak='" . $_GET['QuantityBreak']."'";

	$result = DB_query($sql,$db);
	prnMsg( _('The discount matrix record has been deleted'),'success');
	echo '<br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


echo '<table class="selection">';

$sql = "SELECT typeabbrev,
		sales_type
		FROM salestypes";

$result = DB_query($sql, $db);

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


$sql = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
$result = DB_query($sql, $db);
if (DB_num_rows($result) > 0) {
	echo '<tr>
			<td>' .  _('Discount Category Code') .': </td>
			<td><select name="DiscountCategory">';

	while ($myrow = DB_fetch_array($result)){
		if ($myrow['discountcategory']==$_POST['DiscCat']){
			echo '<option selected="selected" value="' . $myrow['discountcategory'] . '">' . $myrow['discountcategory'] . '</option>';
		} else {
			echo '<option value="' . $myrow['discountcategory'] . '">' . $myrow['discountcategory'] . '</option>';
		}
	}
	echo '</select></td></tr>';
} else {
	echo '<tr><td><input type="hidden" name="DiscountCategory" value="" /></td></tr>';
}

echo '<tr>
		<td>' . _('Quantity Break') . '</td>
		<td><input class="integer' . (in_array('QuantityBreak',$Errors) ? ' inputerror' : '') . '" tabindex="3" required="required" type="number" name="QuantityBreak" size="10" maxlength="10" /></td>
	</tr>
	<tr>
		<td>' . _('Discount Rate') . ' (%):</td>
		<td><input class="number' . (in_array('DiscountRate',$Errors) ? ' inputerror' : '') . '" tabindex="4" type="text" required="required" name="DiscountRate" title="' . _('The discount to apply to orders where the quantity exceeds the specified quantity') . '" size="5" maxlength="5" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
	<br />';

$sql = "SELECT sales_type,
			salestype,
			discountcategory,
			quantitybreak,
			discountrate
		FROM discountmatrix INNER JOIN salestypes
			ON discountmatrix.salestype=salestypes.typeabbrev
		ORDER BY salestype,
			discountcategory,
			quantitybreak";

$result = DB_query($sql,$db);

echo '<table class="selection">';
echo '<tr>
		<th>' . _('Sales Type') . '</th>
		<th>' . _('Discount Category') . '</th>
		<th>' . _('Quantity Break') . '</th>
		<th>' . _('Discount Rate') . ' %' . '</th>
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
	$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=yes&amp;SalesType=' . $myrow['salestype'] . '&amp;DiscountCategory=' . $myrow['discountcategory'] . '&amp;QuantityBreak=' . $myrow['quantitybreak'];

	printf('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td><a href="%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this discount matrix record?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['sales_type'],
			$myrow['discountcategory'],
			$myrow['quantitybreak'],
			$myrow['discountrate']*100 ,
			$DeleteURL);

}

echo '</table>
      </div>
	  </form>';

include('includes/footer.inc');
?>