<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Purchasing Data');
$ViewTopic = 'PurchaseOrdering';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['EffectiveFrom'])){$_POST['EffectiveFrom'] = ConvertSQLDate($_POST['EffectiveFrom']);}

if (isset($_GET['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_POST['SupplierID']));
}

if (isset($_GET['StockID'])) {
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

if (isset($_GET['Edit'])) {
	$Edit = true;
} elseif (isset($_POST['Edit'])) {
	$Edit = true;
} else {
	$Edit = false;
}

if (isset($_GET['EffectiveFrom'])) {
	$EffectiveFrom = $_GET['EffectiveFrom'];
} elseif ($Edit == true AND isset($_POST['EffectiveFrom'])) {
	$EffectiveFrom = FormatDateForSQL($_POST['EffectiveFrom']);
}

if (isset($_POST['StockUOM'])) {
	$StockUOM = $_POST['StockUOM'];
}

/*Deleting a supplier purchasing discount */
if (isset($_GET['DeleteDiscountID'])){
	$Result = DB_query("DELETE FROM supplierdiscounts WHERE id='" . intval($_GET['DeleteDiscountID']) . "'");
	prnMsg(__('Deleted the supplier discount record'),'success');
}


$NoPurchasingData = 0;

echo '<a href="' . $RootPath . '/SelectProduct.php" class="toplink">' . __('Back to Items') . '</a>';

if (isset($_POST['SupplierDescription'])) {
	$_POST['SupplierDescription'] = trim($_POST['SupplierDescription']);
}

if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($SupplierID)) { /*Validate Inputs */
	$InputError = 0; /*Start assuming the best */

	if ($StockID == '' OR !isset($StockID)) {
		$InputError = 1;
		prnMsg(__('There is no stock item set up enter the stock code or select a stock item using the search page'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['Price']))) {
		$InputError = 1;
		unset($_POST['Price']);
		prnMsg(__('The price entered was not numeric and a number is expected. No changes have been made to the database'), 'error');
	} elseif ($_POST['Price'] == 0) {
		prnMsg(__('The price entered is zero') . '   ' . __('Is this intentional?'), 'warn');
	}
	if (!is_numeric(filter_number_format($_POST['LeadTime']))) {
		$InputError = 1;
		unset($_POST['LeadTime']);
		prnMsg(__('The lead time entered was not numeric a number of days is expected no changes have been made to the database'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['MinOrderQty']))) {
		$InputError = 1;
		unset($_POST['MinOrderQty']);
		prnMsg(__('The minimum order quantity was not numeric and a number is expected no changes have been made to the database'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['ConversionFactor']))) {
		$InputError = 1;
		unset($_POST['ConversionFactor']);
		prnMsg(__('The conversion factor entered was not numeric') . ' (' . __('a number is expected') . '). ' .
			__('The conversion factor is the number which the price must be divided by to get the unit price in our unit of measure') . '. <br />' .
			__('E.g.') . ' ' . __('The supplier sells an item by the tonne and we hold stock by the kg') . '. ' .
			__('The suppliers price must be divided by 1000 to get to our cost per kg') . '. ' .
			__('The conversion factor to enter is 1000') . '. <br /><br />' .
			__('No changes will be made to the database'), 'error');
	}
	if (!Is_Date($_POST['EffectiveFrom'])){
		$InputError = 1;
		unset($_POST['EffectiveFrom']);
		prnMsg(__('The date this purchase price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if ($InputError == 0 AND isset($_POST['AddRecord'])) {
		$SQL = "INSERT INTO purchdata (supplierno,
										stockid,
										price,
										effectivefrom,
										suppliersuom,
										conversionfactor,
										supplierdescription,
										suppliers_partno,
										leadtime,
										minorderqty,
										preferred)
						VALUES ('" . $SupplierID . "',
							'" . $StockID . "',
							'" . filter_number_format($_POST['Price']) . "',
							'" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
							'" . $_POST['SuppliersUOM'] . "',
							'" . filter_number_format($_POST['ConversionFactor']) . "',
							'" . mb_substr(DB_escape_string($_POST['SupplierDescription']), 0, 50) . "',
							'" . mb_substr(DB_escape_string($_POST['SupplierCode']), 0, 50) . "',
							'" . filter_number_format($_POST['LeadTime']) . "',
							'" . filter_number_format($_POST['MinOrderQty']) . "',
							'" . $_POST['Preferred'] . "')";
		$ErrMsg = __('The supplier purchasing details could not be added to the database because');
		$AddResult = DB_query($SQL, $ErrMsg);
		prnMsg(__('This supplier purchasing data has been added to the database'), 'success');
	}
	if ($InputError == 0 AND isset($_POST['UpdateRecord'])) {
		$SQL = "UPDATE purchdata SET price='" . filter_number_format($_POST['Price']) . "',
										effectivefrom='" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
										suppliersuom='" . $_POST['SuppliersUOM'] . "',
										conversionfactor='" . filter_number_format($_POST['ConversionFactor']) . "',
										supplierdescription='" . mb_substr(DB_escape_string($_POST['SupplierDescription']), 0, 50) . "',
										suppliers_partno='" . mb_substr(DB_escape_string($_POST['SupplierCode']), 0, 50) . "',
										leadtime='" . filter_number_format($_POST['LeadTime']) . "',
										minorderqty='" . filter_number_format($_POST['MinOrderQty']) . "',
										preferred='" . $_POST['Preferred'] . "'
							WHERE purchdata.stockid='" . $StockID . "'
							AND purchdata.supplierno='" . $SupplierID . "'
							AND purchdata.effectivefrom='" . $_POST['WasEffectiveFrom'] . "'";
		$ErrMsg = __('The supplier purchasing details could not be updated because');
		$UpdResult = DB_query($SQL, $ErrMsg);
		prnMsg(__('Supplier purchasing data has been updated'), 'success');

		/*Now need to validate supplier purchasing discount records  and update/insert as necessary */
		$ErrMsg = __('The supplier purchasing discount details could not be updated because');
		$DiscountInputError = false;
		for ($i = 0; $i < $_POST['NumberOfDiscounts']; $i++) {
			if (mb_strlen($_POST['DiscountNarrative' . $i]) == 0 OR $_POST['DiscountNarrative' . $i] == ''){
				prnMsg(__('Supplier discount narrative cannot be empty. No changes will be made to this record'),'error');
				$DiscountInputError = true;
			} elseif (filter_number_format($_POST['DiscountPercent' . $i]) > 100 OR filter_number_format($_POST['DiscountPercent' . $i]) < 0) {
				prnMsg(__('Supplier discount percent must be greater than zero but less than 100 percent. No changes will be made to this record'),'error');
				$DiscountInputError = true;
			} elseif (filter_number_format($_POST['DiscountPercent' . $i]) <> 0 AND filter_number_format($_POST['DiscountAmount' . $i]) <> 0) {
				prnMsg(__('Both the supplier discount percent and discount amount are non-zero. Only one or the other can be used. No changes will be made to this record'),'error');
				$DiscountInputError = true;
			} elseif (Date1GreaterThanDate2($_POST['DiscountEffectiveFrom' . $i], $_POST['DiscountEffectiveTo' .$i])) {
				prnMsg(__('The effective to date is prior to the effective from date. No changes will be made to this record'),'error');
				$DiscountInputError = true;
			}
			if ($DiscountInputError == false) {
				$SQL = "UPDATE supplierdiscounts SET discountnarrative ='" . $_POST['DiscountNarrative' . $i] . "',
													discountamount ='" . filter_number_format($_POST['DiscountAmount' . $i]) . "',
													discountpercent = '" . filter_number_format($_POST['DiscountPercent' . $i]) / 100 . "',
													effectivefrom = '" . FormatDateForSQL($_POST['DiscountEffectiveFrom' . $i]) . "',
													effectiveto = '" . FormatDateForSQL($_POST['DiscountEffectiveTo' . $i]) . "'
						WHERE id = " . intval($_POST['DiscountID' . $i]);
				$UpdResult = DB_query($SQL, $ErrMsg);
			}
		} /*end loop through all supplier discounts */

		/*Now check to see if a new Supplier Discount has been entered */
		if (mb_strlen($_POST['DiscountNarrative']) == 0 OR $_POST['DiscountNarrative'] == ''){
			/* A new discount entry has not been entered */
		} elseif (filter_number_format($_POST['DiscountPercent']) > 100 OR filter_number_format($_POST['DiscountPercent']) < 0) {
			prnMsg(__('Supplier discount percent must be greater than zero but less than 100 percent. This discount record cannot be added.'),'error');
		} elseif (filter_number_format($_POST['DiscountPercent']) <> 0 AND filter_number_format($_POST['DiscountAmount']) <> 0) {
			prnMsg(__('Both the supplier discount percent and discount amount are non-zero. Only one or the other can be used. This discount record cannot be added.'),'error');
		} elseif (Date1GreaterThanDate2($_POST['DiscountEffectiveFrom'], $_POST['DiscountEffectiveTo'])) {
			prnMsg(__('The effective to date is prior to the effective from date. This discount record cannot be added.'),'error');
		} elseif(filter_number_format($_POST['DiscountPercent']) == 0 AND filter_number_format($_POST['DiscountAmount']) == 0) {
			prnMsg(__('Some supplier discount narrative was entered but both the discount amount and the discount percent are zero. One of these must be none zero to create a valid supplier discount record. The supplier discount record was not added.'),'error');
		} else {
			/*It looks like a valid new discount entry has been entered - need to insert it into DB */
			$SQL = "INSERT INTO supplierdiscounts ( supplierno,
													stockid,
													discountnarrative,
													discountamount,
													discountpercent,
													effectivefrom,
													effectiveto )
						VALUES ('" . $SupplierID . "',
								'" . $StockID . "',
								'" . $_POST['DiscountNarrative'] . "',
								'" . floatval($_POST['DiscountAmount']) . "',
								'" . floatval($_POST['DiscountPercent']) / 100 . "',
								'" . FormatDateForSQL($_POST['DiscountEffectiveFrom']) . "',
								'" . FormatDateForSQL($_POST['DiscountEffectiveTo']) . "')";
			$ErrMsg = __('Could not insert a new supplier discount entry because');
			$InsertResult = DB_query($SQL, $ErrMsg);
			prnMsg(__('A new supplier purchasing discount record was entered successfully'),'success');
		}

	}

	if ($InputError == 0 AND isset($_POST['AddRecord'])) {
	/*  insert took place and need to clear the form  */
		unset($SupplierID);
		unset($_POST['Price']);
		unset($CurrCode);
		unset($_POST['SuppliersUOM']);
		unset($_POST['EffectiveFrom']);
		unset($_POST['ConversionFactor']);
		unset($_POST['SupplierDescription']);
		unset($_POST['LeadTime']);
		unset($_POST['Preferred']);
		unset($_POST['SupplierCode']);
		unset($_POST['MinOrderQty']);
		unset($SuppName);
		for ($i = 0; $i < $_POST['NumberOfDiscounts']; $i++) {
			unset($_POST['DiscountNarrative' . $i]);
			unset($_POST['DiscountAmount' . $i]);
			unset($_POST['DiscountPercent' . $i]);
			unset($_POST['DiscountEffectiveFrom' . $i]);
			unset($_POST['DiscountEffectiveTo' . $i]);
		}
		unset($_POST['NumberOfDiscounts']);

	}
}

if (isset($_GET['Delete'])) {
	$SQL = "DELETE FROM purchdata
			WHERE purchdata.supplierno='" . $SupplierID . "'
				AND purchdata.stockid='" . $StockID . "'
				AND purchdata.effectivefrom='" . $EffectiveFrom . "'";
	$ErrMsg = __('The supplier purchasing details could not be deleted because');
	$DelResult = DB_query($SQL, $ErrMsg);
	prnMsg(__('This purchasing data record has been successfully deleted'), 'success');
	unset($SupplierID);
}


if ($Edit == false) {

	$ItemResult = DB_query("SELECT description FROM stockmaster WHERE stockid='" . $StockID . "'");
	$DescriptionRow = DB_fetch_array($ItemResult);
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' .
		' ' . $Title . ' ' . __('For Stock Code') . ' - ' . $StockID . ' - ' . $DescriptionRow['description'] . '</p>';

	$SQL = "SELECT purchdata.supplierno,
				suppliers.suppname,
				purchdata.price,
				suppliers.currcode,
				purchdata.effectivefrom,
				purchdata.suppliersuom,
				purchdata.supplierdescription,
				purchdata.leadtime,
				purchdata.suppliers_partno,
				purchdata.minorderqty,
				purchdata.preferred,
				purchdata.conversionfactor,
				currencies.decimalplaces AS currdecimalplaces
			FROM purchdata
			INNER JOIN suppliers
				ON purchdata.supplierno=suppliers.supplierid
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE purchdata.stockid = '" . $StockID . "'
			ORDER BY purchdata.effectivefrom DESC";
	$ErrMsg = __('The supplier purchasing details for the selected part could not be retrieved because');
	$PurchDataResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($PurchDataResult) == 0 and $StockID != '') {
		prnMsg(__('There is no purchasing data set up for the part selected'), 'info');
		$NoPurchasingData = 1;
	} else if ($StockID != '') {

		echo '<table cellpadding="2" class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Supplier') . '</th>
					<th class="SortedColumn">' . __('Price') . '</th>
					<th>' . __('Supplier Unit') . '</th>
					<th>' . __('Conversion Factor') . '</th>
					<th class="SortedColumn">' . __('Cost Per Our Unit') .  '</th>
					<th class="SortedColumn">' . __('Currency') . '</th>
					<th class="SortedColumn">' . __('Effective From') . '</th>
					<th class="SortedColumn">' . __('Min Order Qty') . '</th>
					<th class="SortedColumn">' . __('Lead Time') . '</th>
					<th>' . __('Preferred') . '</th>
					<th colspan="3"></th>
				</tr>
			</thead>
			<tbody>';

		$CountPreferreds = 0;

		while ($MyRow = DB_fetch_array($PurchDataResult)) {
			if ($MyRow['preferred'] == 1) {
				$DisplayPreferred = __('Yes');
				$CountPreferreds++;
			} else {
				$DisplayPreferred = __('No');
			}
			$UPriceDecimalPlaces = max($MyRow['currdecimalplaces'],$_SESSION['StandardCostDecimalPlaces']);
			$CostPerUnit = ($MyRow['conversionfactor'] != 0) ? $MyRow['price'] / $MyRow['conversionfactor'] : 0;
			echo '<tr class="striped_row">
					<td>', $MyRow['suppname'], '</td>
					<td class="number">', locale_number_format($MyRow['price'],$UPriceDecimalPlaces), '</td>
					<td>', $MyRow['suppliersuom'], '</td>
					<td class="number">', locale_number_format($MyRow['conversionfactor'],'Variable'), '</td>
					<td class="number">', locale_number_format($CostPerUnit,$UPriceDecimalPlaces), '</td>
					<td>', $MyRow['currcode'], '</td>
					<td class="date">', ConvertSQLDate($MyRow['effectivefrom']), '</td>
					<td>', locale_number_format($MyRow['minorderqty'],'Variable'), '</td>
					<td>', locale_number_format($MyRow['leadtime'],'Variable'), ' ' . __('days') . '</td>
					<td>', $DisplayPreferred, '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF']), '?StockID=', $StockID, '&amp;SupplierID=', $MyRow['supplierno'], '&amp;Edit=1&amp;EffectiveFrom=', $MyRow['effectivefrom'], '">' . __('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF']), '?StockID=', $StockID, '&amp;SupplierID=', $MyRow['supplierno'], '&amp;Copy=1&amp;EffectiveFrom=', $MyRow['effectivefrom'], '">' . __('Copy') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF']), '?StockID=', $StockID, '&amp;SupplierID=', $MyRow['supplierno'], '&amp;Delete=1&amp;EffectiveFrom=', $MyRow['effectivefrom'], '" onclick=\'return confirm("' . __('Are you sure you wish to delete this suppliers price?') . '");\'>' . __('Delete') . '</a></td>
				</tr>';
		} //end of while loop
		echo '</tbody></table>';
		if ($CountPreferreds > 1) {
			prnMsg(__('There are now') . ' ' . $CountPreferreds . ' ' . __('preferred suppliers set up for') . ' ' . $StockID . ' ' .
				__('you should edit the supplier purchasing data to make only one supplier the preferred supplier'), 'warn');
		} elseif ($CountPreferreds == 0) {
			prnMsg(__('There are NO preferred suppliers set up for') . ' ' . $StockID . ' ' .
				__('you should make one supplier only the preferred supplier'), 'warn');
		}
	} // end of there are purchsing data rows to show
} /* Only show the existing purchasing data records if one is not being edited */

