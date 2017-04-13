<?php
/* $Id: CustItem.php 1 2014-04-23 05:10:46Z agaluski $*/

include ('includes/session.php');

$Title = _('Customer Item Data');

include ('includes/header.php');

if (isset($_GET['DebtorNo'])) {
    $DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
} elseif (isset($_POST['DebtorNo'])) {
    $DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
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

if (isset($_POST['StockUOM'])) {
	$StockUOM=$_POST['StockUOM'];
}

$NoCustItemData=0;

echo '<a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a><br />';

if (isset($_POST['cust_description'])) {
    $_POST['cust_description'] = trim($_POST['cust_description']);
}
if (isset($_POST['cust_part'])) {
    $_POST['cust_part'] = trim($_POST['cust_part']);
}

if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($DebtorNo)) { /*Validate Inputs */
	$InputError = 0; /*Start assuming the best */

	if ($StockID == '' OR !isset($StockID)) {
		$InputError = 1;
		prnMsg(_('There is no stock item set up enter the stock code or select a stock item using the search page'), 'error');
	}

	if (!is_numeric(filter_number_format($_POST['ConversionFactor']))) {
		$InputError = 1;
		unset($_POST['ConversionFactor']);
		prnMsg(_('The conversion factor entered was not numeric') . ' (' . _('a number is expected') . '). ' . _('The conversion factor is the number which the price must be divided by to get the unit price in our unit of measure') . '. <br />' . _('E.g.') . ' ' . _('The customer sells an item by the tonne and we hold stock by the kg') . '. ' . _('The debtorsmaster.price must be divided by 1000 to get to our cost per kg') . '. ' . _('The conversion factor to enter is 1000') . '. <br /><br />' . _('No changes will be made to the database'), 'error');
	}

    if ($InputError == 0 AND isset($_POST['AddRecord'])) {
        $sql = "INSERT INTO custitem (debtorno,
										stockid,
										customersuom,
										conversionfactor,
										cust_description,
										cust_part)
						VALUES ('" . $DebtorNo . "',
							'" . $StockID . "',
							'" . $_POST['customersUOM'] . "',
							'" . filter_number_format($_POST['ConversionFactor']) . "',
							'" . $_POST['cust_description'] . "',
							'" . $_POST['cust_part'] . "')";
        $ErrMsg = _('The customer Item details could not be added to the database because');
        $DbgMsg = _('The SQL that failed was');
        $AddResult = DB_query($sql, $ErrMsg, $DbgMsg);
        prnMsg(_('This customer data has been added to the database'), 'success');
		unset($debtorsmasterResult);
    }
    if ($InputError == 0 AND isset($_POST['UpdateRecord'])) {
        $sql = "UPDATE custitem SET customersuom='" . $_POST['customersUOM'] . "',
										conversionfactor='" . filter_number_format($_POST['ConversionFactor']) . "',
										cust_description='" . $_POST['cust_description'] . "',
										custitem.cust_part='" . $_POST['cust_part'] . "'
							WHERE custitem.stockid='" . $StockID . "'
							AND custitem.debtorno='" . $DebtorNo . "'";
        $ErrMsg = _('The customer details could not be updated because');
        $DbgMsg = _('The SQL that failed was');
        $UpdResult = DB_query($sql, $ErrMsg, $DbgMsg);
        prnMsg(_('customer data has been updated'), 'success');
        unset($Edit);
		unset($debtorsmasterResult);
		unset($DebtorNo);
    }

    if ($InputError == 0 AND isset($_POST['AddRecord'])) {
	/*  insert took place and need to clear the form  */
        unset($DebtorNo);
        unset($_POST['customersUOM']);
        unset($_POST['ConversionFactor']);
        unset($_POST['cust_description']);
        unset($_POST['cust_part']);

    }
}

if (isset($_GET['Delete'])) {
    $sql = "DELETE FROM custitem
	   				WHERE custitem.debtorno='" . $DebtorNo . "'
	   				AND custitem.stockid='" . $StockID . "'";
    $ErrMsg = _('The customer details could not be deleted because');
    $DelResult = DB_query($sql, $ErrMsg);
    prnMsg(_('This customer data record has been successfully deleted'), 'success');
    unset($DebtorNo);
}


