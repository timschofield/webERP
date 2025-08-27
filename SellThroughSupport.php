<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Sell Through Support');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['EffectiveFrom'])){$_POST['EffectiveFrom'] = ConvertSQLDate($_POST['EffectiveFrom']);}
if (isset($_POST['EffectiveTo'])){$_POST['EffectiveTo'] = ConvertSQLDate($_POST['EffectiveTo']);}

if (isset($_GET['SupplierID']) AND $_GET['SupplierID']!='') {
	$SupplierID = trim(mb_strtoupper($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_POST['SupplierID']));
}

//if $Edit == true then we are editing an existing SellThroughSupport record
if (isset($_GET['Edit'])) {
	$Edit = true;
} elseif (isset($_POST['Edit'])) {
	$Edit = true;
} else {
	$Edit = false;
}

/*Deleting a supplier sell through support record */
if (isset($_GET['Delete'])){
	$Result = DB_query("DELETE FROM sellthroughsupport WHERE id='" . intval($_GET['SellSupportID']) . "'");
	prnMsg(__('Deleted the supplier sell through support record'),'success');
}


if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($SupplierID)) { /*Validate Inputs */
	$InputError = 0; /*Start assuming the best */

	if (is_numeric(filter_number_format($_POST['RebateAmount']))==false) {
		$InputError = 1;
		prnMsg(__('The rebate amount entered was not numeric and a number is required.'), 'error');
		unset($_POST['RebateAmount']);
	} elseif (filter_number_format($_POST['RebateAmount']) == 0 AND filter_number_format($_POST['RebatePercent'])==0) {
		prnMsg(__('Both the rebate amount and the rebate percent is zero. One or the other must be a positive number?'), 'error');
		$InputError = 1;

/*
	} elseif (mb_strlen($_POST['Narrative'])==0 OR $_POST['Narrative']==''){
		prnMsg(__('The narrative cannot be empty.'),'error');
		$InputError = 1;
*/
	} elseif (filter_number_format($_POST['RebatePercent'])>100 OR  filter_number_format($_POST['RebatePercent']) < 0) {
		prnMsg(__('The rebate percent must be greater than zero but less than 100 percent. No changes will be made to this record'),'error');
		$InputError = 1;
	} elseif (filter_number_format($_POST['RebateAmount']) !=0 AND filter_number_format($_POST['RebatePercent'])!=0) {
		prnMsg(__('Both the rebate percent and rebate amount are non-zero. Only one or the other can be used.'),'error');
		$InputError = 1;
	} elseif (Date1GreaterThanDate2($_POST['EffectiveFrom'], $_POST['EffectiveTo'])) {
		prnMsg(__('The effective to date is prior to the effective from date.'),'error');
		$InputError = 1;
	}

	if ($InputError == 0 AND isset($_POST['AddRecord'])) {
		$SQL = "INSERT INTO sellthroughsupport (supplierno,
												debtorno,
												categoryid,
												stockid,
												narrative,
												rebateamount,
												rebatepercent,
												effectivefrom,
												effectiveto )
						VALUES ('" . $SupplierID . "',
							'" . $_POST['DebtorNo'] . "',
							'" . $_POST['CategoryID'] . "',
							'" . $_POST['StockID'] . "',
							'" . $_POST['Narrative'] . "',
							'" . filter_number_format($_POST['RebateAmount']) . "',
							'" . filter_number_format($_POST['RebatePercent']/100) . "',
							'" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
							'" . FormatDateForSQL($_POST['EffectiveTo']) . "')";

		$ErrMsg = __('The sell through support record could not be added to the database because');
		$AddResult = DB_query($SQL, $ErrMsg);
		prnMsg(__('This sell through support has been added to the database'), 'success');
	}
	if ($InputError == 0 AND isset($_POST['UpdateRecord'])) {
		$SQL = "UPDATE sellthroughsupport SET debtorno='" . $_POST['DebtorNo'] . "',
											categoryid='" . $_POST['CategoryID'] . "',
											stockid='" . $_POST['StockID'] . "',
											narrative='" . $_POST['Narrative'] . "',
											rebateamount='" . filter_number_format($_POST['RebateAmount']) . "',
											rebatepercent='" . filter_number_format($_POST['RebatePercent'])/100 . "',
											effectivefrom='" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
											effectiveto='" . FormatDateForSQL($_POST['EffectiveTo']) . "'
							WHERE id='" . $_POST['SellSupportID'] . "'";

		$ErrMsg = __('The sell through support record could not be updated because');
		$UpdResult = DB_query($SQL, $ErrMsg);
		prnMsg(__('Sell Through Support record has been updated'), 'success');
		$Edit = false;

	}

	if ($InputError == 0) {
	/*  insert took place and need to clear the form  */
		unset($_POST['StockID']);
		unset($_POST['EffectiveFrom']);
		unset($_POST['DebtorNo']);
		unset($_POST['CategoryID']);
		unset($_POST['Narrative']);
		unset($_POST['RebatePercent']);
		unset($_POST['RebateAmount']);
		unset($_POST['EffectiveFrom']);
		unset($_POST['EffectiveTo']);
	}
}