if (isset($SupplierID) AND $SupplierID != '' AND !isset($_POST['SearchSupplier'])) {
	/*NOT EDITING AN EXISTING BUT SUPPLIER selected OR ENTERED*/

    $SQL = "SELECT suppliers.suppname,
					suppliers.currcode,
					currencies.decimalplaces AS currdecimalplaces
			FROM suppliers
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE supplierid='".$SupplierID."'";
    $ErrMsg = __('The supplier details for the selected supplier could not be retrieved because');
    $SuppSelResult = DB_query($SQL, $ErrMsg);
    if (DB_num_rows($SuppSelResult) == 1) {
        $MyRow = DB_fetch_array($SuppSelResult);
        $SuppName = $MyRow['suppname'];
        $CurrCode = $MyRow['currcode'];
        $CurrDecimalPlaces = $MyRow['currdecimalplaces'];
    } else {
        prnMsg(__('The supplier code') . ' ' . $SupplierID . ' ' . __('is not an existing supplier in the database') . '. ' . __('You must enter an alternative supplier code or select a supplier using the search facility below'), 'error');
        unset($SupplierID);
    }
} else {
	if ($NoPurchasingData==0) {
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . ' ' . __('For Stock Code') . ' - ' . $StockID . '</p>';
	}
    if (!isset($_POST['SearchSupplier'])) {
        echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<fieldset>
					<legend>', __('Supplier Selection'), '</legend>
					<field>
						<input type="hidden" name="StockID" value="' . $StockID . '" />
						<label for="Keywords">' . __('Text in the Supplier') . ' <b>' . __('NAME') . '</b>:</label>
						<input type="text" name="Keywords" size="20" maxlength="25" />
					</field>
					<field>
						<label for="SupplierCode">' . '<b>'. __('OR'). ' </b>'. __('Text in Supplier') . ' <b>' . __('CODE') . '</b>:</label>
						<input type="text" name="SupplierCode" data-type="no-illegal-chars" size="20" maxlength="50" />
					</field>
				</fieldset>
				<div class="centre">
					<input type="submit" name="SearchSupplier" value="' . __('Find Suppliers Now') . '" />
				</div>
			</form>';
        include('includes/footer.php');
        exit();
    }
}

