<?php
/************************************************************/
/*To deploy this revised version take the following steps:  */
/*1. Add field called MinOrderQty as Int to purchdata       */
/*2. Copy this code on top of the standard PurchData.php    */
/*3. Copy the revised SelectProduct.php over the existing   */
/* pbj 11/3/2010                                            */
/************************************************************/

/* $Id: PurchData.php 3952 2010-09-30 15:22:14Z tim_schofield $*/

$PageSecurity = 4;

include ('includes/session.inc');

$title = _('Supplier Purchasing Data');

include ('includes/header.inc');

if (isset($_GET['SupplierID'])) {
    $SupplierID = trim(strtoupper($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])) {
    $SupplierID = trim(strtoupper($_POST['SupplierID']));
}

if (isset($_GET['StockID'])) {
    $StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
    $StockID = trim(strtoupper($_POST['StockID']));
}

if (isset($_POST['stockuom'])) {
	$stockuom=$_POST['stockuom'];
}

$NoPurchasingData=0;

echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</a><br>';

if (isset($_POST['SupplierDescription'])) {
    $_POST['SupplierDescription'] = trim($_POST['SupplierDescription']);
}

if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($SupplierID)) { /*Validate Inputs */
    $InputError = 0; /*Start assuming the best */
    if ($StockID == '' OR !isset($StockID)) {
        $InputError = 1;
        prnMsg(_('There is no stock item set up enter the stock code or select a stock item using the search page'), 'error');
    }
    if (!is_numeric($_POST['Price'])) {
        $InputError = 1;
        unset($_POST['Price']);
        prnMsg(_('The price entered was not numeric') . ' (' . _('a number is expected') . ') - ' . _('no changes have been made to the database'), 'error');
    }
    if ($_POST['Price'] == 0) {
        prnMsg(_('The price entered is zero') . '   ' . _('Is this intentional?'), 'warn');
    }
    if (!is_numeric($_POST['LeadTime'])) {
        $InputError = 1;
        unset($_POST['LeadTime']);
        prnMsg(_('The lead time entered was not numeric') . ' (' . _('a number is expected') . ') - ' . _('no changes have been made to the database'), 'error');
    }
    if (!is_numeric($_POST['MinOrderQty'])) {
        $InputError = 1;
        unset($_POST['LeadTime']);
        prnMsg(_('The lead time entered was not numeric') . ' (' . _('a number is expected') . ') - ' . _('no changes have been made to the database'), 'error');
    }
    if (!is_numeric($_POST['ConversionFactor'])) {
        $InputError = 1;
        unset($_POST['ConversionFactor']);
        prnMsg(_('The conversion factor entered was not numeric') . ' (' . _('a number is expected') . '). ' . _('The conversion factor is the number which the price must be divided by to get the unit price in our unit of measure') . '. <br>' . _('E.g.') . ' ' . _('The supplier sells an item by the tonne and we hold stock by the kg') . '. ' . _('The suppliers price must be divided by 1000 to get to our cost per kg') . '. ' . _('The conversion factor to enter is 1000') . '. <br><br>' . _('No changes will be made to the database'), 'error');
    }
    if ($InputError == 0 AND isset($_POST['AddRecord'])) {
        $sql = "INSERT INTO purchdata (supplierno,
					stockid,
					price,
					effectivefrom,
					suppliersuom,
					conversionfactor,
					supplierdescription,
					suppliers_partno,
					leadtime,
                                  MinOrderQty,
					preferred)
			VALUES ('" . $SupplierID . "',
				'" . $StockID . "',
				'" . $_POST['Price'] . "',
				'" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
				'" . $_POST['SuppliersUOM'] . "',
				'" . $_POST['ConversionFactor'] . "',
				'" . $_POST['SupplierDescription'] . "',
				'" . $_POST['SupplierCode'] . "',
				'" . $_POST['LeadTime'] . "',
                            '" . $_POST['MinOrderQty'] . "',
				'" . $_POST['Preferred'] . "')";
        $ErrMsg = _('The supplier purchasing details could not be added to the database because');
        $DbgMsg = _('The SQL that failed was');
        $AddResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        prnMsg(_('This supplier purchasing data has been added to the database'), 'success');
    }
    if ($InputError == 0 AND isset($_POST['UpdateRecord'])) {
        $sql = "UPDATE purchdata SET
			    price='" . $_POST['Price'] . "',
			    effectivefrom='" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
				suppliersuom='" . $_POST['SuppliersUOM'] . "',
				conversionfactor='" . $_POST['ConversionFactor'] . "',
				supplierdescription='" . $_POST['SupplierDescription'] . "',
				suppliers_partno='" . $_POST['SupplierCode'] . "',
				leadtime='" . $_POST['LeadTime'] . "',
                           MinOrderQty='" . $_POST['MinOrderQty'] . "',
				preferred='" . $_POST['Preferred'] . "'
		WHERE purchdata.stockid='".$StockID."'
		AND purchdata.supplierno='".$SupplierID."'
		AND purchdata.effectivefrom='" . $_POST['WasEffectiveFrom'] . "'";
        $ErrMsg = _('The supplier purchasing details could not be update because');
        $DbgMsg = _('The SQL that failed was');
        $UpdResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        prnMsg(_('Supplier purchasing data has been updated'), 'success');
    }
    if ($InputError == 0 AND (isset($_POST['UpdateRecord']) OR isset($_POST['AddRecord']))) {
        /*update or insert took place and need to clear the form  */
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
    }
}