if (isset($_POST['SearchSupplier'])) {
	if (isset($_POST['Keywords']) AND isset($_POST['SupplierCode'])) {
		prnMsg( __('Supplier Name keywords have been used in preference to the Supplier Code extract entered') . '.', 'info' );
		echo '<br />';
	}
	if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
		$_POST['Keywords'] = ' ';
	}
	if (mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.currcode,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3
				FROM suppliers
				WHERE suppliers.suppname " . LIKE  . " '".$SearchString."'";

	} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.currcode,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3
				FROM suppliers
				WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'";

	} //one of keywords or SupplierCode was more than a zero length string
	$ErrMsg = __('The suppliers matching the criteria entered could not be retrieved because');
	$SuppliersResult = DB_query($SQL, $ErrMsg);

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p> ';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table cellpadding="2" colspan="7" class="selection">';

	$TableHeader = '<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Supplier Name') . '</th>
						<th>' . __('Currency') . '</th>
						<th>' . __('Address 1') . '</th>
						<th>' . __('Address 2') . '</th>
						<th>' . __('Address 3') . '</th>
					</tr>';
	echo $TableHeader;

	while ($MyRow = DB_fetch_array($SuppliersResult)) {
	   echo '<tr class="striped_row">
				<td><input type="submit" name="SupplierID" value="', $MyRow['supplierid'], '" /></td>
				<td>', $MyRow['suppname'], '</td>
				<td>', $MyRow['currcode'], '</td>
				<td>', $MyRow['address1'], '</td>
				<td>', $MyRow['address2'], '</td>
				<td>', $MyRow['address3'], '</td>
			</tr>';
	}//end of while loop
	echo '</table>
			</form>';
}//end if results to show
 elseif (!isset($SupplierID)) {

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p> ';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend class="search">', __('Search Criteria'), '</legend>
			<field>
				<label for="Keywords">' . __('Text in the Supplier') . ' <b>' . __('NAME') . '</label>
				<input type="text" name="Keywords" size="20" maxlength="25" />
			</field>
			<field>
				<label for="SupplierCode">' .'<b>' . __('OR') . ' </b>' .  __('Text in Supplier') . ' <b>' . __('CODE') . '</b>:</label>
				<input type="text" name="SupplierCode" size="20" maxlength="50" />
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="SearchSupplier" value="' . __('Find Suppliers Now') . '" />
			</div>
		</form>';
	include('includes/footer.php');
	exit();
}

