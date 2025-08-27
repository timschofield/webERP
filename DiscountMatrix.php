<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Discount Matrix Maintenance');
$ViewTopic = 'SalesOrders';
$BookMark = 'DiscountMatrix';
include('includes/header.php');

$Errors = array();
$i = 1;

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	if (!is_numeric(filter_number_format($_POST['QuantityBreak']))){
		prnMsg( __('The quantity break must be entered as a positive number'),'error');
		$InputError =1;
		$Errors[$i] = 'QuantityBreak';
		$i++;
	}

	if (filter_number_format($_POST['QuantityBreak'])<=0){
		prnMsg( __('The quantity of all items on an order in the discount category') . ' ' . $_POST['DiscountCategory'] . ' ' . __('at which the discount will apply is 0 or less than 0') . '. ' . __('Positive numbers are expected for this entry'),'warn');
		$InputError =1;
		$Errors[$i] = 'QuantityBreak';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['DiscountRate']))){
		prnMsg( __('The discount rate must be entered as a positive number'),'warn');
		$InputError =1;
		$Errors[$i] = 'DiscountRate';
		$i++;
	}
	if (filter_number_format($_POST['DiscountRate'])<=0 OR filter_number_format($_POST['DiscountRate'])>100){
		prnMsg( __('The discount rate applicable for this record is either less than 0% or greater than 100%') . '. ' . __('Numbers between 1 and 100 are expected'),'warn');
		$InputError =1;
		$Errors[$i] = 'DiscountRate';
		$i++;
	}

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if ($InputError !=1) {

		$SQL = "INSERT INTO discountmatrix (salestype,
							discountcategory,
							quantitybreak,
							discountrate)
					VALUES('" . $_POST['SalesType'] . "',
						'" . $_POST['DiscountCategory'] . "',
						'" . filter_number_format($_POST['QuantityBreak']) . "',
						'" . (filter_number_format($_POST['DiscountRate'])/100) . "')";

		$Result = DB_query($SQL);
		prnMsg( __('The discount matrix record has been added'),'success');
		echo '<br />';
		unset($_POST['DiscountCategory']);
		unset($_POST['SalesType']);
		unset($_POST['QuantityBreak']);
		unset($_POST['DiscountRate']);
	}
} elseif (isset($_GET['Delete']) and $_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$SQL="DELETE FROM discountmatrix
		WHERE discountcategory='" .$_GET['DiscountCategory'] . "'
		AND salestype='" . $_GET['SalesType'] . "'
		AND quantitybreak='" . $_GET['QuantityBreak']."'";

	$Result = DB_query($SQL);
	prnMsg( __('The discount matrix record has been deleted'),'success');
	echo '<br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Matrix Parameters'), '</legend>';

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

$SQL = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
$Result = DB_query($SQL);
if (DB_num_rows($Result) > 0) {
	echo '<field>
			<label for="DiscountCategory">' .  __('Discount Category Code') .': </label>
			<select name="DiscountCategory">';

	while ($MyRow = DB_fetch_array($Result)){
		if ($MyRow['discountcategory']==$_POST['DiscCat']){
			echo '<option selected="selected" value="' . $MyRow['discountcategory'] . '">' . $MyRow['discountcategory'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['discountcategory'] . '">' . $MyRow['discountcategory'] . '</option>';
		}
	}
	echo '</select>
		</field>';
} else {
	echo '<field><td><input type="hidden" name="DiscountCategory" value="" /></td></field>';
}

echo '<field>
		<label for="QuantityBreak">' . __('Quantity Break') . '</label>
		<input class="integer' . (in_array('QuantityBreak',$Errors) ? ' inputerror' : '') . '" tabindex="3" required="required" type="number" name="QuantityBreak" size="10" maxlength="10" />
	</field>
	<field>
		<label for="DiscountRate">' . __('Discount Rate') . ' (%):</label>
		<input class="number' . (in_array('DiscountRate',$Errors) ? ' inputerror' : '') . '" tabindex="4" type="text" required="required" name="DiscountRate" title="" size="5" maxlength="5" />
		<fieldhelp>' . __('The discount to apply to orders where the quantity exceeds the specified quantity') . '</fieldhelp>
	</field>
	</fieldset>
	<div class="centre">
		<input tabindex="5" type="submit" name="submit" value="' . __('Enter Information') . '" />
	</div>';

$SQL = "SELECT sales_type,
			salestype,
			discountcategory,
			quantitybreak,
			discountrate
		FROM discountmatrix INNER JOIN salestypes
			ON discountmatrix.salestype=salestypes.typeabbrev
		ORDER BY salestype,
			discountcategory,
			quantitybreak";

$Result = DB_query($SQL);

echo '<table class="selection">';
echo '<tr>
		<th>' . __('Sales Type') . '</th>
		<th>' . __('Discount Category') . '</th>
		<th>' . __('Quantity Break') . '</th>
		<th>' . __('Discount Rate') . ' %' . '</th>
		<th></th>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {
	$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=yes&amp;SalesType=' . $MyRow['salestype'] . '&amp;DiscountCategory=' . $MyRow['discountcategory'] . '&amp;QuantityBreak=' . $MyRow['quantitybreak'];

	echo '<tr class="striped_row">
			<td>', $MyRow['sales_type'], '</td>
			<td>', $MyRow['discountcategory'], '</td>
			<td class="number">', $MyRow['quantitybreak'], '</td>
			<td class="number">', $MyRow['discountrate']*100, '</td>
			<td><a href="', $DeleteURL, '" onclick="return confirm(\'' . __('Are you sure you wish to delete this discount matrix record?') . '\');">' . __('Delete') . '</a></td>
		</tr>';

}

echo '</table>
	  </form>';

include('includes/footer.php');