if ($Edit == false) {

	$ItemResult = DB_query("SELECT description FROM stockmaster WHERE stockid='" . $StockID . "'");
	$DescriptionRow = DB_fetch_array($ItemResult);
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . ' ' . _('For Stock Code') . ' - ' . $StockID . ' - ' . $DescriptionRow['description'] . '</p><br />';

    $sql = "SELECT custitem.debtorno,
				debtorsmaster.name,
				debtorsmaster.currcode,
				custitem.customersUOM,
				custitem.conversionfactor,
				custitem.cust_description,
				custitem.cust_part,
				currencies.decimalplaces AS currdecimalplaces
			FROM custitem INNER JOIN debtorsmaster
				ON custitem.debtorno=debtorsmaster.DebtorNo
			INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
			WHERE custitem.stockid = '" . $StockID . "'";
    $ErrMsg = _('The customer details for the selected part could not be retrieved because');
    $custitemResult = DB_query($sql, $ErrMsg);
    if (DB_num_rows($custitemResult) == 0 and $StockID != '') {
		prnMsg(_('There is no customer data set up for the part selected'), 'info');
		$NoCustItemData=1;
    } else if ($StockID != '') {

        echo '<table cellpadding="2" class="selection">';
        $TableHeader = '<tr>
							<th class="ascending">' . _('Customer') . '</th>
							<th>' . _('Customer Unit') . '</th>
							<th>' . _('Conversion Factor') . '</th>
							<th class="ascending">' . _('Customer Item') . '</th>
							<th class="ascending">' . _('Customer Description') . '</th>

						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($custitemResult)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s?StockID=%s&amp;DebtorNo=%s&amp;Edit=1">' . _('Edit') . '</a></td>
					<td><a href="%s?StockID=%s&amp;DebtorNo=%s&amp;Delete=1" onclick=\'return confirm("' . _('Are you sure you wish to delete this customer data?') . '");\'>' . _('Delete') . '</a></td>
					</tr>',
					$myrow['name'],
					$myrow['customersUOM'],
					locale_number_format($myrow['conversionfactor'],'Variable'),
					$myrow['cust_part'],
					$myrow['cust_description'],
					htmlspecialchars($_SERVER['PHP_SELF']),
					$StockID,
					$myrow['debtorno'],
					htmlspecialchars($_SERVER['PHP_SELF']),
					$StockID,
					$myrow['debtorno']);
        } //end of while loop
        echo '</table><br/>';
    } // end of there are rows to show
    echo '<br/>';
} /* Only show the existing records if one is not being edited */

if (isset($DebtorNo) AND $DebtorNo != '' AND !isset($_POST['Searchcustomer'])) {
	/*NOT EDITING AN EXISTING BUT customer selected OR ENTERED*/

    $sql = "SELECT debtorsmaster.name,
					debtorsmaster.currcode,
					currencies.decimalplaces AS currdecimalplaces
			FROM debtorsmaster
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE DebtorNo='".$DebtorNo."'";
    $ErrMsg = _('The customer details for the selected customer could not be retrieved because');
    $DbgMsg = _('The SQL that failed was');
    $SuppSelResult = DB_query($sql, $ErrMsg, $DbgMsg);
    if (DB_num_rows($SuppSelResult) == 1) {
        $myrow = DB_fetch_array($SuppSelResult);
        $name = $myrow['name'];
        $CurrCode = $myrow['currcode'];
        $CurrDecimalPlaces = $myrow['currdecimalplaces'];
    } else {
        prnMsg(_('The customer code') . ' ' . $DebtorNo . ' ' . _('is not an existing customer in the database') . '. ' . _('You must enter an alternative customer code or select a customer using the search facility below'), 'error');
        unset($DebtorNo);
    }
} else {
	if ($NoCustItemData==0) {
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . ' ' . _('For Stock Code') . ' - ' . $StockID . '</p><br />';
	}
    if (!isset($_POST['Searchcustomer'])) {
        echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
				<table cellpadding="3" colspan="4" class="selection">
				<tr>
					<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
					<input type="hidden" name="StockID" value="' . $StockID . '" />
					<td>' . _('Text in the customer') . ' <b>' . _('NAME') . '</b>:</td>
					<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
					<td><b>' . _('OR') . '</b></td>
					<td>' . _('Text in customer') . ' <b>' . _('CODE') . '</b>:</td>
					<td><input type="text" name="cust_no" data-type="no-illegal-chars" size="20" maxlength="50" /></td>
				</tr>
				</table>
				<br />
				<div class="centre">
					<input type="submit" name="Searchcustomer" value="' . _('Find Customers Now') . '" />
				</div>
			</form>';
        include ('includes/footer.php');
        exit;
    };
}