if ($Edit == true) {
	$ItemResult = DB_query("SELECT description FROM stockmaster WHERE stockid='" . $StockID . "'");
	$DescriptionRow = DB_fetch_array($ItemResult);
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' .
		' ' . $Title . ' ' . __('For Stock Code') . ' - ' . $StockID . ' - ' . $DescriptionRow['description'] . '</p>';
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
} //end of if search

if (isset($SuppliersResult)) {
	if (isset($StockID)) {
        $Result = DB_query("SELECT stockmaster.description,
								stockmaster.units,
								stockmaster.mbflag
						FROM stockmaster
						WHERE stockmaster.stockid='".$StockID."'");
		$MyRow = DB_fetch_row($Result);
		$StockUOM = $MyRow[1];
		if (DB_num_rows($Result) == 1) {
			if ($MyRow[2] == 'D' OR $MyRow[2] == 'A' OR $MyRow[2] == 'K') {
				prnMsg($StockID . ' - ' . $MyRow[0] . '<p> ' . __('The item selected is a dummy part or an assembly or kit set part') . ' - ' . __('it is not purchased') . '. ' . __('Entry of purchasing information is therefore inappropriate'), 'warn');
				include('includes/footer.php');
				exit();
			}
		} else {
			prnMsg(__('Stock Item') . ' - ' . $StockID . ' ' . __('is not defined in the database'), 'warn');
		}
	} else {
		$StockID = '';
		$StockUOM = 'each';
	}
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">
			<table cellpadding="2" colspan="7" class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Code') . '</th>
				<th class="SortedColumn">' . __('Supplier Name') . '</th>
				<th class="SortedColumn">' . __('Currency') . '</th>
				<th class="SortedColumn">' . __('Address 1') . '</th>
				<th class="SortedColumn">' . __('Address 2') . '</th>
				<th class="SortedColumn">' . __('Address 3') . '</th>
			</tr>
		</thead>
		<tbody>';

    while ($MyRow = DB_fetch_array($SuppliersResult)) {
		echo '<tr class="striped_row">
				<td><input type="submit" name="SupplierID" value="', $MyRow['supplierid'], '" /></td>
				<td>', $MyRow['suppname'], '</td>
				<td>', $MyRow['currcode'], '</td>
				<td>', $MyRow['address1'], '</td>
				<td>', $MyRow['address2'], '</td>
				<td>', $MyRow['address3'], '</td>
			</tr>';

        echo '<input type="hidden" name="StockID" value="' . $StockID . '" />';
        echo '<input type="hidden" name="StockUOM" value="' . $StockUOM . '" />';

    }
    //end of while loop
    echo '</tbody>
		</table>
	</form>';
}
//end if results to show

/*Show the input form for new supplier purchasing details */
if (!isset($SuppliersResult)) {
	if ($Edit == true OR isset($_GET['Copy'])) {

		 $SQL = "SELECT purchdata.supplierno,
						suppliers.suppname,
						purchdata.price,
						purchdata.effectivefrom,
						suppliers.currcode,
						purchdata.suppliersuom,
						purchdata.supplierdescription,
						purchdata.leadtime,
						purchdata.conversionfactor,
						purchdata.suppliers_partno,
						purchdata.minorderqty,
						purchdata.preferred,
						stockmaster.units,
						currencies.decimalplaces AS currdecimalplaces
				FROM purchdata
				INNER JOIN suppliers
					ON purchdata.supplierno=suppliers.supplierid
				INNER JOIN stockmaster
					ON purchdata.stockid=stockmaster.stockid
				INNER JOIN currencies
					ON suppliers.currcode = currencies.currabrev
				WHERE purchdata.supplierno='" . $SupplierID . "'
					AND purchdata.stockid='" . $StockID . "'
					AND purchdata.effectivefrom='" . $EffectiveFrom . "'";

		$ErrMsg = __('The supplier purchasing details for the selected supplier and item could not be retrieved because');
		$EditResult = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($EditResult);
		$SuppName = $MyRow['suppname'];
		$UPriceDecimalPlaces = max($MyRow['currdecimalplaces'],$_SESSION['StandardCostDecimalPlaces']);
		if ($Edit == true) {
			$_POST['Price'] = locale_number_format(round($MyRow['price'],$UPriceDecimalPlaces),$UPriceDecimalPlaces);
			$_POST['EffectiveFrom'] = ConvertSQLDate($MyRow['effectivefrom']);
		} else { // we are copying a blank record effective from today
			$_POST['Price'] = 0;
			$_POST['EffectiveFrom'] = Date($_SESSION['DefaultDateFormat']);
		}
		$CurrCode = $MyRow['currcode'];
		$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
		$_POST['SuppliersUOM'] = $MyRow['suppliersuom'];
		$_POST['SupplierDescription'] = $MyRow['supplierdescription'];
		$_POST['LeadTime'] = locale_number_format($MyRow['leadtime'],'Variable');

		$_POST['ConversionFactor'] = locale_number_format($MyRow['conversionfactor'],'Variable');
		$_POST['Preferred'] = $MyRow['preferred'];
		$_POST['MinOrderQty'] = locale_number_format($MyRow['minorderqty'],'Variable');
		$_POST['SupplierCode'] = $MyRow['suppliers_partno'];
		$StockUOM=$MyRow['units'];
    }
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    if (!isset($SupplierID)) {
        $SupplierID = '';
    }
    echo '<fieldset>
			<legend>', __('Purchasing Data'), '</legend>';
	if ($Edit == true) {
		echo '<field>
				<label for="SupplierID">' . __('Supplier Name') . ':</label>
				<input type="hidden" name="SupplierID" value="' . $SupplierID . '" />
				<fieldtext>' . $SupplierID . ' - ' . $SuppName . '</fieldtext>
				<input type="hidden" name="WasEffectiveFrom" value="' . $MyRow['effectivefrom'] . '" />
			</field>';
    } else {
        echo '<field>
				<label for="SupplierID">' . __('Supplier Name') . ':</label>
				<input type="hidden" name="SupplierID" maxlength="10" size="11" value="' . $SupplierID . '" />';

		if ($SupplierID!='') {
			echo '<fieldtext>' . $SuppName;
		}
		if (!isset($SuppName) OR $SuppName = '') {
			echo '(' . __('A search facility is available below if necessary') . ')';
		} else {
			echo '<fieldtext>' . $SuppName;
		}
		echo '</fieldtext>
			</field>';
	}
	echo '<input type="hidden" name="StockID" maxlength="10" size="11" value="' . $StockID . '" />';
	if (!isset($CurrCode)) {
		$CurrCode = '';
	}
	if (!isset($_POST['Price'])) {
		$_POST['Price'] = 0;
	}
	if (!isset($_POST['EffectiveFrom'])) {
		$_POST['EffectiveFrom'] = Date($_SESSION['DefaultDateFormat']);
	}
	if (!isset($_POST['SuppliersUOM'])) {
		$_POST['SuppliersUOM'] = '';
	}
	if (!isset($_POST['SupplierDescription'])) {
		$_POST['SupplierDescription'] = '';
	}
	if (!isset($_POST['SupplierCode'])) {
		$_POST['SupplierCode'] = '';
	}
	if (!isset($_POST['MinOrderQty'])) {
		$_POST['MinOrderQty'] = '1';
	}
	echo '<field>
			<label for="CurrCode">' . __('Currency') . ':</label>
			<input type="hidden" name="CurrCode" . value="' . $CurrCode . '" />
			<fieldtext>' . $CurrCode . '</fieldtext>
		</field>
		<field>
			<label for="Price">' . __('Price') . ' (' . __('in Supplier Currency') . '):</label>
			<input type="text" class="number" name="Price" maxlength="12" size="12" value="' . $_POST['Price'] . '" />
		</field>
		<field>
			<label for="EffectiveFrom">' . __('Price Effective From') . ':</label>
			<input type="date" name="EffectiveFrom" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['EffectiveFrom']) . '" />
		</field>
		<field>
			<label>' . __('Our Unit of Measure') . ':</label>';

	if (isset($SupplierID)) {
		echo '<fieldtext>' . $StockUOM . '</fieldtext></field>';
	}
	echo '<field>
			<label for="SuppliersUOM">' . __('Suppliers Unit of Measure') . ':</label>
			<input type="text" name="SuppliersUOM" size="20" maxlength="20" value ="' . $_POST['SuppliersUOM'] . '"/>
		</field>';

	if (!isset($_POST['ConversionFactor']) OR $_POST['ConversionFactor'] == '') {
		$_POST['ConversionFactor'] = 1;
	}

	echo '<field>
			<label for="ConversionFactor">' . __('Conversion Factor (to our UOM)') . ':</label>
			<input type="text" class="number" name="ConversionFactor" maxlength="12" size="12" value="' . $_POST['ConversionFactor'] . '" />
		</field>
		<field>
			<label for="SupplierCode">' . __('Supplier Stock Code') . ':</label>
			<input type="text" name="SupplierCode" maxlength="50" size="20" value="' . $_POST['SupplierCode'] . '" />
		</field>
		<field>
			<label for="MinOrderQty">' . __('MinOrderQty') . ':</label>
			<input type="text" class="number" name="MinOrderQty" maxlength="15" size="15" value="' . $_POST['MinOrderQty'] . '" />
		</field>
		<field>
			<label for="SupplierDescription">' . __('Supplier Stock Description') . ':</label>
			<input type="text" name="SupplierDescription" maxlength="50" size="51" value="' . $_POST['SupplierDescription'] . '" />
		</field>';

	if (!isset($_POST['LeadTime']) OR $_POST['LeadTime'] == "") {
		$_POST['LeadTime'] = 1;
	}
	echo '<field>
			<label for="LeadTime">' . __('Lead Time') . ' (' . __('in days from date of order') . '):</label>
			<input type="text" class="integer" name="LeadTime" maxlength="4" size="5" value="' . $_POST['LeadTime'] . '" />
		</field>
		<field>
			<label for="Preferred">' . __('Preferred Supplier') . ':</label>
			<select name="Preferred">';

	if ($_POST['Preferred'] == 1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>
				<option value="0">' . __('No')  . '</option>';
	} else {
		echo '<option value="1">' . __('Yes')  . '</option>
				<option selected="selected" value="0">' . __('No')  . '</option>';
	}
	echo '</select>
		</field>
		</fieldset>
		<div class="centre">';

	if ($Edit == true) {
		/* A supplier purchase price is being edited - also show the discounts applicable to the supplier  for update/deletion*/

		/*List the discount records for this supplier */
		$SQL = "SELECT id,
						discountnarrative,
						discountpercent,
						discountamount,
						effectivefrom,
						effectiveto
				FROM supplierdiscounts
				WHERE supplierno = '" . $SupplierID . "'
					AND stockid = '" . $StockID . "'";

		$ErrMsg = __('The supplier discounts could not be retrieved because');
		$DiscountsResult = DB_query($SQL, $ErrMsg);

		echo '<table cellpadding="2" colspan="7" class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('Discount Name') . '</th>
	               	<th class="SortedColumn">' . __('Discount') . '<br />' . __('Value') . '</th>
					<th class="SortedColumn">' . __('Discount') . '<br />' . __('Percent') . '</th>
					<th class="SortedColumn">' . __('Effective From') . '</th>
					<th class="SortedColumn">' . __('Effective To') . '</th>
				</tr>
			</thead>
			<tbody>';

	    $i = 0; //DiscountCounter
	    while ($MyRow = DB_fetch_array($DiscountsResult)) {
			echo '<tr class="striped_row">
					<input type="hidden" name="DiscountID', $i, '" value="', $MyRow['id'], '" />
					<td><input type="text" name="DiscountNarrative', $i, '" value="', $MyRow['discountnarrative'], '" maxlength="20" size="20" /></td>
					<td><input type="text" class="number" name="DiscountAmount', $i, '" value="', locale_number_format($MyRow['discountamount'],$CurrDecimalPlaces), '" maxlength="10" size="11" /></td>
					<td><input type="text" class="number" name="DiscountPercent', $i, '" value="', locale_number_format($MyRow['discountpercent']*100,2), '" maxlength="5" size="6" /></td>
					<td class="date"><input type="date" name="DiscountEffectiveFrom', $i, '" maxlength="10" size="11" value="', ConvertSQLDate($MyRow['effectivefrom']), '" /></td>
					<td class="date"><input type="date" name="DiscountEffectiveTo', $i, '" maxlength="10" size="11" value="', ConvertSQLDate($MyRow['effectiveto']), '" /></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF']), '?DeleteDiscountID=', $MyRow['id'], '&amp;StockID=', $StockID, '&amp;EffectiveFrom=', $EffectiveFrom, '&amp;SupplierID=', $SupplierID, '&amp;Edit=1">' . __('Delete') . '</a></td>
				</tr>';

			$i++;
		}//end of while loop

		echo '</tbody><input type="hidden" name="NumberOfDiscounts" value="' . $i . '" />';

		$DefaultEndDate = Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m') + 1,0,Date('y')));

		echo '<tr>
				<td><input type="text" name="DiscountNarrative" value="" maxlength="20" size="20" /></td>
				<td><input type="text" class="number" name="DiscountAmount" value="0" maxlength="10" size="11" /></td>
				<td><input type="text" class="number" name="DiscountPercent" value="0" maxlength="5" size="6" /></td>
				<td><input type="date" name="DiscountEffectiveFrom" maxlength="10" size="11" value="' . Date($_SESSION['DefaultDateFormat']) . '" /></td>
				<td><input type="date" name="DiscountEffectiveTo" maxlength="10" size="11" value="' . $DefaultEndDate . '" /></td>
			</tr>
			</table>';

		echo '<input type="submit" name="UpdateRecord" value="' . __('Update') . '" />';
		echo '<input type="hidden" name="Edit" value="1" />';

		/*end if there is a supplier purchasing price being updated */
	} else {
		echo '<input type="submit" name="AddRecord" value="' . __('Add') . '" />';
	}

	echo '</div>
		<div class="centre">';

	if (isset($StockLocation) AND isset($StockID) AND mb_strlen($StockID) != 0) {
		echo '<br /><a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . __('Show Stock Status') . '</a>';
		echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '&StockLocation=' . $StockLocation . '">' . __('Show Stock Movements') . '</a>';
		echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '&StockLocation=' . $StockLocation . '">' . __('Search Outstanding Sales Orders') . '</a>';
		echo '<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Completed Sales Orders') . '</a>';
	}
	echo '</form></div>';
}

include('includes/footer.php');