if (isset($SupplierID)) { /* Then display all the sell through support for the supplier */

	/*Get the supplier details */
	$SuppResult = DB_query("SELECT suppname,
									currcode,
									decimalplaces
							FROM suppliers INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE supplierid='" . $SupplierID . "'");
	$SuppRow = DB_fetch_array($SuppResult);

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . ' ' . __('For Supplier') . ' - ' . $SupplierID . ' - ' . $SuppRow['suppname'] . '</p><br />';
}

if (isset($SupplierID) AND $Edit == false) {

	$SQL = "SELECT	id,
					sellthroughsupport.debtorno,
					debtorsmaster.name,
					rebateamount,
					rebatepercent,
					effectivefrom,
					effectiveto,
					sellthroughsupport.stockid,
					description,
					categorydescription,
					sellthroughsupport.categoryid,
					narrative
			FROM sellthroughsupport LEFT JOIN stockmaster
			ON sellthroughsupport.stockid=stockmaster.stockid
			LEFT JOIN stockcategory
			ON sellthroughsupport.categoryid = stockcategory.categoryid
			LEFT JOIN debtorsmaster
			ON sellthroughsupport.debtorno=debtorsmaster.debtorno
			WHERE supplierno = '" . $SupplierID . "'
			ORDER BY sellthroughsupport.effectivefrom DESC";
	$ErrMsg = __('The supplier sell through support deals could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result)==0) {
		prnMsg(__('There are no sell through support deals entered for this supplier'), 'info');
	} else {
		echo '<table cellpadding="2" class="selection">
				<tr>
					<th>' . __('Item or Category') . '</th>
					<th>' . __('Customer') . '</th>
					<th>' . __('Rebate') . '<br />' .  __('Value') . ' ' . $SuppRow['currcode'] . '</th>
					<th>' . __('Rebate') . '<br />' . __('Percent') . '</th>
					<th>' . __('Narrative') . '</th>
					<th>' . __('Effective From') . '</th>
					<th>' . __('Effective To') . '</th>
					<th colspan="2"></th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['categoryid']=='') {
				$ItemDescription = $MyRow['stockid'] . ' - ' . $MyRow['description'];
			} else {
				$ItemDescription = __('Any') . ' ' . $MyRow['categorydescription'];
			}
			if ($MyRow['debtorno']==''){
				$Customer = __('All Customers');
			} else {
				$Customer = $MyRow['debtorno'] . ' - ' . $MyRow['name'];
			}

			echo '<tr class="striped_row">
					<td>', $ItemDescription, '</td>
					<td>', $Customer, '</td>
					<td class="number">', locale_number_format($MyRow['rebateamount'],$SuppRow['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['rebatepercent']*100,2), '</td>
					<td>', $MyRow['narrative'], '</td>
					<td>', ConvertSQLDate($MyRow['effectivefrom']), '</td>
					<td>', ConvertSQLDate($MyRow['effectiveto']), '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF']), '?SellSupportID=', $MyRow['id'], '&amp;SupplierID=', $SupplierID, '&amp;Edit=1">' . __('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF']), '?SellSupportID=', $MyRow['id'], '&amp;Delete=1&amp;SupplierID=', $SupplierID, '" onclick=\'return confirm("' . __('Are you sure you wish to delete this sell through support record?') . '");\'>' . __('Delete') . '</a></td>
				</tr>';
		} //end of while loop
		echo '</table>';
	} // end of there are sell through support rows to show
} /* Only show the existing supplier sell through support records if one is not being edited */