if ($Edit == true) {
	$ItemResult = DB_query("SELECT description FROM stockmaster WHERE stockid='" . $StockID . "'");
	$DescriptionRow = DB_fetch_array($ItemResult);
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . ' ' . _('For Stock Code') . ' - ' . $StockID . ' - ' . $DescriptionRow['description'] . '</p><br />';
}

if (isset($_POST['Searchcustomer'])) {
    if (isset($_POST['Keywords']) AND isset($_POST['cust_no'])) {
        prnMsg( _('Customer Name keywords have been used in preference to the customer Code extract entered') . '.', 'info' );
        echo '<br />';
    }
    if ($_POST['Keywords'] == '' AND $_POST['cust_no'] == '') {
        $_POST['Keywords'] = ' ';
    }
    if (mb_strlen($_POST['Keywords']) > 0) {
        //insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT debtorsmaster.DebtorNo,
						debtorsmaster.name,
						debtorsmaster.currcode,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3
				FROM debtorsmaster
				WHERE debtorsmaster.name " . LIKE  . " '".$SearchString."'";

    } elseif (mb_strlen($_POST['cust_no']) > 0) {
        $SQL = "SELECT debtorsmaster.DebtorNo,
						debtorsmaster.name,
						debtorsmaster.currcode,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3
				FROM debtorsmaster
				WHERE debtorsmaster.DebtorNo " . LIKE . " '%" . $_POST['cust_no'] . "%'";

    } //one of keywords or cust_part was more than a zero length string
    $ErrMsg = _('The cuswtomer matching the criteria entered could not be retrieved because');
    $DbgMsg = _('The SQL to retrieve customer details that failed was');
    $debtorsmasterResult = DB_query($SQL, $ErrMsg, $DbgMsg);
} //end of if search
if (isset($debtorsmasterResult) AND DB_num_rows($debtorsmasterResult) > 0) {
	if (isset($StockID)) {
        $result = DB_query("SELECT stockmaster.description,
								stockmaster.units,
								stockmaster.mbflag
						FROM stockmaster
						WHERE stockmaster.stockid='".$StockID."'");
		$myrow = DB_fetch_row($result);
		$StockUOM = $myrow[1];
		if (DB_num_rows($result) <> 1) {
			prnMsg(_('Stock Item') . ' - ' . $StockID . ' ' . _('is not defined in the database'), 'warn');
		}
	} else {
		$StockID = '';
		$StockUOM = 'each';
	}
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">
			<table cellpadding="2" colspan="7" class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    $TableHeader = '<tr>
						<th class="ascending">' . _('Code') . '</th>
	                	<th class="ascending">' . _('Customer Name') . '</th>
						<th class="ascending">' . _('Currency') . '</th>
						<th class="ascending">' . _('Address 1') . '</th>
						<th class="ascending">' . _('Address 2') . '</th>
						<th class="ascending">' . _('Address 3') . '</th>
					</tr>';
    echo $TableHeader;
	$k = 0;
    while ($myrow = DB_fetch_array($debtorsmasterResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
       printf('<td><input type="submit" name="DebtorNo" value="%s" /></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>',
				$myrow['DebtorNo'],
				$myrow['name'],
				$myrow['currcode'],
				$myrow['address1'],
				$myrow['address2'],
				$myrow['address3']);

        echo '<input type="hidden" name="StockID" value="' . $StockID . '" />';
        echo '<input type="hidden" name="StockUOM" value="' . $StockUOM . '" />';

    }
    //end of while loop
    echo '</table>
			<br/>
			</form>';
}
//end if results to show

/*Show the input form for new customer details */
if (!isset($debtorsmasterResult)) {
	if ($Edit == true OR isset($_GET['Copy'])) {

		 $sql = "SELECT custitem.debtorno,
						debtorsmaster.name,
						debtorsmaster.currcode,
						custitem.customersUOM,
						custitem.cust_description,
						custitem.conversionfactor,
						custitem.cust_part,
						stockmaster.units,
						currencies.decimalplaces AS currdecimalplaces
				FROM custitem INNER JOIN debtorsmaster
					ON custitem.debtorno=debtorsmaster.DebtorNo
				INNER JOIN stockmaster
					ON custitem.stockid=stockmaster.stockid
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
				WHERE custitem.debtorno='" . $DebtorNo . "'
				AND custitem.stockid='" . $StockID . "'";

		$ErrMsg = _('The customer purchasing details for the selected customer and item could not be retrieved because');
		$EditResult = DB_query($sql, $ErrMsg);
		$myrow = DB_fetch_array($EditResult);
		$name = $myrow['name'];

		$CurrCode = $myrow['currcode'];
		$CurrDecimalPlaces = $myrow['currdecimalplaces'];
		$_POST['customersUOM'] = $myrow['customersUOM'];
		$_POST['cust_description'] = $myrow['cust_description'];
		$_POST['ConversionFactor'] = locale_number_format($myrow['conversionfactor'],'Variable');
		$_POST['cust_part'] = $myrow['cust_part'];
		$StockUOM=$myrow['units'];
    }
    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">
		<table class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    if (!isset($DebtorNo)) {
        $DebtorNo = '';
    }
	if ($Edit == true) {
        echo '<tr>
				<td>' . _('Customer Name') . ':</td>
				<td><input type="hidden" name="DebtorNo" value="' . $DebtorNo . '" />' . $DebtorNo . ' - ' . $name . '</td>
			</tr>';
    } else {
        echo '<tr>
				<td>' . _('Customer Name') . ':</td>
				<input type="hidden" name="DebtorNo" maxlength="10" size="11" value="' . $DebtorNo . '" />';

		if ($DebtorNo!='') {
			echo '<td>' . $name;
		}
		if (!isset($name) OR $name = '') {
			echo '(' . _('A search facility is available below if necessary') . ')';
		} else {
			echo '<td>' . $name;
		}
		echo '</td></tr>';
	}
	echo '<td><input type="hidden" name="StockID" maxlength="10" size="11" value="' . $StockID . '" />';
	if (!isset($CurrCode)) {
		$CurrCode = '';
	}

	if (!isset($_POST['customersUOM'])) {
		$_POST['customersUOM'] = '';
	}
	if (!isset($_POST['cust_description'])) {
		$_POST['cust_description'] = '';
	}
	if (!isset($_POST['cust_part'])) {
		$_POST['cust_part'] = '';
	}
	echo '<tr>
			<td>' . _('Currency') . ':</td>
			<td><input type="hidden" name="CurrCode" . value="' . $CurrCode . '" />' . $CurrCode . '</td>
		</tr>
		<tr>
			<td>' . _('Our Unit of Measure') . ':</td>';

	if (isset($DebtorNo)) {
		echo '<td>' . $StockUOM . '</td></tr>';
	}
	echo '<tr>
			<td>' . _('Customer Unit of Measure') . ':</td>
			<td><input type="text" name="customersUOM" size="20" maxlength="20" value ="' . $_POST['customersUOM'] . '"/></td>
		</tr>';

	if (!isset($_POST['ConversionFactor']) OR $_POST['ConversionFactor'] == '') {
		$_POST['ConversionFactor'] = 1;
	}

	echo '<tr>
			<td>' . _('Conversion Factor (to our UOM)') . ':</td>
			<td><input type="text" class="number" name="ConversionFactor" maxlength="12" size="12" value="' . $_POST['ConversionFactor'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Customer Stock Code') . ':</td>
			<td><input type="text" name="cust_part" maxlength="20" size="20" value="' . $_POST['cust_part'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Customer Stock Description') . ':</td>
			<td><input type="text" name="cust_description" maxlength="30" size="30" value="' . $_POST['cust_description'] . '" /></td>
		</tr>';


	echo '</table>
		<br />
		<div class="centre">';

	if ($Edit == true) {
		echo '<input type="submit" name="UpdateRecord" value="' . _('Update') . '" />';
		echo '<input type="hidden" name="Edit" value="1" />';
	} else {
		echo '<input type="submit" name="AddRecord" value="' . _('Add') . '" />';
	}

	echo '</div>
		<div class="centre">';

	if (isset($StockLocation) AND isset($StockID) AND mb_strlen($StockID) != 0) {
		echo '<br /><a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') . '</a>';
		echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '&StockLocation=' . $StockLocation . '">' . _('Show Stock Movements') . '</a>';
		echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '&StockLocation=' . $StockLocation . '">' . _('Search Outstanding Sales Orders') . '</a>';
		echo '<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';
	}
	echo '</form></div>';
}

include ('includes/footer.php');
?>