if (isset($_GET['Delete'])) {
    $sql = "DELETE FROM purchdata
   				WHERE purchdata.supplierno='".$SupplierID."'
   				AND purchdata.stockid='".$StockID."'
   				AND purchdata.effectivefrom='" . $_GET['EffectiveFrom'] . "'";
    $ErrMsg = _('The supplier purchasing details could not be deleted because');
    $DelResult = DB_query($sql, $db, $ErrMsg);
    prnMsg(_('This purchasing data record has been successfully deleted'), 'success');
    unset($SupplierID);
}

if (!isset($_GET['Edit'])) {
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title . ' ' . _('For Stock Code') . ' - ' . $StockID . '<br>';
    $sql = "SELECT  purchdata.supplierno,
					suppliers.suppname,
					purchdata.price,
					suppliers.currcode,
					purchdata.effectivefrom,
					unitsofmeasure.unitname,
					purchdata.supplierdescription,
					purchdata.leadtime,
					purchdata.suppliers_partno,
                                  purchdata.MinOrderQty,
					purchdata.preferred
			FROM purchdata INNER JOIN suppliers
				ON purchdata.supplierno=suppliers.supplierid
			LEFT JOIN unitsofmeasure
				ON purchdata.suppliersuom=unitsofmeasure.unitid
			WHERE purchdata.stockid = '" . $StockID . "'
			ORDER BY purchdata.effectivefrom DESC";
    $ErrMsg = _('The supplier purchasing details for the selected part could not be retrieved because');
    $PurchDataResult = DB_query($sql, $db, $ErrMsg);
    if (DB_num_rows($PurchDataResult) == 0 and $StockID != '') {
//        prnMsg(_('There is no purchasing data set up for the part selected'), 'info');
		$NoPurchasingData=1;
    } else if ($StockID != '') {
        echo '<table cellpadding=2 class=selection>';
        $TableHeader = '<tr><th>' . _('Supplier') . '</th>
						<th>' . _('Price') . '</th>
						<th>' . _('Currency') . '</th>
						<th>' . _('Effective From') . '</th>
						<th>' . _('Supplier Unit') . '</th>
                                         <th>' . _('Min Order Qty') . '</th>
						<th>' . _('Lead Time') . '</th>
						<th>' . _('Preferred') . '</th>
					</tr>';
        echo $TableHeader;
        $CountPreferreds = 0;
        $k = 0; //row colour counter
        while ($myrow = DB_fetch_array($PurchDataResult)) {
            if ($myrow['preferred'] == 1) {
                echo '<tr class="EvenTableRows">';
            } elseif ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k++;
            }
            if ($myrow['preferred'] == 1) {
                $DisplayPreferred = _('Yes');
                $CountPreferreds++;
            } else {
                $DisplayPreferred = _('No');
            }
            printf("<td>%s</td>
			<td class=number>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
                     <td>%s</td>
			<td class=number>%s " . _('days') . "</td>
			<td>%s</td>
			<td><a href='%s?%s&StockID=%s&SupplierID=%s&Edit=1&EffectiveFrom=%s'>" . _('Edit') . "</a></td>
			<td><a href='%s?%s&StockID=%s&SupplierID=%s&Delete=1&EffectiveFrom=%s' onclick=\"return confirm('" . _('Are you sure you wish to delete this suppliers price?') . "');\">" . _('Delete') . "</a></td>
			</tr>", $myrow['suppname'], number_format($myrow['price'], 3), $myrow['currcode'], ConvertSQLDate($myrow['effectivefrom']), $myrow['unitname'],$myrow['MinOrderQty'], $myrow['leadtime'], $DisplayPreferred, $_SERVER['PHP_SELF'], SID, $StockID, $myrow['supplierno'], $myrow['effectivefrom'], $_SERVER['PHP_SELF'], SID, $StockID, $myrow['supplierno'], $myrow['effectivefrom']);
        } //end of while loop
        echo '</table><br/>';
        if ($CountPreferreds > 1) {
            prnMsg(_('There are now') . ' ' . $CountPreferreds . ' ' . _('preferred suppliers set up for') . ' ' . $StockID . ' ' . _('you should edit the supplier purchasing data to make only one supplier the preferred supplier'), 'warn');
        } elseif ($CountPreferreds == 0) {
            prnMsg(_('There are NO preferred suppliers set up for') . ' ' . $StockID . ' ' . _('you should make one supplier only the preferred supplier'), 'warn');
        }
    } // end of there are purchsing data rows to show
    echo '<br/>';
} /* Only show the existing purchasing data records if one is not being edited */