/*Show the input form for new supplier sell through support details */
if (isset($SupplierID)) { //not selecting a supplier
	if ($Edit == true) {
		 $SQL = "SELECT id,
						debtorno,
						suppliers.suppname,
						rebateamount,
						rebatepercent,
						effectivefrom,
						effectiveto,
						stockid,
						categoryid,
						narrative
				FROM sellthroughsupport
				INNER JOIN suppliers
				ON sellthroughsupport.supplierno=suppliers.supplierid
				WHERE id='" . floatval($_GET['SellSupportID']) . "'";

		$ErrMsg = __('The supplier sell through support could not be retrieved because');
		$EditResult = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($EditResult);
	} else {
		$SQL = "SELECT suppname FROM suppliers WHERE supplierid='" . $SupplierID . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
	}

	$SuppName = $MyRow['suppname'];

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="SupplierID" value="' . $SupplierID . '" />';

	echo '<fieldset>';

	if ($Edit == true) {
		$_POST['DebtorNo'] = $MyRow['debtorno'];
		$_POST['StockID'] = $MyRow['stockid'];
		$_POST['CategoryID'] = $MyRow['categoryid'];
		$_POST['Narrative'] = $MyRow['narrative'];
		$_POST['RebatePercent'] = locale_number_format($MyRow['rebatepercent']*100,2);
		$_POST['RebateAmount'] = locale_number_format($MyRow['rebateamount'],$CurrDecimalPlaces);
		$_POST['EffectiveFrom'] = ConvertSQLDate($MyRow['effectivefrom']);
		$_POST['EffectiveTo'] = ConvertSQLDate($MyRow['effectiveto']);

		echo '<input type="hidden" name="SellSupportID" value="' . $MyRow['id'] . '" />';
		echo '<legend>', __('Edit Sell Through Support Deal'), '</legend>';
	} else {
		echo '<legend>', __('Create Sell Through Support Deal'), '</legend>';
	}

	if (!isset($_POST['RebateAmount'])) {
		$_POST['RebateAmount'] = 0;
	}
	if (!isset($_POST['RebatePercent'])) {
		$_POST['RebatePercent'] = 0;
	}
	if (!isset($_POST['EffectiveFrom'])) {
		$_POST['EffectiveFrom'] = Date($_SESSION['DefaultDateFormat']);
	}
	if (!isset($_POST['EffectiveTo'])) {
		/* Default EffectiveTo to the end of the month */
		$_POST['EffectiveTo'] = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m')+1,0,Date('y')));
	}
	if (!isset($_POST['DebtorNo'])){
		$_POST['DebtorNo']='';
	}
	if (!isset($_POST['Narrative'])){
		$_POST['Narrative'] ='';
	}


	echo '<field>
			<label for="DebtorNo">'. __('Support for Customer') . ':</label>
			<select name="DebtorNo">';
	if ($_POST['DebtorNo']=='') {
		echo '<option selected="selected" value="">' . __('All Customers') . '</option>';
	} else {
		echo '<option value="">' . __('All Customers') . '</option>';
	}

	$CustomerResult = DB_query("SELECT debtorno, name FROM debtorsmaster");

	while ($CustomerRow = DB_fetch_array($CustomerResult)){
		if ($CustomerRow['debtorno'] == $_POST['DebtorNo']){
			echo '<option selected="selected" value="' . $CustomerRow['debtorno'] . '">' . $CustomerRow['name'] . '</option>';
		} else {
			echo '<option value="' . $CustomerRow['debtorno'] . '">' . $CustomerRow['name'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="CategoryID">' . __('Support Whole Category') . ':</label>
			<select name="CategoryID">';
	if ($_POST['CategoryID']=='') {
		echo '<option selected="selected" value="">' . __('Specific Item Only') . '</option>';
	} else {
		echo '<option value="">' . __('Specific Item Only') . '</option>';
	}

	$CategoriesResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype='F'");

	while ($CategoriesRow = DB_fetch_array($CategoriesResult)){
		if ($CategoriesRow['categoryid'] == $_POST['CategoryID']){
			echo '<option selected="selected" value="' . $CategoriesRow['categoryid'] . '">' . $CategoriesRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $CategoriesRow['categoryid'] . '">' . $CategoriesRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="StockID">' . __('Support Specific Item') . ':</label>
			<select name="StockID">';
	if ($_POST['StockID']=='') {
		echo '<option selected="selected" value="">' . __('Support An Entire Category') . '</option>';
	} else {
		echo '<option value="">' . __('Support An Entire Category') . '</option>';
	}


	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description
			FROM purchdata INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
			WHERE supplierno ='" . $SupplierID . "'
			AND preferred=1";
	$ErrMsg = __('Could not retrieve the items that the supplier provides');
	$ItemsResult = DB_query($SQL, $ErrMsg);

	while ($ItemsRow = DB_fetch_array($ItemsResult)){
		if ($ItemsRow['stockid'] == $_POST['StockID']){
			echo '<option selected="selected" value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . ' - ' . $ItemsRow['description'] . '</option>';
		} else {
			echo '<option value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . ' - ' . $ItemsRow['description'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Narrative">' . __('Narrative') . ':</label>
			<input type="text" name="Narrative" maxlength="20" size="21" value="' . $_POST['Narrative'] . '" />
		</field>
		 <field>
			<label for="RebateAmount">' . __('Rebate value per unit') . ' (' . $SuppRow['currcode'] . '):</label>
			<input type="text" class="number" name="RebateAmount" maxlength="12" size="12" value="' . $_POST['RebateAmount'] . '" />
		</field>
		<field>
			<label for="RebatePercent">' . __('Rebate Percent') . ':</label>
			<input type="text" class="number" name="RebatePercent" maxlength="5" size="6" value="' . $_POST['RebatePercent'] . '" />%
		</field>
		<field>
			<label for="EffectiveFrom">' . __('Support Start Date') . ':</label>
			<input type="date" name="EffectiveFrom" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['EffectiveFrom']) . '" />
		</field>
		<field>
			<label for="EffectiveTo">' . __('Support End Date') . ':</label>
			<input type="date" name="EffectiveTo" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['EffectiveTo']) . '" />
		</field>
		</fieldset>
		<div class="centre">';
	if ($Edit == true) {
		echo '<input type="submit" name="UpdateRecord" value="' . __('Update') . '" />';
		echo '<input type="hidden" name="Edit" value="1" />';

		/*end if there is a supplier sell through support record being updated */
	} else {
		echo '<input type="submit" name="AddRecord" value="' . __('Add') . '" />';
	}

	echo '</div>
		</form>';
}

include('includes/footer.php');
