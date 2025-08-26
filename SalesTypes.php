<?php

include('includes/session.php');

$Title = __('Sales Types') . ' / ' . __('Price List Maintenance');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['SelectedType'])){
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	if (mb_strlen($_POST['TypeAbbrev']) > 2) {
		$InputError = 1;
		prnMsg(__('The sales type (price list) code must be two characters or less long'),'error');
		$Errors[$i] = 'SalesType';
		$i++;
	} elseif ($_POST['TypeAbbrev']=='' OR $_POST['TypeAbbrev']==' ' OR $_POST['TypeAbbrev']=='  ') {
		$InputError = 1;
		prnMsg( __('The sales type (price list) code cannot be an empty string or spaces'),'error');
		$Errors[$i] = 'SalesType';
		$i++;
	} elseif( trim($_POST['Sales_Type'])==''){
		$InputError = 1;
		prnMsg(__('The sales type (price list) description cannot be empty'),'error');
		$Errors[$i] = 'SalesType';
		$i++;
	} elseif (mb_strlen($_POST['Sales_Type']) >40) {
		$InputError = 1;
		prnMsg(__('The sales type (price list) description must be forty characters or less long'),'error');
		$Errors[$i] = 'SalesType';
		$i++;
	} elseif ($_POST['TypeAbbrev']=='AN'){
		$InputError = 1;
		prnMsg(__('The sales type code cannot be AN since this is a system defined abbreviation for any sales type in general ledger interface lookups'),'error');
		$Errors[$i] = 'SalesType';
		$i++;
	}

	if (isset($SelectedType) AND $InputError !=1) {

		$SQL = "UPDATE salestypes
			SET sales_type = '" . $_POST['Sales_Type'] . "'
			WHERE typeabbrev = '".$SelectedType."'";

		$Msg = __('The customer/sales/pricelist type') . ' ' . $SelectedType . ' ' .  __('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$CheckSQL = "SELECT count(*)
			     FROM salestypes
			     WHERE typeabbrev = '" . $_POST['TypeAbbrev'] . "'";

		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

		if ( $CheckRow[0] > 0 ) {
			$InputError = 1;
			prnMsg( __('The customer/sales/pricelist type ') . $_POST['TypeAbbrev'] . __(' already exist.'),'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO salestypes (typeabbrev,
											sales_type)
							VALUES ('" . str_replace(' ', '', $_POST['TypeAbbrev']) . "',
									'" . $_POST['Sales_Type'] . "')";

			$Msg = __('Customer/sales/pricelist type') . ' ' . $_POST['Sales_Type'] .  ' ' . __('has been created');
			$CheckSQL = "SELECT count(typeabbrev)
						FROM salestypes";
			$Result = DB_query($CheckSQL);
			$Row = DB_fetch_row($Result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

	// Check the default price list exists
		$CheckSQL = "SELECT count(*)
			     FROM salestypes
			     WHERE typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'";
		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

	// If it doesnt then update config with newly created one.
		if ($CheckRow[0] == 0) {
			$SQL = "UPDATE config
					SET confvalue='".$_POST['TypeAbbrev']."'
					WHERE confname='DefaultPriceList'";
			$Result = DB_query($SQL);
			$_SESSION['DefaultPriceList'] = $_POST['TypeAbbrev'];
		}

		prnMsg($Msg,'success');

		unset($SelectedType);
		unset($_POST['TypeAbbrev']);
		unset($_POST['Sales_Type']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions

	$SQL= "SELECT COUNT(*)
	       FROM debtortrans
	       WHERE debtortrans.tpe='".$SelectedType."'";

	$ErrMsg = __('The number of transactions using this customer/sales/pricelist type could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(__('Cannot delete this sale type because customer transactions have been created using this sales type') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('transactions using this sales type code'),'error');

	} else {

		$SQL = "SELECT COUNT(*) FROM debtorsmaster WHERE salestype='".$SelectedType."'";

		$ErrMsg = __('The number of transactions using this Sales Type record could not be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(__('Cannot delete this sale type because customers are currently set up to use this sales type') . '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('customers with this sales type code'));
		} else {

			$SQL="DELETE FROM salestypes WHERE typeabbrev='" . $SelectedType . "'";
			$ErrMsg = __('The Sales Type record could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(__('Sales type') . ' / ' . __('price list') . ' ' . $SelectedType  . ' ' . __('has been deleted') ,'success');

			$SQL ="DELETE FROM prices WHERE prices.typeabbrev='" . $SelectedType . "'";
			$ErrMsg =  __('The Sales Type prices could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(' ...  ' . __('and any prices for this sales type / price list were also deleted'),'success');
			unset ($SelectedType);
			unset($_GET['delete']);

		}
	} //end if sales type used in debtor transactions or in customers set up
}


if(isset($_POST['Cancel'])){
	unset($SelectedType);
	unset($_POST['TypeAbbrev']);
	unset($_POST['Sales_Type']);
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT typeabbrev,sales_type FROM salestypes ORDER BY typeabbrev";
	$Result = DB_query($SQL);

	echo '<table class="selection">
			<thead>
			<tr>
				<th class="SortedColumn">' . __('Type Code') . '</th>
				<th class="SortedColumn">' . __('Type Name') . '</th>
				<th colspan="2"></th>
			</tr>
		</thead>
		<tbody>';

while ($MyRow = DB_fetch_row($Result)) {

	echo '<tr class="striped_row">
			<td>', $MyRow[0], '</td>
			<td>', $MyRow[1], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedType=', $MyRow[0], '">' . __('Edit') . '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedType=', $MyRow[0], '&amp;delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this price list and all the prices it may have set up?') . '\');">' . __('Delete') . '</a></td>
		</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<br />
			<div class="centre">
				<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . __('Show All Sales Types Defined') . '</a>
			</div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' ) {

		$SQL = "SELECT typeabbrev,
			       sales_type
		        FROM salestypes
		        WHERE typeabbrev='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TypeAbbrev'] = $MyRow['typeabbrev'];
		$_POST['Sales_Type']  = $MyRow['sales_type'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />
			<input type="hidden" name="TypeAbbrev" value="' . $_POST['TypeAbbrev'] . '" />
			<fieldset>
			<legend>' . __('Edit Sales Type/Price') . '</legend>
			<field>
				<label for="TypeAbbrev">' . __('Type Code') . ':</label>
				<fieldtext>' . $_POST['TypeAbbrev'] . '</fieldtext>
			</field>';

	} else 	{

		// This is a new type so the user may volunteer a type code

		echo '<fieldset>
				<legend>' . __('Create Sales Type/Price List') . '</legend>
				<field>
					<label for="TypeAbbrev">' . __('Type Code') . ':</label>
					<input type="text" ' . (in_array('SalesType',$Errors) ? 'class="inputerror"' : '' ) .' size="3" maxlength="2" name="TypeAbbrev" />
				</field>';
	}

	if (!isset($_POST['Sales_Type'])) {
		$_POST['Sales_Type']='';
	}
	echo '<field>
			<label for="Sales_Type">' . __('Sales Type Name') . ':</label>
			<input type="text" name="Sales_Type" value="' . $_POST['Sales_Type'] . '" />
		</field>
		</fieldset>'; // close main table

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Accept') . '" /><input type="reset" name="Cancel" value="' . __('Cancel') . '" />
		</div>
	</form>';

} // end if user wish to delete

include('includes/footer.php');
