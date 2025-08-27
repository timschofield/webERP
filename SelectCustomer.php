<?php

/* Selection of customer - from where all customer related maintenance, transactions and inquiries start */

require(__DIR__ . '/includes/session.php');

$Title = __('Search Customers');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'SelectCustomer';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', __('Customer'), '" /> ', __('Customers'), '
	</p>';

if (isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
} // isset($_GET['Select'])
if (!isset($_SESSION['CustomerID'])) { // initialise if not already done
	$_SESSION['CustomerID'] = '';
} // !isset($_SESSION['CustomerID'])
if (isset($_GET['Area'])) {
	$_POST['Area'] = $_GET['Area'];
	$_POST['Search'] = 'Search';
	$_POST['Keywords'] = '';
	$_POST['CustCode'] = '';
	$_POST['CustPhone'] = '';
	$_POST['CustAdd'] = '';
	$_POST['CustType'] = '';
} // isset($_GET['Area'])
if (!isset($_SESSION['CustomerType'])) { // initialise if not already done
	$_SESSION['CustomerType'] = '';
} // !isset($_SESSION['CustomerType'])
if (isset($_POST['JustSelectedACustomer'])) {
	if (isset($_POST['SubmitCustomerSelection'])) {
		foreach ($_POST['SubmitCustomerSelection'] as $CustomerID => $BranchCode) $_SESSION['CustomerID'] = $CustomerID;
		$_SESSION['BranchCode'] = $BranchCode;
	} elseif (!isset($_POST['Search'])){
		prnMsg(__('Unable to identify the selected customer'), 'error');
	}
}

$Msg = '';

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
} // isset($_POST['Go1']) or isset($_POST['Go2'])
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	} // $_POST['PageOffset'] == 0

}