if (isset($SupplierID) AND $SupplierID != '' AND !isset($_POST['SearchSupplier'])) { /*NOT EDITING AN EXISTING BUT SUPPLIER selected OR ENTERED*/
    $sql = "SELECT suppliers.suppname, suppliers.currcode FROM suppliers WHERE supplierid='".$SupplierID."'";
    $ErrMsg = _('The supplier details for the selected supplier could not be retrieved because');
    $DbgMsg = _('The SQL that failed was');
    $SuppSelResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    if (DB_num_rows($SuppSelResult) == 1) {
        $myrow = DB_fetch_array($SuppSelResult);
        $SuppName = $myrow['suppname'];
        $CurrCode = $myrow['currcode'];
    } else {
        prnMsg(_('The supplier code') . ' ' . $SupplierID . ' ' . _('is not an existing supplier in the database') . '. ' . _('You must enter an alternative supplier code or select a supplier using the search facility below'), 'error');
        unset($SupplierID);
    }
} else {
	if ($NoPurchasingData=0) {
		echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title . ' ' . _('For Stock Code') . ' - ' . $StockID . '<br>';
	}
    if (!isset($_POST['SearchSupplier'])) {
        echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post><table cellpadding=3 colspan=4 class=selection><tr>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<input type="hidden" name="StockID" value="' . $StockID . '">';
        echo '<td>' . _('Text in the Supplier') . ' <b>' . _('NAME') . '</b>:</font></td>';
        echo '<td><input type="Text" name="Keywords" size=20 maxlength=25></td>';
        echo '<td><font size=3><b>' . _('OR') . '</b></font></td>';
        echo '<td>' . _('Text in Supplier') . ' <b>' . _('CODE') . '</b>:</font></td>';
        echo '<td><input type="Text" name="SupplierCode" size=15 maxlength=18></td>';
        echo '</tr></table><br />';
        echo '<div class="centre"><input type=submit name="SearchSupplier" VALUE="' . _('Find Suppliers Now') . '"></div></form>';
        include ('includes/footer.inc');
        exit;
    };
}

if (isset($_GET['Edit'])) {
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title . ' ' . _('For Stock Code') . ' - ' . $StockID . '<br>';
}

if (isset($_POST['SearchSupplier'])) {
    if (isset($_POST['Keywords']) AND isset($_POST['SupplierCode'])) {
        prnMsg( _('Supplier Name keywords have been used in preference to the Supplier Code extract entered') . '.', 'info' );
        echo '<br>';
    }
    if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
        $_POST['Keywords'] = ' ';
    }
    if (strlen($_POST['Keywords']) > 0) {
        //insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					suppliers.currcode,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3
					FROM suppliers WHERE suppliers.suppname LIKE " ."'".$SearchString."'";
    } elseif (strlen($_POST['SupplierCode']) > 0) {
        $SQL = "SELECT suppliers.supplierid,
				suppliers.suppname,
				suppliers.currcode,
				suppliers.address1,
				suppliers.address2,
				suppliers.address3
			FROM suppliers
			WHERE suppliers.supplierid LIKE '%" . $_POST['SupplierCode'] . "%'";
    } //one of keywords or SupplierCode was more than a zero length string
    $ErrMsg = _('The suppliers matching the criteria entered could not be retrieved because');
    $DbgMsg = _('The SQL to retrieve supplier details that failed was');
    $SuppliersResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
} //end of if search

