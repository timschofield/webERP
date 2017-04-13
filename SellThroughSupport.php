<?php
/* $Id: SellThroughSupport.php 5785 2012-12-29 04:47:42Z daintree $*/

include ('includes/session.php');

$Title = _('Sell Through Support');

include ('includes/header.php');

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
	prnMsg(_('Deleted the supplier sell through support record'),'success');
}


if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($SupplierID)) { /*Validate Inputs */
	$InputError = 0; /*Start assuming the best */

	if (is_numeric(filter_number_format($_POST['RebateAmount']))==false) {
		$InputError = 1;
		prnMsg(_('The rebate amount entered was not numeric and a number is required.'), 'error');
		unset($_POST['RebateAmount']);
	} elseif (filter_number_format($_POST['RebateAmount']) == 0 AND filter_number_format($_POST['RebatePercent'])==0) {
		prnMsg(_('Both the rebate amount and the rebate percent is zero. One or the other must be a positive number?'), 'error');
		$InputError = 1;

/*
	} elseif (mb_strlen($_POST['Narrative'])==0 OR $_POST['Narrative']==''){
		prnMsg(_('The narrative cannot be empty.'),'error');
		$InputError = 1;
*/
	} elseif (filter_number_format($_POST['RebatePercent'])>100 OR  filter_number_format($_POST['RebatePercent']) < 0) {
		prnMsg(_('The rebate percent must be greater than zero but less than 100 percent. No changes will be made to this record'),'error');
		$InputError = 1;
	} elseif (filter_number_format($_POST['RebateAmount']) !=0 AND filter_number_format($_POST['RebatePercent'])!=0) {
		prnMsg(_('Both the rebate percent and rebate amount are non-zero. Only one or the other can be used.'),'error');
		$InputError = 1;
	} elseif (Date1GreaterThanDate2($_POST['EffectiveFrom'], $_POST['EffectiveTo'])) {
		prnMsg(_('The effective to date is prior to the effective from date.'),'error');
		$InputError = 1;
	}

    if ($InputError == 0 AND isset($_POST['AddRecord'])) {
        $sql = "INSERT INTO sellthroughsupport (supplierno,
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

        $ErrMsg = _('The sell through support record could not be added to the database because');
        $DbgMsg = _('The SQL that failed was');
        $AddResult = DB_query($sql, $ErrMsg, $DbgMsg);
        prnMsg(_('This sell through support has been added to the database'), 'success');
    }
    if ($InputError == 0 AND isset($_POST['UpdateRecord'])) {
        $sql = "UPDATE sellthroughsupport SET debtorno='" . $_POST['DebtorNo'] . "',
											categoryid='" . $_POST['CategoryID'] . "',
											stockid='" . $_POST['StockID'] . "',
											narrative='" . $_POST['Narrative'] . "',
											rebateamount='" . filter_number_format($_POST['RebateAmount']) . "',
											rebatepercent='" . filter_number_format($_POST['RebatePercent'])/100 . "',
											effectivefrom='" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
											effectiveto='" . FormatDateForSQL($_POST['EffectiveTo']) . "'
							WHERE id='" . $_POST['SellSupportID'] . "'";

		$ErrMsg = _('The sell through support record could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$UpdResult = DB_query($sql, $ErrMsg, $DbgMsg);
		prnMsg(_('Sell Through Support record has been updated'), 'success');
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
        prnMsg( _('Supplier Name keywords have been used in preference to the Supplier Code extract entered') . '.', 'info' );
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
    $ErrMsg = _('The suppliers matching the criteria entered could not be retrieved because');
    $DbgMsg = _('The SQL to retrieve supplier details that failed was');
    $SuppliersResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table cellpadding="2" colspan="7" class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    $TableHeader = '<tr>
						<th>' . _('Code') . '</th>
	                	<th>' . _('Supplier Name') . '</th>
						<th>' . _('Currency') . '</th>
						<th>' . _('Address 1') . '</th>
						<th>' . _('Address 2') . '</th>
						<th>' . _('Address 3') . '</th>
					</tr>';
    echo $TableHeader;
	$k = 0;
    while ($myrow = DB_fetch_array($SuppliersResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
       printf('<td><input type="submit" name="SupplierID" value="%s" /></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>',
				$myrow['supplierid'],
				$myrow['suppname'],
				$myrow['currcode'],
				$myrow['address1'],
				$myrow['address2'],
				$myrow['address3']);
	}//end of while loop
    echo '</table>
			<br/>
			</form>';
}//end if results to show
 elseif (!isset($SupplierID)) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table cellpadding="3" colspan="4" class="selection">
			<tr>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<td>' . _('Text in the Supplier') . ' <b>' . _('NAME') . '</b>:</td>
				<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Text in Supplier') . ' <b>' . _('CODE') . '</b>:</td>
				<td><input type="text" name="SupplierCode" size="20" maxlength="50" /></td>
			</tr>
			</table>
			<br />
			<div class="centre">
				<input type="submit" name="SearchSupplier" value="' . _('Find Suppliers Now') . '" />
			</div>
		</form>';
	include ('includes/footer.php');
	exit;
}



if (isset($SupplierID)) { /* Then display all the sell through support for the supplier */

	/*Get the supplier details */
	$SuppResult = DB_query("SELECT suppname,
									currcode,
									decimalplaces
							FROM suppliers INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE supplierid='" . $SupplierID . "'",$db);
	$SuppRow = DB_fetch_array($SuppResult);

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . ' ' . _('For Supplier') . ' - ' . $SupplierID . ' - ' . $SuppRow['suppname'] . '</p><br />';
}

if (isset($SupplierID) AND $Edit == false) {

    $sql = "SELECT	id,
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
    $ErrMsg = _('The supplier sell through support deals could not be retrieved because');
    $Result = DB_query($sql, $ErrMsg);
    if (DB_num_rows($Result)==0) {
		prnMsg(_('There are no sell through support deals entered for this supplier'), 'info');
    } else {
        echo '<table cellpadding="2" class="selection">';
        $TableHeader = '<tr>
							<th>' . _('Item or Category') . '</th>
							<th>' . _('Customer') . '</th>
							<th>' . _('Rebate') . '<br />' .  _('Value') . ' ' . $SuppRow['currcode'] . '</th>
							<th>' . _('Rebate') . '<br />' . _('Percent') . '</th>
							<th>' . _('Narrative') . '</th>
							<th>' . _('Effective From') . '</th>
							<th>' . _('Effective To') . '</th>
						</tr>';

		echo $TableHeader;
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			if ($myrow['categoryid']=='') {
				$ItemDescription = $myrow['stockid'] . ' - ' . $myrow['description'];
			} else {
				$ItemDescription = _('Any') . ' ' . $myrow['categorydescription'];
			}
			if ($myrow['debtorno']==''){
				$Customer = _('All Customers');
			} else {
				$Customer = $myrow['debtorno'] . ' - ' . $myrow['name'];
			}

            printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s?SellSupportID=%s&amp;SupplierID=%s&amp;Edit=1">' . _('Edit') . '</a></td>
					<td><a href="%s?SellSupportID=%s&amp;Delete=1&amp;SupplierID=%s" onclick=\'return confirm("' . _('Are you sure you wish to delete this sell through support record?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					$ItemDescription,
					$Customer,
					locale_number_format($myrow['rebateamount'],$SuppRow['decimalplaces']),
					locale_number_format($myrow['rebatepercent']*100,2),
					$myrow['narrative'],
					ConvertSQLDate($myrow['effectivefrom']),
					ConvertSQLDate($myrow['effectiveto']),
					htmlspecialchars($_SERVER['PHP_SELF']),
					$myrow['id'],
					$SupplierID,
					htmlspecialchars($_SERVER['PHP_SELF']),
					$myrow['id'],
					$SupplierID);
		} //end of while loop
		echo '</table><br/>';
    } // end of there are sell through support rows to show
    echo '<br/>';
} /* Only show the existing supplier sell through support records if one is not being edited */

/*Show the input form for new supplier sell through support details */
if (isset($SupplierID)) { //not selecting a supplier
	if ($Edit == true) {
		 $sql = "SELECT id,
						debtorno,
						rebateamount,
						rebatepercent,
						effectivefrom,
						effectiveto,
						stockid,
						categoryid,
						narrative
				FROM sellthroughsupport
				WHERE id='" . floatval($_GET['SellSupportID']) . "'";

		$ErrMsg = _('The supplier sell through support could not be retrieved because');
		$EditResult = DB_query($sql, $ErrMsg);
		$myrow = DB_fetch_array($EditResult);
	}

	$SuppName = $myrow['suppname'];

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="SupplierID" value="' . $SupplierID . '" />
			<table class="selection">';

	if ($Edit == true) {
		$_POST['DebtorNo'] = $myrow['debtorno'];
		$_POST['StockID'] = $myrow['stockid'];
		$_POST['CategoryID'] = $myrow['categoryid'];
		$_POST['Narrative'] = $myrow['narrative'];
		$_POST['RebatePercent'] = locale_number_format($myrow['rebatepercent']*100,2);
		$_POST['RebateAmount'] = locale_number_format($myrow['rebateamount'],$CurrDecimalPlaces);
		$_POST['EffectiveFrom'] = ConvertSQLDate($myrow['effectivefrom']);
		$_POST['EffectiveTo'] = ConvertSQLDate($myrow['effectiveto']);

		echo '<input type="hidden" name="SellSupportID" value="' . $myrow['id'] . '" />';
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


	echo '<tr>
			<td>' . _('Support for Customer') . ':</td>
			<td><select name="DebtorNo">';
	if ($_POST['DebtorNo']=='') {
		echo '<option selected="selected" value="">' . _('All Customers') . '</option>';
	} else {
		echo '<option value="">' . _('All Customers') . '</option>';
	}

	$CustomerResult = DB_query("SELECT debtorno, name FROM debtorsmaster");

	while ($CustomerRow = DB_fetch_array($CustomerResult)){
		if ($CustomerRow['debtorno'] == $_POST['DebtorNo']){
			echo '<option selected="selected" value="' . $CustomerRow['debtorno'] . '">' . $CustomerRow['name'] . '</option>';
		} else {
			echo '<option value="' . $CustomerRow['debtorno'] . '">' . $CustomerRow['name'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('Support Whole Category') . ':</td>
			<td><select name="CategoryID">';
	if ($_POST['CategoryID']=='') {
		echo '<option selected="selected" value="">' . _('Specific Item Only') . '</option>';
	} else {
		echo '<option value="">' . _('Specific Item Only') . '</option>';
	}

	$CategoriesResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype='F'");

	while ($CategoriesRow = DB_fetch_array($CategoriesResult)){
		if ($CategoriesRow['categoryid'] == $_POST['CategoryID']){
			echo '<option selected="selected" value="' . $CategoriesRow['categoryid'] . '">' . $CategoriesRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $CategoriesRow['categoryid'] . '">' . $CategoriesRow['categorydescription'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('Support Specific Item') . ':</td>
			<td><select name="StockID">';
	if ($_POST['StockID']=='') {
		echo '<option selected="selected" value="">' . _('Support An Entire Category') . '</option>';
	} else {
		echo '<option value="">' . _('Support An Entire Category') . '</option>';
	}


	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description
			FROM purchdata INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
			WHERE supplierno ='" . $SupplierID . "'
			AND preferred=1";
	$ErrMsg = _('Could not retrieve the items that the supplier provides');
	$DbgMsg = _('The SQL that was used to get the supplier items and failed was');
	$ItemsResult = DB_query($SQL,$ErrMsg,$DbgMsg);

	while ($ItemsRow = DB_fetch_array($ItemsResult)){
		if ($ItemsRow['stockid'] == $_POST['StockID']){
			echo '<option selected="selected" value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . ' - ' . $ItemsRow['description'] . '</option>';
		} else {
			echo '<option value="' . $ItemsRow['stockid'] . '">' . $ItemsRow['stockid'] . ' - ' . $ItemsRow['description'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('Narrative') . ':</td>
			<td><input type="text" name="Narrative" maxlength="20" size="21" value="' . $_POST['Narrative'] . '" /></td>
		</tr>
		 <tr>
			<td>' . _('Rebate value per unit') . ' (' . $SuppRow['currcode'] . '):</td>
			<td><input type="text" class="number" name="RebateAmount" maxlength="12" size="12" value="' . $_POST['RebateAmount'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Rebate Percent') . ':</td>
			<td><input type="text" class="number" name="RebatePercent" maxlength="5" size="6" value="' . $_POST['RebatePercent'] . '" />%</td>
		</tr>
		<tr>
			<td>' . _('Support Start Date') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveFrom" maxlength="10" size="11" value="' . $_POST['EffectiveFrom'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Support End Date') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveTo" maxlength="10" size="11" value="' . $_POST['EffectiveTo'] . '" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">';
	if ($Edit == true) {
		echo '<input type="submit" name="UpdateRecord" value="' . _('Update') . '" />';
		echo '<input type="hidden" name="Edit" value="1" />';

		/*end if there is a supplier sell through support record being updated */
	} else {
		echo '<input type="submit" name="AddRecord" value="' . _('Add') . '" />';
	}

	echo '</div>
		</form>';
}

include ('includes/footer.php');
?>