if (isset($_POST['Search']) or isset($_POST['CSV']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	unset($_POST['JustSelectedACustomer']);
	if (isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	} // isset($_POST['Search'])
	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				custbranch.branchcode,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno,
				custbranch.email
			FROM debtorsmaster
			LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			INNER JOIN debtortype
				ON debtorsmaster.typeid = debtortype.typeid";
	$SearchKeywords = '';
	if (!(($_POST['Keywords'] == '') and ($_POST['CustCode'] == '') and ($_POST['CustPhone'] == '') and ($_POST['CustType'] == 'ALL') and ($_POST['Area'] == 'ALL') and ($_POST['CustAdd'] == ''))) {
		// criteria is set, proceed with SQL refinement
		$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
		$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
		$_POST['CustPhone'] = trim($_POST['CustPhone']);
		$_POST['CustAdd'] = trim($_POST['CustAdd']);
		$SQL .= " WHERE debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
						AND debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
						AND (custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%' OR custbranch.phoneno IS NULL)
						AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address3 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address4 " . LIKE . " '%" . $_POST['CustAdd'] . "%')"; // If there is no custbranch set, the phoneno in custbranch will be null, so we add IS NULL condition otherwise those debtors without custbranches setting will be no searchable and it will make a inconsistence with customer receipt interface.
		if (mb_strlen($_POST['CustType']) > 0 and $_POST['CustType'] != 'ALL') {
			$SQL.= " AND debtortype.typename = '" . $_POST['CustType'] . "'";
		} // mb_strlen($_POST['CustType']) > 0 and $_POST['CustType'] != 'ALL'
		if (mb_strlen($_POST['Area']) > 0 and $_POST['Area'] != 'ALL') {
			$SQL.= " AND custbranch.area = '" . $_POST['Area'] . "'";
		} // mb_strlen($_POST['Area']) > 0 and $_POST['Area'] != 'ALL'

	} // one of keywords or custcode or custphone was more than a zero length string
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	} // $_SESSION['SalesmanLogin'] != ''
	$SQL.= " ORDER BY debtorsmaster.name";
	$ErrMsg = __('The searched customer records requested cannot be retrieved because');

	$SearchResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($SearchResult) == 1) {
		$MyRow = DB_fetch_array($SearchResult);
		$_SESSION['CustomerID'] = $MyRow['debtorno'];
		$_SESSION['BranchCode'] = $MyRow['branchcode'];
		unset($SearchResult);
		unset($_POST['Search']);
	} elseif (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No customer records contain the selected text') . ' - ' . __('please alter your search criteria and try again'), 'info');
	} // DB_num_rows($Result) == 0

} // end of if search
if ($_SESSION['CustomerID'] != '' and !isset($_POST['Search']) and !isset($_POST['CSV'])) {
	$SQL = "SELECT debtorsmaster.name,
				custbranch.phoneno,
				custbranch.brname
			FROM debtorsmaster
			INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'";

	if (isset($_SESSION['BranchCode'])) {
		$SQL .= " AND custbranch.branchcode='" . $_SESSION['BranchCode'] . "'";
	} // isset($_SESSION['BranchCode'])

	$ErrMsg = __('The customer name requested cannot be retrieved because');
	$CustomerResult = DB_query($SQL, $ErrMsg);
	if ($MyRow = DB_fetch_array($CustomerResult)) {
		$CustomerName = htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8', false);
		$PhoneNo = $MyRow['phoneno'];
		$BranchName = $MyRow['brname'];
	} // $MyRow = DB_fetch_array($Result)
	unset($CustomerResult);

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', // Icon image.
	__('Customer'), '" /> ', // Icon title.
	__('Customer'), ' : ', stripslashes($_SESSION['CustomerID']), ' - ', $CustomerName, ' - ', $PhoneNo, __(' has been selected'), '
		</p>'; // Page title.
	echo '<div class="page_help_text">', __('Select a menu option to operate using this customer'), '.</div>';

	echo '<fieldset style="text-align:center">';
	// Customer inquiries options:
	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', __('Inquiries and Reports'), '" />', __('Customer Inquiries'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustomerInquiry.php?CustomerID=', urlencode($_SESSION['CustomerID']), '">', __('Customer Transaction Inquiries'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustomerAccount.php?CustomerID=', urlencode($_SESSION['CustomerID']), '">', __('Customer Account statement on screen'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/Customers.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;Modify=No">', __('View Customer Details'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/PrintCustStatements.php?FromCust=', urlencode($_SESSION['CustomerID']), '&amp;ToCust=', urlencode($_SESSION['CustomerID']), '&amp;EmailOrPrint=print&amp;PrintPDF=Yes">', __('Print Customer Statement'), '</a>
				</li>
				<li class="MenuItem">
					<a data-title="', __('One of the customer\'s contacts must have an email address and be flagged as the address to send the customer statement to for this function to work'), '" href="', $RootPath, '/PrintCustStatements.php?FromCust=', urlencode($_SESSION['CustomerID']), '&amp;ToCust=', urlencode($_SESSION['CustomerID']), '&amp;EmailOrPrint=email&amp;PrintPDF=Yes">', __('Email Customer Statement'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedCustomer=', urlencode($_SESSION['CustomerID']), '">', __('Order Inquiries'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustomerPurchases.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Show purchases from this customer'), '</a>
				</li>
				<li class="MenuItem">
					', wikiLink('Customer', $_SESSION['CustomerID']), '
				</li>
			</ul>
		</fieldset>';

	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', __('Customer Transactions'), '" />', __('Customer Transactions'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="', $RootPath, '/SelectSalesOrder.php?SelectedCustomer=', urlencode($_SESSION['CustomerID']), '">', __('Modify Outstanding Sales Orders'), '</a>
				</li>
				<li class="MenuItem">
					<a data-title="', __('This allows the deposits received from the customer to be matched against invoices'), '" href="', $RootPath, '/CustomerAllocations.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Allocate Receipts OR Credit Notes'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/JobCards.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;BranchNo=', $_SESSION['BranchCode'], '">', __('Job Cards'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustomerReceipt.php?CustomerID=', urlencode($_SESSION['CustomerID']), '&NewReceipt=Yes&Type=Customer">', __('Enter a Receipt From This Customer'), '</a>
				</li>';
	if (isset($_SESSION['CustomerID']) and isset($_SESSION['BranchCode'])) {
		echo '<li class="MenuItem">
				<a href="', $RootPath, '/CounterSales.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;BranchNo=', $_SESSION['BranchCode'], '">', __('Create a Counter Sale for this Customer'), '</a>
			</li>';
	}
	echo '</ul>
		</fieldset>';

	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', __('Customer Maintenance'), '" />', __('Customer Maintenance'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="', $RootPath, '/Customers.php">', __('Add a New Customer'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/Customers.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Modify Customer Details'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustomerBranches.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Add/Edit/Delete Customer Branches'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/SelectProduct.php">', __('Special Customer Prices'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustEDISetup.php">', __('Customer EDI Configuration'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/CustLoginSetup.php">', __('Customer Login Configuration'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Add a customer contact'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Add a note on this customer'), '</a>
				</li>
			</ul>
		</fieldset>';

	echo '</fieldset>';

}

// Search for customers:
echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">
		<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
if (mb_strlen($Msg) > 1) {
	prnMsg($Msg, 'info');
} // mb_strlen($Msg) > 1
echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', __('Search'), '" /> ', __('Search for Customers'), '
	</p>'; // Page title.
echo '<fieldset>
		<legend>', __('Search Criteria'), '</legend>
		<field>';
echo '<field>
		<label for="Keywords">', __('Enter a partial Name'), ':</label>
		<input type="text" maxlength="25" name="Keywords" title=""  size="20" ',( isset($_POST['Keywords']) ? 'value="' . $_POST['Keywords'] . '" ' : '' ), '/>
		<fieldhelp>', __('If there is an entry in this field then customers with the text entered in their name will be returned') , '</fieldhelp>
	</field>';

echo '<field>
		<label for="CustCode">', '<b>' . __('OR') . ' </b>' . __('Enter a partial Code'), ':</label>
		<input maxlength="18" name="CustCode" pattern="[\w-]*" size="15" type="text" title="" ', (isset($_POST['CustCode']) ? 'value="' . $_POST['CustCode'] . '" ' : '' ), '/>
		<fieldhelp>', __('If there is an entry in this field then customers with the text entered in their customer code will be returned') , '</fieldhelp>
	</field>';

echo '<field>
		<label for="CustPhone">', '<b>' . __('OR') . ' </b>' . __('Enter a partial Phone Number'), ':</label>
		<input maxlength="18" name="CustPhone" pattern="[0-9\-\s()+]*" size="15" type="tel" ',( isset($_POST['CustPhone']) ? 'value="' . $_POST['CustPhone'] . '" ' : '' ), '/>
	</field>';

echo '<field>
		<label for="CustAdd">', '<b>' . __('OR') . ' </b>' . __('Enter part of the Address'), ':</label>
		<input maxlength="25" name="CustAdd" size="20" type="text" ',(isset($_POST['CustAdd']) ? 'value="' . $_POST['CustAdd'] . '" ' : '' ), '/>
	</field>';

echo '<field>
		<label for="CustType">', '<b>' . __('OR') . ' </b>' . __('Choose a Type'), ':</label>
		<field>';
if(isset($_POST['CustType'])) {
	// Show Customer Type drop down list
	$Result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	// Error if no customer types setup
	if(DB_num_rows($Result2) == 0) {
		$DataError = 1;
		echo '<a href="' . $RootPath . '/CustomerTypes.php" target="_parent">' . __('Setup Types') . '</a>';
		echo '<field><td colspan="2">' . prnMsg(__('No Customer types defined'), 'error','',true) . '</td></field>';
	} else {
		// If OK show select box with option selected
		echo '<select name="CustType">
				<option value="ALL">' . __('Any') . '</option>';
		while ($MyRow = DB_fetch_array($Result2)) {
			if($_POST['CustType'] == $MyRow['typename']) {
				echo '<option selected="selected" value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
			}// $_POST['CustType'] == $MyRow['typename']
			else {
				echo '<option value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
			}
		}// end while loop
		DB_data_seek($Result2, 0);
		echo '</select>
			</field>';
	}
} else {// CustType is not set
	// No option selected="selected" yet, so show Customer Type drop down list
	$Result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	// Error if no customer types setup
	if(DB_num_rows($Result2) == 0) {
		$DataError = 1;
		echo '<a href="' . $RootPath . '/CustomerTypes.php" target="_parent">' . __('Setup Types') . '</a>';
		echo '<field><td colspan="2">' . prnMsg(__('No Customer types defined'), 'error','',true) . '</td></field>';
	} else {
		// if OK show select box with available options to choose
		echo '<select name="CustType">
				<option value="ALL">' . __('Any') . '</option>';
		while ($MyRow = DB_fetch_array($Result2)) {
			echo '<option value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
		}// end while loop
		DB_data_seek($Result2, 0);
		echo '</select>
			</field>';
	}
}

/* Option to select a sales area */
echo '<field>
		<label for="Area">' . '<b>' . __('OR') . ' </b>' . __('Choose an Area') . ':</label>';
$Result2 = DB_query("SELECT areacode, areadescription FROM areas");
// Error if no sales areas setup
if(DB_num_rows($Result2) == 0) {
	$DataError = 1;
	echo '<a href="' . $RootPath . '/Areas.php" target="_parent">' . __('Setup Areas') . '</a>';
	echo '<field><td colspan="2">' . prnMsg(__('No Sales Areas defined'), 'error','',true) . '</td></field>';
} else {
	// if OK show select box with available options to choose
	echo '<select name="Area">';
	echo '<option value="ALL">' . __('Any') . '</option>';
	while ($MyRow = DB_fetch_array($Result2)) {
		if(isset($_POST['Area']) AND $_POST['Area'] == $MyRow['areacode']) {
			echo '<option selected="selected" value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
	}// end while loop
	DB_data_seek($Result2, 0);
	echo '</select>
		<field>';
}
echo '</fieldset>';

echo '<div class="centre">
		<input name="Search" type="submit" value="', __('Search Now'), '" />
		<input name="CSV" type="submit" value="', __('CSV Format'), '" />
	</div>';

// End search for customers.
if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != '') {
	prnMsg(__('Your account enables you to see only customers allocated to you'), 'warn', __('Note: Sales-person Login'));
} // isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != ''
if (isset($SearchResult)) {
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($SearchResult);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (!isset($_POST['CSV'])) {
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			} // $_POST['PageOffset'] < $ListPageMax

		} // isset($_POST['Next'])
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			} // $_POST['PageOffset'] > 1

		} // isset($_POST['Previous'])
		echo '<input type="hidden" name="PageOffset" value="', $_POST['PageOffset'], '" />';
		if ($ListPageMax > 1) {
			echo '<div class="centre">&nbsp;&nbsp;', $_POST['PageOffset'], ' ', __('of'), ' ', $ListPageMax, ' ', __('pages'), '. ', __('Go to Page'), ': ';
			echo '<select name="PageOffset1">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="', $ListPage, '" selected="selected">', $ListPage, '</option>';
				} // $ListPage == $_POST['PageOffset']
				else {
					echo '<option value="', $ListPage, '">', $ListPage, '</option>';
				}
				$ListPage++;
			} // $ListPage <= $ListPageMax
			echo '</select>
				<input type="submit" name="Go1" value="', __('Go'), '" />
				<input type="submit" name="Previous" value="', __('Previous'), '" />
				<input type="submit" name="Next" value="', __('Next'), '" />';
			echo '</div>';
		} // $ListPageMax > 1
		$RowIndex = 0;
	} // !isset($_POST['CSV'])
	if (DB_num_rows($SearchResult) <> 0) {
		echo '<table cellpadding="2">
				<thead>
					<tr>
						<th class="SortedColumn">', __('Code'), '</th>
						<th class="SortedColumn">', __('Customer Name'), '</th>
						<th class="SortedColumn">', __('Branch'), '</th>
						<th>', __('Contact'), '</th>
						<th>', __('Type'), '</th>
						<th>', __('Phone'), '</th>
						<th>', __('Fax'), '</th>
						<th>', __('Email'), '</th>
					</tr>
				</thead>';
		if (isset($_POST['CSV'])) {
			$FileName = $_SESSION['reports_dir'] . '/Customer_Listing_' . date('Y-m-d') . '.csv';
			echo '<p class="page_help_text">
					<a href="', $FileName, '">', __('Click to view the csv Search Result'), '</a>
				</p>';
			$fp = fopen($FileName, 'w');
			while ($MyRow2 = DB_fetch_array($Result)) {
				fwrite($fp, $MyRow2['debtorno'] . ',' . str_replace(',', '', $MyRow2['name']) . ',' . str_replace(',', '', $MyRow2['address1']) . ',' . str_replace(',', '', $MyRow2['address2']) . ',' . str_replace(',', '', $MyRow2['address3']) . ',' . str_replace(',', '', $MyRow2['address4']) . ',' . str_replace(',', '', $MyRow2['contactname']) . ',' . str_replace(',', '', $MyRow2['typename']) . ',' . $MyRow2['phoneno'] . ',' . $MyRow2['faxno'] . ',' . $MyRow2['email'] . "\n");
			} // $MyRow2 = DB_fetch_array($Result)

		} // isset($_POST['CSV'])
		if (!isset($_POST['CSV'])) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		} // !isset($_POST['CSV'])
		$i = 0; // counter for input controls
		$RowIndex = 0;
		echo '<tbody>';
		while (($MyRow = DB_fetch_array($SearchResult)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			echo '<tr class="striped_row">
					<td><button type="submit" name="SubmitCustomerSelection[', htmlspecialchars($MyRow['debtorno'], ENT_QUOTES, 'UTF-8', false), ']" value="', htmlspecialchars($MyRow['branchcode'], ENT_QUOTES, 'UTF-8', false), '" >', $MyRow['debtorno'], ' ', $MyRow['branchcode'], '</button></td>
					<td class="text">', htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8', false), '</td>
					<td class="text">', htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8', false), '</td>
					<td class="text">', $MyRow['contactname'], '</td>
					<td class="text">', $MyRow['typename'], '</td>
					<td class="text">', $MyRow['phoneno'], '</td>
					<td class="text">', $MyRow['faxno'], '</td>
					<td><a href="mailto://', $MyRow['email'], '">', $MyRow['email'], '</a></td>
				</tr>';
			++$i;
			$RowIndex++;
			// end of page full new headings if

		} // ($MyRow = DB_fetch_array($Result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])
		// end of while loop
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	} // DB_num_rows($Result) <> 0

} // isset($Result)
// end if results to show
if (!isset($_POST['CSV'])) {
	if (isset($ListPageMax) and $ListPageMax > 1) {
		echo '<div class="centre">&nbsp;&nbsp;', $_POST['PageOffset'], ' ', __('of'), ' ', $ListPageMax, ' ', __('pages'), '. ', __('Go to Page'), ': ';
		echo '<select name="PageOffset2">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="', $ListPage, '" selected="selected">', $ListPage, '</option>';
			} // $ListPage == $_POST['PageOffset']
			else {
				echo '<option value="', $ListPage, '">', $ListPage, '</option>';
			}
			$ListPage++;
		} // $ListPage <= $ListPageMax
		echo '</select>
			<input type="submit" name="Go2" value="', __('Go'), '" />
			<input type="submit" name="Previous" value="', __('Previous'), '" />
			<input type="submit" name="Next" value="', __('Next'), '" />';
		echo '</div>';
	} // isset($ListPageMax) and $ListPageMax > 1
	// end if results to show

} // !isset($_POST['CSV'])
echo '</form>';

// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {

		$SQL = "SELECT * FROM geocode_param";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 0) {
			prnMsg(__('You must first setup the geocode parameters') . ' ' . '<a href="' . $RootPath . '/GeocodeSetup.php">' . __('here') . '</a>', 'error');
			include('includes/footer.php');
			exit();
		}
		$MyRow = DB_fetch_array($Result);
		$API_key = $MyRow['geocode_key'];
		$center_long = $MyRow['center_long'];
		$center_lat = $MyRow['center_lat'];
		$map_height = $MyRow['map_height'];
		$map_width = $MyRow['map_width'];
		$map_host = $MyRow['map_host'];

		$SQL = "SELECT
					debtorsmaster.debtorno,
					debtorsmaster.name,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.lat,
					custbranch.lng,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4
				FROM debtorsmaster
				LEFT JOIN custbranch
					ON debtorsmaster.debtorno = custbranch.debtorno
				WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
					AND custbranch.branchcode = '" . $_SESSION['BranchCode'] . "'
				ORDER BY debtorsmaster.debtorno";
		$Result2 = DB_query($SQL);
		$MyRow2 = DB_fetch_array($Result2);
		$Lat = $MyRow2['lat'];
		$Lng = $MyRow2['lng'];

		if ($Lat == 0 and $MyRow2['braddress1'] != '' and $_SESSION['BranchCode'] != '') {
			$delay = 0;
			$base_url = 'https://' . $map_host . '/maps/api/geocode/xml?address=';

			$geocode_pending = true;
			while ($geocode_pending) {
				$address = urlencode($MyRow2['braddress1'] . ',' . $MyRow2['braddress2'] . ',' . $MyRow2['braddress3'] . ',' . $MyRow2['braddress4']);
				$id = $MyRow2['branchcode'];
				$debtorno = $MyRow2['debtorno'];
				$request_url = $base_url . $address . ',&sensor=true';

				$buffer = file_get_contents($request_url) /* or die("url not loading")*/;
				$xml = simplexml_load_string($buffer);
				// echo $xml->asXML();
				$status = $xml->status;
				if (strcmp($status, "OK") == 0) {
					$geocode_pending = false;

					$Lat = $xml->result->geometry->location->lat;
					$Lng = $xml->result->geometry->location->lng;

					$query = sprintf("UPDATE custbranch " . " SET lat = '%s', lng = '%s' " . " WHERE branchcode = '%s' " . " AND debtorno = '%s' LIMIT 1;", ($Lat), ($Lng), ($id), ($debtorno));
					$update_result = DB_query($query);

					if ($update_result == 1) {
						prnMsg(__('GeoCode has been updated for CustomerID') . ': ' . $id . ' - ' . __('Latitude') . ': ' . $Lat . ' ' . __('Longitude') . ': ' . $Lng, 'info');
					}
				} else {
					$geocode_pending = false;
					prnMsg(__('Unable to update GeoCode for CustomerID') . ': ' . $id . ' - ' . __('Received status') . ': ' . $status, 'error');
				}
				usleep($delay);
			}
		}

		if ($Lat == 0) {
			echo '<div class="centre">', __('Mapping is enabled, but no Mapping data to display for this Customer.'), '</div>';
		} // $Lattitude == 0
		else {
			echo '<table cellpadding="4">
					<thead>
						<tr>
							<th style="width:auto">', __('Customer Mapping'), '</th>
						</tr>
						<tr>
							<th style="width:auto">', __('Mapping is enabled, Map will display below.'), '</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><div class="center" id="map" style="height:', $map_height . 'px; margin: 0 auto; width:', $map_width, 'px;"></div></td>
						</tr>
					</tbody>
				</table>';

			// Reference: Google Maps JavaScript API V3, https://developers.google.com/maps/documentation/javascript/reference.
			echo '
<script>
var map;
function initMap() {

	var myLatLng = {lat: ', $Lat, ', lng: ', $Lng, '};', /* Fills with customer's coordinates. */
			'

	var map = new google.maps.Map(document.getElementById(\'map\'), {', /* Creates the map with the road map view. */
			'
		center: myLatLng,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		zoom: 14
	});

	var contentString =', /* Fills the content to be displayed in the InfoWindow. */
			'
		\'<div style="overflow: auto;">\' +
		\'<div><b>', $BranchName, '</b></div>\' +
		\'<div>', $MyRow2['braddress1'], '</div>\' +
		\'<div>', $MyRow2['braddress2'], '</div>\' +
		\'<div>', $MyRow2['braddress3'], '</div>\' +
		\'<div>', $MyRow2['braddress4'], '</div>\' +
		\'</div>\';

	var infowindow = new google.maps.InfoWindow({', /* Creates an info window to display the content of 'contentString'. */
			'
		content: contentString,
		maxWidth: 250
	});

	var marker = new google.maps.Marker({', /* Creates a marker to identify a location on the map. */
			'
		position: myLatLng,
		map: map,
		title: \'', $CustomerName, '\'
	});

	marker.addListener(\'click\', function() {', /* Creates the event clicking the marker to display the InfoWindow. */
			'
		infowindow.open(map, marker);
	});
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=', $API_key, '&callback=initMap"></script>';
			/*		echo '<script src="https://' . $map_host . '/maps/api/js?v=3.exp&key=' . $API_key . '" type="text/javascript"></script>';*/
		}

	} // $_SESSION['geocode_integration'] == 1
	// Extended Customer Info only if selected in Configuration
	if ($_SESSION['Extended_CustomerInfo'] == 1) {
		if ($_SESSION['CustomerID'] != '') {
			$SQL = "SELECT debtortype.typeid,
							debtortype.typename
					FROM debtorsmaster
					INNER JOIN debtortype
						ON debtorsmaster.typeid = debtortype.typeid
					WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$CustomerType = $MyRow['typeid'];
			$CustomerTypeName = $MyRow['typename'];
			// Customer Data
			echo '<br />';
			// Select some basic data about the Customer
			$SQL = "SELECT debtorsmaster.clientsince,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.clientsince))) as customersincedays,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.lastpaiddate))) as lastpaiddays,
						debtorsmaster.paymentterms,
						debtorsmaster.lastpaid,
						debtorsmaster.lastpaiddate,
						currencies.decimalplaces AS currdecimalplaces
					FROM debtorsmaster
					INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
					WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
			$DataResult = DB_query($SQL);
			$MyRow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) as total
					FROM debtortrans
					WHERE debtorno = '" . $_SESSION['CustomerID'] . "'
						AND type !=12";
			$Total1Result = DB_query($SQL);
			$row = DB_fetch_array($Total1Result);
			echo '<table style="width: 45%;">
					<tr>
						<th colspan="3" style="width:33%">', __('Customer Data'), '</th>
					</tr>
					<tr>
						<td class="select" valign="top">';
			/* Customer Data */
			if ($MyRow['lastpaiddate'] == 0) {
				echo __('No receipts from this customer.'), '</td>
					<td class="select">&nbsp;</td>
					<td class="select">&nbsp;</td>
				</tr>';
			} // $MyRow['lastpaiddate'] == 0
			else {
				echo __('Last Paid Date'), ':</td>
					<td class="select"><b>', ConvertSQLDate($MyRow['lastpaiddate']), '</b></td>
					<td class="select">', $MyRow['lastpaiddays'], ' ', __('days'), '</td>
					</tr>';
			}
			echo '<tr>
					<td class="select">', __('Last Paid Amount (inc tax)'), ':</td>
					<td class="select"><b>', locale_number_format($MyRow['lastpaid'], $MyRow['currdecimalplaces']), '</b></td>
					<td class="select">&nbsp;</td>
				</tr>';
			echo '<tr>
					<td class="select">', __('Customer since'), ':</td>
					<td class="select"><b>', ConvertSQLDate($MyRow['clientsince']), '</b></td>
					<td class="select">', $MyRow['customersincedays'], ' ', __('days'), '</td>
				</tr>';
			if ($row['total'] == 0) {
				echo '<tr>
						<td class="select"><b>', __('No Spend from this Customer.'), '</b></td>
						<td class="select">&nbsp;</td>
						<td class="select">&nbsp;</td>
					</tr>';
			} // $row['total'] == 0
			else {
				echo '<tr>
						<td class="select">', __('Total Spend from this Customer (inc tax)'), ':</td>
						<td class="select"><b>', locale_number_format($row['total'], $MyRow['currdecimalplaces']), '</b></td>
						<td class="select"></td>
					</tr>';
			}
			echo '<tr>
					<td class="select">', __('Customer Type'), ':</td>
					<td class="select"><b>', $CustomerTypeName, '</b></td>
					<td class="select">&nbsp;</td>
				</tr>';
			echo '</table>';
		} // $_SESSION['CustomerID'] != ''
		// Customer Contacts
		$SQL = "SELECT * FROM custcontacts
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY contid";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) <> 0) {
			echo '<p class="page_title_text">
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" title="', __('Customer Contacts'), '" alt="" />', ' ', __('Customer Contacts'), '
				</p>';

			echo '<table width="45%">
					<thead>
						<tr>
							<th class="SortedColumn">', __('Name'), '</th>
							<th class="SortedColumn">', __('Role'), '</th>
							<th class="SortedColumn">', __('Phone Number'), '</th>
							<th>', __('Email'), '</th>
							<th class="text">', __('Statement'), '</th>
							<th>', __('Notes'), '</th>
							<th>', __('Edit'), '</th>
							<th>', __('Delete'), '</th>
							<th> <a href="' . $RootPath . '/AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', __('Add New Contact'), '</a></th>
						</tr>
					</thead>';

			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				echo '<tr class="striped_row">
						<td>', $MyRow[2], '</td>
						<td>', $MyRow[3], '</td>
						<td>', $MyRow[4], '</td>
						<td><a href="mailto:', $MyRow[6], '">', $MyRow[6], '</a></td>
						<td>', ($MyRow[7] == 0) ? __('No') : __('Yes'), '</td>
						<td>', $MyRow[5], '</td>
						<td><a href="' . $RootPath . '/AddCustomerContacts.php?Id=', urlencode($MyRow[0]), '&DebtorNo=', urlencode($MyRow[1]), '">', __('Edit'), '</a></td>
						<td><a href="' . $RootPath . '/AddCustomerContacts.php?Id=', urlencode($MyRow[0]), '&DebtorNo=', urlencode($MyRow[1]), '&delete=1">', __('Delete'), '</a></td>
						<td></td>
					</tr>';
			} // END WHILE LIST LOOP
			// Customer Branch Contacts if selected
			if (isset($_SESSION['BranchCode']) and $_SESSION['BranchCode'] != '') {
				$SQL = "SELECT
							branchcode,
							brname,
							contactname,
							phoneno,
							email
						FROM custbranch
						WHERE debtorno='" . $_SESSION['CustomerID'] . "'
							AND branchcode='" . $_SESSION['BranchCode'] . "'";
				$Result2 = DB_query($SQL);
				$BranchContact = DB_fetch_row($Result2);

				echo '<tr class="striped_row">
						<td>', $BranchContact[2], '</td>
						<td>', __('Branch Contact'), ' ', $BranchContact[0], '</td>
						<td>', $BranchContact[3], '</td>
						<td><a href="mailto:', $BranchContact[4], '">', $BranchContact[4], '</a></td>
						<td colspan="5"></td>
					</tr>';
			}
			echo '</tbody>
				</table>';
		} // DB_num_rows($Result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<p class="page_title_text">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" title="', __('Customer Contacts'), '" alt="" />
						<a href="' . $RootPath . '/AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', ' ', __('Add New Contact'), '</a>
					</p>';
			} // $_SESSION['CustomerID'] != ''

		}
		// Customer Notes
		$SQL = "SELECT
					noteid,
					debtorno,
					href,
					note,
					date,
					priority
				FROM custnotes
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY date DESC";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) <> 0) {
			echo '<p class="page_title_text">
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/note_add.png" title="', __('Customer Notes'), '" alt="" />', ' ', __('Customer Notes'), '
				</p>';
			echo '<table style="width: 45%;">
					<thead>
						<tr>
							<th class="SortedColumn">', __('date'), '</th>
							<th>', __('note'), '</th>
							<th>', __('hyperlink'), '</th>
							<th class="SortedColumn">', __('priority'), '</th>
							<th>', __('Edit'), '</th>
							<th>', __('Delete'), '</th>
							<th> <a href="' . $RootPath . '/AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', ' ', __('Add New Note'), '</a> </th>
						</tr>
					</thead>';
			$k = 0; // row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				echo '<tr class="striped_row">
						<td class="date">', ConvertSQLDate($MyRow['date']), '</td>
						<td>', $MyRow['note'], '</td>
						<td><a href="', $MyRow['href'], '">', $MyRow['href'], '</a></td>
						<td>', $MyRow['priority'], '</td>
						<td><a href="' . $RootPath . '/AddCustomerNotes.php?Id=', urlencode($MyRow['noteid']), '&amp;DebtorNo=', urlencode($MyRow['debtorno']), '">', __('Edit'), '</a></td>
						<td><a href="' . $RootPath . '/AddCustomerNotes.php?Id=', urlencode($MyRow['noteid']), '&amp;DebtorNo=', urlencode($MyRow['debtorno']), '&amp;delete=1">', __('Delete'), '</a></td>
						<td></td>
					</tr>';
			} // END WHILE LIST LOOP
			echo '</tbody>';
			echo '</table>';
		} // DB_num_rows($Result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<p class="page_title_text">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/note_add.png" title="', __('Customer Notes'), '" alt="" />
						<a href="' . $RootPath . '/AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', ' ', __('Add New Note for this Customer'), '</a>
					</p>';
			} // $_SESSION['CustomerID'] != ''

		}
		// Custome Type Notes
		$SQL = "SELECT * FROM debtortypenotes
				WHERE typeid='" . $CustomerType . "'
				ORDER BY date DESC";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) <> 0) {
			echo '<p class="page_title_text">
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/folder_add.png" title="', __('Customer Type (Group) Notes'), '" alt="" />', ' ', __('Customer Type (Group) Notes for'), ':<b> ', $CustomerTypeName, '</b>', '
				</p>';
			echo '<table style="width:45%">
					<thead>
						<tr>
							<th class="SortedColumn">', __('date'), '</th>
							<th>', __('Note'), '</th>
							<th>', __('File Link / Reference / URL'), '</th>
							<th class="SortedColumn">', __('Priority'), '</th>
							<th>', __('Edit'), '</th>
							<th>', __('Delete'), '</th>
							<th><a href="' . $RootPath . '/AddCustomerTypeNotes.php?DebtorType=', $CustomerType, '">', __('Add New Group Note'), '</a></th>
						</tr>
					</thead>';
			$k = 0; // row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				echo '<tr class="striped_row">
						<td class="date">', $MyRow[4], '</td>
						<td>', $MyRow[3], '</td>
						<td>', $MyRow[2], '</td>
						<td>', $MyRow[5], '</td>
						<td><a href="' . $RootPath . '/AddCustomerTypeNotes.php?Id=', urlencode($MyRow[0]), '&amp;DebtorType=', urlencode($MyRow[1]), '">', __('Edit'), '</a></td>
						<td><a href="' . $RootPath . '/AddCustomerTypeNotes.php?Id=', urlencode($MyRow[0]), '&amp;DebtorType=', urlencode($MyRow[1]), '&amp;delete=1">', __('Delete'), '</a></td>
					</tr>';
			} // END WHILE LIST LOOP
			echo '</tbody>';
			echo '</table>';
		} // DB_num_rows($Result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<p class="page_title_text">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/folder_add.png" title="', __('Customer Group Notes'), '" alt="" />
						<a href="' . $RootPath . '/AddCustomerTypeNotes.php?DebtorType=', urlencode($CustomerType), '">', ' ', __('Add New Group Note'), '</a>
					</p>';
			} // $_SESSION['CustomerID'] != ''

		}
	} // $_SESSION['Extended_CustomerInfo'] == 1

} // isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != ''
include('includes/footer.php');