if (isset($SuppliersResult)) {
	if (isset($StockID)) {
        $result = DB_query("SELECT stockmaster.description,
								stockmaster.units,
								stockmaster.mbflag
						FROM stockmaster
						WHERE stockmaster.stockid='".$StockID."'", $db);
        $myrow = DB_fetch_row($result);
        $stockuom = $myrow[1];
        if (DB_num_rows($result) == 1) {
            if ($myrow[2] == 'D' OR $myrow[2] == 'A' OR $myrow[2] == 'K') {
                prnMsg($StockID . ' - ' . $myrow[0] . '<p> ' . _('The item selected is a dummy part or an assembly or kit set part') . ' - ' . _('it is not purchased') . '. ' . _('Entry of purchasing information is therefore inappropriate'), 'warn');
                include ('includes/footer.inc');
                exit;
            } else {
 //               echo '<br><font color=BLUE size=3><b>' . $StockID . ' - ' . $myrow[0] . ' </b>  (' . _('In Units of') . ' ' . $myrow[1] . ' )</font>';
            }
        } else {
            prnMsg(_('Stock Item') . ' - ' . $StockID . ' ' . _('is not defined in the database'), 'warn');
        }
    } else {
        $StockID = '';
        $stockuom = 'each';
    }
    echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post><table cellpadding=2 colspan=7 class=selection>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    $TableHeader = '<tr><th>' . _('Code') . '</th>
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
       printf("<td><font size=1><input type=submit name='SupplierID' VALUE='%s'</font></td>
				<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
				<td><font size=1>%s</font></td>
			</tr>", $myrow['supplierid'], $myrow['suppname'], $myrow['currcode'], $myrow['address1'], $myrow['address2'], $myrow['address3']);
        echo '<input type=hidden name=StockID value="' . $StockID . '">';
        echo '<input type=hidden name=stockuom value="' . $stockuom . '">';

    }
    //end of while loop
    echo '</table><br/></form>';
}
//end if results to show

/*Show the input form for new supplier purchasing details */
if (!isset($SuppliersResult)) {
    if (isset($_GET['Edit'])) {
        $sql = "SELECT purchdata.supplierno,
				suppliers.suppname,
				purchdata.price,
				purchdata.effectivefrom,
				suppliers.currcode,
				purchdata.suppliersuom,
				purchdata.supplierdescription,
				purchdata.leadtime,
				purchdata.conversionfactor,
				purchdata.suppliers_partno,
                           purchdata.MinOrderQty,
				purchdata.preferred,
				stockmaster.units
		FROM purchdata INNER JOIN suppliers
			ON purchdata.supplierno=suppliers.supplierid
		INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
		WHERE purchdata.supplierno='".$SupplierID."'
		AND purchdata.stockid='".$StockID."'
		AND purchdata.effectivefrom='" . $_GET['EffectiveFrom'] . "'";
        $ErrMsg = _('The supplier purchasing details for the selected supplier and item could not be retrieved because');
        $EditResult = DB_query($sql, $db, $ErrMsg);
        $myrow = DB_fetch_array($EditResult);
        $SuppName = $myrow['suppname'];
        $_POST['Price'] = $myrow['price'];
        $_POST['EffectiveFrom'] = ConvertSQLDate($myrow['effectivefrom']);
        $CurrCode = $myrow['currcode'];
        $_POST['SuppliersUOM'] = $myrow['suppliersuom'];
        $_POST['SupplierDescription'] = $myrow['supplierdescription'];
        $_POST['LeadTime'] = $myrow['leadtime'];
        $_POST['ConversionFactor'] = $myrow['conversionfactor'];
        $_POST['Preferred'] = $myrow['preferred'];
        $_POST['MinOrderQty'] = $myrow['MinOrderQty'];
        $_POST['SupplierCode'] = $myrow['suppliers_partno'];
		$stockuom=$myrow['units'];
    }
    echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post><table class=selection>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    if (!isset($SupplierID)) {
        $SupplierID = '';
    }
	if (isset($_GET['Edit'])) {
        echo '<tr><td>' . _('Supplier Name') . ':</td>
    	<td><input type=hidden name="SupplierID" VALUE="' . $SupplierID . '">' . $SupplierID . ' - ' . $SuppName . '<input type=hidden name="WasEffectiveFrom" VALUE="' . $myrow['effectivefrom'] . '"></td></tr>';
    } else {
        echo '<tr><td>' . _('Supplier Name') . ':</td>
    	<input type=hidden name="SupplierID" maxlength=10 size=11 VALUE="' . $SupplierID . '">';
		if ($SupplierID!='') {
			echo '<td>'.$SuppName;
		}
        if (!isset($SuppName) OR $SuppName = "") {
            echo '<font size=1>' . '(' . _('A search facility is available below if necessary') . ')';
        } else {
            echo '<td>'.$SuppName;
        }
        echo '</td></tr>';
    }
   	echo '<td><input type=hidden name="StockID" maxlength=10 size=11 VALUE="' . $StockID . '">';
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
        $_POST['MinOrderQty'] = '';
    }
    echo '<tr><td>' . _('Currency') . ':</td>
	<td><input type=hidden name="CurrCode" . VALUE="' . $CurrCode . '">' . $CurrCode . '</td></tr>';
    echo '<tr><td>' . _('Price') . ' (' . _('in Supplier Currency') . '):</td>
	<td><input type=text class=number name="Price" maxlength=12 size=12 VALUE=' . number_format($_POST['Price'], DecimalPlaces($CurrCode, $db),'.','') . '></td></tr>';
    echo '<tr><td>' . _('Date Updated') . ':</td>
	<td><input type=text class=date alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveFrom" maxlength=10 size=11 VALUE="' . $_POST['EffectiveFrom'] . '"></td></tr>';
    echo '<tr><td>' . _('Our Unit of Measure') . ':</td>';
	if (isset($SupplierID)) {
		echo '<td>' . $stockuom . '</td></tr>';
	}
    echo '<tr><td>' . _('Suppliers Unit of Measure') . ':</td>';
    echo '<td><select name="SuppliersUOM">';
    $sql = 'SELECT * FROM unitsofmeasure';
    $result = DB_query($sql, $db);
    while ($myrow = DB_fetch_array($result)) {
        if ($_POST['SuppliersUOM'] == $myrow['unitid']) {
            echo '<option selected value="' . $myrow['unitid'] . '">' . $myrow['unitname'] . '</option>';
        } else {
            echo '<option value="' . $myrow['unitid'] . '">' . $myrow['unitname'] . '</option>';
        }
    }
    echo '</td></tr>';
    if (!isset($_POST['ConversionFactor']) OR $_POST['ConversionFactor'] == "") {
        $_POST['ConversionFactor'] = 1;
    }
    echo '<tr><td>' . _('Conversion Factor (to our UOM)') . ':</td>
	<td><input type=text class=number name="ConversionFactor" maxlength=12 size=12 VALUE=' . $_POST['ConversionFactor'] . '></td></tr>';
    echo '<tr><td>' . _('Supplier Stock Code') . ':</td>
	<td><input type=text name="SupplierCode" maxlength=15 size=15 VALUE="' . $_POST['SupplierCode'] . '"></td></tr>';
    echo '<tr><td>' . _('MinOrderQty') . ':</td>
	<td><input type=text name="MinOrderQty" maxlength=15 size=15 VALUE="' . $_POST['MinOrderQty'] . '"></td></tr>';
    echo '<tr><td>' . _('Supplier Stock Description') . ':</td>
	<td><input type=text name="SupplierDescription" maxlength=50 size=51 VALUE="' . $_POST['SupplierDescription'] . '"></td></tr>';
    if (!isset($_POST['LeadTime']) OR $_POST['LeadTime'] == "") {
        $_POST['LeadTime'] = 1;
    }
    echo '<tr><td>' . _('Lead Time') . ' (' . _('in days from date of order') . '):</td>
	<td><input type=text class=number name="LeadTime" maxlength=4 size=5 VALUE=' . $_POST['LeadTime'] . '></td></tr>';
    echo '<tr><td>' . _('Preferred Supplier') . ':</td>
	<td><select name="Preferred">';
    if ($_POST['Preferred'] == 1) {
        echo '<option selected VALUE=1>' . _('Yes');
        echo '<option VALUE=0>' . _('No');
    } else {
        echo '<option VALUE=1>' . _('Yes');
        echo '<option selected VALUE=0>' . _('No');
    }
    echo '</select></td></tr></table><br><div class="centre">';
    if (isset($_GET['Edit'])) {
        echo '<input type=submit name="UpdateRecord" VALUE="' . _('Update') . '">';
    } else {
        echo '<input type=submit name="AddRecord" VALUE="' . _('Add') . '">';
    }
    echo '</div>';
    echo '<div class="centre">';
    if (isset($StockLocation) and isset($StockID) AND strlen($StockID) != 0) {
        echo '<br><a href="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Status') . '</a>';
        echo '<br><a href="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockID . '&StockLocation=' . $StockLocation . '">' . _('Show Stock Movements') . '</a>';
        echo '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '&StockLocation=' . $StockLocation . '">' . _('Search Outstanding Sales Orders') . '</a>';
        echo '<br><a href="' . $rootpath . '/SelectCompletedOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';
    }
    echo '</form></div>';
}

include ('includes/footer.inc');
?>