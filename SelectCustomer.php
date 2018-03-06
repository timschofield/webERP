<?php
/* Selection of customer - from where all customer related maintenance, transactions and inquiries start */

include('includes/session.php');
$Title = _('Search Customers');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'SelectCustomer';
include('includes/header.php');

include('includes/SQL_CommonFunctions.inc');

if(isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
}

if(!isset($_SESSION['CustomerID'])) {// initialise if not already done
	$_SESSION['CustomerID'] = '';
}

if(isset($_GET['Area'])) {
	$_POST['Area'] = $_GET['Area'];
	$_POST['Search'] = 'Search';
	$_POST['Keywords'] = '';
	$_POST['CustCode'] = '';
	$_POST['CustPhone'] = '';
	$_POST['CustAdd'] = '';
	$_POST['CustType'] = '';
}

if(!isset($_SESSION['CustomerType'])) {// initialise if not already done
	$_SESSION['CustomerType'] = '';
}

if(isset($_POST['JustSelectedACustomer'])) {
	if(isset ($_POST['SubmitCustomerSelection'])) {
	foreach ($_POST['SubmitCustomerSelection'] as $CustomerID => $BranchCode)
		$_SESSION['CustomerID'] = $CustomerID;
		$_SESSION['BranchCode'] = $BranchCode;
	} else {
		prnMsg(_('Unable to identify the selected customer'), 'error');
	}
}

$msg = '';

if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}

if(!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if(isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	unset($_POST['JustSelectedACustomer']);
	if(isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}

	if(($_POST['Keywords'] == '') AND ($_POST['CustCode'] == '') AND ($_POST['CustPhone'] == '') AND ($_POST['CustType'] == 'ALL') AND ($_POST['Area'] == 'ALL') AND ($_POST['CustAdd'] == '')) {
		// no criteria set then default to all customers
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
				FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
				INNER JOIN debtortype
				ON debtorsmaster.typeid = debtortype.typeid";
	} else {
		$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
		$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
		$_POST['CustPhone'] = trim($_POST['CustPhone']);
		$_POST['CustAdd'] = trim($_POST['CustAdd']);
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
					FROM debtorsmaster INNER JOIN debtortype
						ON debtorsmaster.typeid = debtortype.typeid
					LEFT JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno
					WHERE debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
					AND debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
					AND (custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%' OR custbranch.phoneno IS NULL)
					AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address3 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address4 " . LIKE . " '%" . $_POST['CustAdd'] . "%')";// If there is no custbranch set, the phoneno in custbranch will be null, so we add IS NULL condition otherwise those debtors without custbranches setting will be no searchable and it will make a inconsistence with customer receipt interface.

		if(mb_strlen($_POST['CustType']) > 0 AND $_POST['CustType'] != 'ALL') {
			$SQL .= " AND debtortype.typename = '" . $_POST['CustType'] . "'";
		}

		if(mb_strlen($_POST['Area']) > 0 AND $_POST['Area'] != 'ALL') {
			$SQL .= " AND custbranch.area = '" . $_POST['Area'] . "'";
		}

	}// one of keywords OR custcode OR custphone was more than a zero length string

	if($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$SQL .= " ORDER BY debtorsmaster.name";
	$ErrMsg = _('The searched customer records requested cannot be retrieved because');

	$result = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($result) == 1) {
		$myrow = DB_fetch_array($result);
		$_SESSION['CustomerID'] = $myrow['debtorno'];
		$_SESSION['BranchCode'] = $myrow['branchcode'];
		unset($result);
		unset($_POST['Search']);
	} elseif(DB_num_rows($result) == 0) {
		prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria AND try again'), 'info');
		echo '<br />';
	}
}// end of if search

$TableHead =
	'<table cellpadding="4" width="90%" class="selection">
		<thead>
			<tr>
				<th style="width:33%">' .
					'<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/reports.png" title="' . _('Inquiries and Reports') . '" />' .
					_('Customer Inquiries') . '</th>
				<th style="width:33%">' .
					'<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . _('Transactions') . '" />' .
					_('Customer Transactions') . '</th>
				<th style="width:33%">' .
					'<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Maintenance') . '" />' .
					_('Customer Maintenance') . '</th>
			</tr>
		</thead>
		<tbody>';

if($_SESSION['CustomerID'] != '' AND !isset($_POST['Search']) AND !isset($_POST['CSV'])) {
	// A customer is selected
	if(!isset($_SESSION['BranchCode'])) {
		// !isset($_SESSION['BranchCode'])
		$SQL = "SELECT debtorsmaster.name,
					custbranch.phoneno,
					custbranch.brname
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'";

	} else {
		// isset($_SESSION['BranchCode'])
		$SQL = "SELECT debtorsmaster.name,
					custbranch.phoneno,
					custbranch.brname
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'
			AND custbranch.branchcode='" . $_SESSION['BranchCode'] . "'";
	}
	$ErrMsg = _('The customer name requested cannot be retrieved because');
	$result = DB_query($SQL, $ErrMsg);
	if($myrow = DB_fetch_array($result)) {
		$CustomerName = htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false);
		$PhoneNo = $myrow['phoneno'];
		$BranchName = $myrow['brname'];
	}// $myrow = DB_fetch_array($result)
	unset($result);

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/customer.png" title="',// Icon image.
		_('Customer'), '" /> ',// Icon title.
		_('Customer'), ': ', $_SESSION['CustomerID'], ' - ', $CustomerName, ' - ', $PhoneNo, '</p>',// Page title.
		'<div class="page_help_text">', _('Select a menu option to operate using this customer.'), '</div>',// Page help text.
		'<br />',
		$TableHead,
			'<tr>
				<td valign="top" class="select">';
	// Customer inquiries options:
	echo '<a href="', $RootPath, '/CustomerInquiry.php?CustomerID=', urlencode($_SESSION['CustomerID']), '">' . _('Customer Transaction Inquiries') . '</a><br />';
	echo '<a href="', $RootPath, '/CustomerAccount.php?CustomerID=', urlencode($_SESSION['CustomerID']), '">' . _('Customer Account statement on screen') . '</a><br />';
	echo '<a href="', $RootPath, '/Customers.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;Modify=No">' . _('View Customer Details') . '</a><br />';
	echo '<a href="', $RootPath, '/PrintCustStatements.php?FromCust=', urlencode($_SESSION['CustomerID']), '&amp;ToCust=', urlencode($_SESSION['CustomerID']), '&amp;EmailOrPrint=print&amp;PrintPDF=Yes">' . _('Print Customer Statement') . '</a><br />';
	echo '<a title="' . _('One of the customer\'s contacts must have an email address and be flagged as the address to send the customer statement to for this function to work') . '" href="', $RootPath, '/PrintCustStatements.php?FromCust=', urlencode($_SESSION['CustomerID']), '&amp;ToCust=', urlencode($_SESSION['CustomerID']), '&amp;EmailOrPrint=email&amp;PrintPDF=Yes">' . _('Email Customer Statement') . '</a><br />';
	echo '<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedCustomer=', urlencode($_SESSION['CustomerID']), '">' . _('Order Inquiries') . '</a><br />';
	echo '<a href="', $RootPath, '/CustomerPurchases.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Show purchases from this customer') . '</a><br />';
	wikiLink('Customer', $_SESSION['CustomerID']);
	echo '</td><td valign="top" class="select">';
	// Customer transactions options:
	echo '<a href="', $RootPath, '/SelectSalesOrder.php?SelectedCustomer=', urlencode($_SESSION['CustomerID']), '">' . _('Modify Outstanding Sales Orders') . '</a><br />';
	echo '<a title="' . _('This allows the deposits received from the customer to be matched against invoices') . '" href="', $RootPath, '/CustomerAllocations.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Allocate Receipts or Credit Notes') . '</a><br />';
	if(isset($_SESSION['CustomerID']) AND isset($_SESSION['BranchCode'])) {
	echo '<a href="', $RootPath, '/CounterSales.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;BranchNo=' . $_SESSION['BranchCode'] . '">' . _('Create a Counter Sale for this Customer') . '</a><br />';
	}
	echo '</td><td valign="top" class="select">';
	// Customer maintenance options:
	echo '<a href="', $RootPath, '/Customers.php">' . _('Add a New Customer') . '</a><br />';
	echo '<a href="', $RootPath, '/Customers.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Modify Customer Details') . '</a><br />';
	echo '<a href="', $RootPath, '/CustomerBranches.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Add/Edit/Delete Customer Branches') . '</a><br />';
	echo '<a href="', $RootPath, '/SelectProduct.php">' . _('Special Customer Prices') . '</a><br />';
	echo '<a href="', $RootPath, '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a><br />';
	echo '<a href="', $RootPath, '/CustLoginSetup.php">' . _('Customer Login Configuration'), '</a><br />';
	echo '<a href="', $RootPath, '/AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', _('Add a customer contact'), '</a><br />';
	echo '<a href="', $RootPath, '/AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', _('Add a note on this customer'), '</a>';
	echo '</td>
			</tr>
		<tbody>
		</table>';
} else {
	// Customer is not selected yet
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/customer.png" title="', // Icon image.
		_('Customers'), '" /> ', // Icon title.
		_('Customers'), '</p>',// Page title.
		'<br />',
		$TableHead,
		'<tr>',
			'<td class="select"></td>',// Customer inquiries options.
			'<td class="select"></td>',// Customer transactions options.
			'<td class="select">';
	if(!isset($_SESSION['SalesmanLogin']) OR $_SESSION['SalesmanLogin'] == '') {
		echo '<a href="', $RootPath, '/Customers.php">', _('Add a New Customer'), '</a><br />';
	}
	echo '</td>',// Item maintenance options.
		'</tr><tbody></table>';
}

// Search for customers:
echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
	'<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
if(mb_strlen($msg) > 1) {
	prnMsg($msg, 'info');
}
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/magnifier.png" title="',// Icon image.
	_('Search'), '" /> ',// Icon title.
	_('Search for Customers'), '</p>';// Page title.

echo '<table cellpadding="3" class="selection">';

echo '<tr>
		<td colspan="2">', _('Enter a partial Name'), ':</td>
		<td><input type="text" maxlength="25" name="Keywords" title="', _('If there is an entry in this field then customers with the text entered in their name will be returned') , '"  size="20" ',
			( isset($_POST['Keywords']) ? 'value="' . $_POST['Keywords'] . '" ' : '' ), '/></td>';

echo '<td><b>', _('OR'), '</b></td><td>', _('Enter a partial Code'), ':</td>
		<td><input maxlength="18" name="CustCode" pattern="[\w-]*" size="15" type="text" title="', _('If there is an entry in this field then customers with the text entered in their customer code will be returned') , '" ', (isset($_POST['CustCode']) ? 'value="' . $_POST['CustCode'] . '" ' : '' ), '/></td>
	</tr>';

echo '<tr>
		<td><b>', _('OR'), '</b></td><td>', _('Enter a partial Phone Number'), ':</td>
		<td><input maxlength="18" name="CustPhone" pattern="[0-9\-\s()+]*" size="15" type="tel" ',
			( isset($_POST['CustPhone']) ? 'value="' . $_POST['CustPhone'] . '" ' : '' ), '/></td>';

echo '<td><b>', _('OR'), '</b></td><td>', _('Enter part of the Address'), ':</td>
		<td><input maxlength="25" name="CustAdd" size="20" type="text" ',
			(isset($_POST['CustAdd']) ? 'value="' . $_POST['CustAdd'] . '" ' : '' ), '/></td>
	</tr>';

echo '<tr>
		<td><b>', _('OR'), '</b></td><td>', _('Choose a Type'), ':</td>
		<td>';
if(isset($_POST['CustType'])) {
	// Show Customer Type drop down list
	$result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	// Error if no customer types setup
	if(DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} else {
		// If OK show select box with option selected
		echo '<select name="CustType">
				<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			if($_POST['CustType'] == $myrow['typename']) {
				echo '<option selected="selected" value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
			}// $_POST['CustType'] == $myrow['typename']
			else {
				echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
			}
		}// end while loop
		DB_data_seek($result2, 0);
		echo '</select></td>';
	}
} else {// CustType is not set
	// No option selected="selected" yet, so show Customer Type drop down list
	$result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	// Error if no customer types setup
	if(DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} else {
		// if OK show select box with available options to choose
		echo '<select name="CustType">
				<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
		}// end while loop
		DB_data_seek($result2, 0);
		echo '</select></td>';
	}
}

/* Option to select a sales area */
echo '<td><b>', _('OR'), '</b></td>
		<td>' . _('Choose an Area') . ':</td><td>';
$result2 = DB_query("SELECT areacode, areadescription FROM areas");
// Error if no sales areas setup
if(DB_num_rows($result2) == 0) {
	$DataError = 1;
	echo '<a href="Areas.php" target="_parent">' . _('Setup Areas') . '</a>';
	echo '<tr><td colspan="2">' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
} else {
	// if OK show select box with available options to choose
	echo '<select name="Area">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($myrow = DB_fetch_array($result2)) {
		if(isset($_POST['Area']) AND $_POST['Area'] == $myrow['areacode']) {
			echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	}// end while loop
	DB_data_seek($result2, 0);
	echo '</select></td></tr>';
}

echo '</table><br />';
echo '<div class="centre">
		<input name="Search" type="submit" value="', _('Search Now'), '" />
		<input name="CSV" type="submit" value="', _('CSV Format'), '" />
	</div>';
// End search for customers.


if(isset($_SESSION['SalesmanLogin']) AND $_SESSION['SalesmanLogin'] != '') {
	prnMsg(_('Your account enables you to see only customers allocated to you'), 'warn', _('Note: Sales-person Login'));
}

if(isset($result)) {
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if(!isset($_POST['CSV'])) {
		if(isset($_POST['Next'])) {
			if($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if(isset($_POST['Previous'])) {
			if($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
		if($ListPageMax > 1) {
			echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset1">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go1" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '</div>';
		}
		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>
						<th class="ascending">' . _('Code') . '</th>
						<th class="ascending">' . _('Customer Name') . '</th>
						<th class="ascending">' . _('Branch') . '</th>
						<th class="ascending">' . _('Contact') . '</th>
						<th class="ascending">' . _('Type') . '</th>
						<th class="ascending">' . _('Phone') . '</th>
						<th class="ascending">' . _('Fax') . '</th>
						<th class="ascending">' . _('Email') . '</th>
					</tr>
				</thead>';
		$RowIndex = 0;
	}// end if NOT producing a CSV file
	if(DB_num_rows($result) <> 0) {
		if(isset($_POST['CSV'])) {// producing a CSV file of customers
			$FileName = $_SESSION['reports_dir'] . '/Customer_Listing_' . date('Y-m-d') . '.csv';
			echo '<br /><p class="page_title_text"><a href="' . $FileName . '">' . _('Click to view the csv Search Result') . '</p>';
			$fp = fopen($FileName, 'w');
			while ($myrow2 = DB_fetch_array($result)) {
				fwrite($fp, $myrow2['debtorno'] . ',' . str_replace(',', '', $myrow2['name']) . ',' . str_replace(',', '', $myrow2['address1']) . ',' . str_replace(',', '', $myrow2['address2']) . ',' . str_replace(',', '', $myrow2['address3']) . ',' . str_replace(',', '', $myrow2['address4']) . ',' . str_replace(',', '', $myrow2['contactname']) . ',' . str_replace(',', '', $myrow2['typename']) . ',' . $myrow2['phoneno'] . ',' . $myrow2['faxno'] . ',' . $myrow2['email'] . "\n");
			}// end loop through customers returned
		}// end if producing a CSV
		if(!isset($_POST['CSV'])) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		$i = 0;// counter for input controls
		echo '<tbody>';
		while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			echo '<tr class="striped_row">
				<td><button type="submit" name="SubmitCustomerSelection[', htmlspecialchars($myrow['debtorno'], ENT_QUOTES, 'UTF-8', false), ']" value="', htmlspecialchars($myrow['branchcode'], ENT_QUOTES, 'UTF-8', false), '" >', $myrow['debtorno'], ' ', $myrow['branchcode'], '</button></td>
				<td class="text">', htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', htmlspecialchars($myrow['brname'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['contactname'], '</td>
				<td class="text">', $myrow['typename'], '</td>
				<td class="text">', $myrow['phoneno'], '</td>
				<td class="text">', $myrow['faxno'], '</td>
				<td><a href="mailto://'.$myrow['email'].'">' . $myrow['email']. '</a></td>
			</tr>';
			$i++;
			$RowIndex++;
			// end of page full new headings if
		}// end loop through customers
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}// end if there are customers to show
}// end if results to show

if(!isset($_POST['CSV'])) {
	if(isset($ListPageMax) AND $ListPageMax > 1) {
		echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset2">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			}// $ListPage == $_POST['PageOffset']
			else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}// $ListPage <= $ListPageMax
		echo '</select>
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}// end if results to show
}

echo '</form>';

// Only display the geocode map if the integration is turned on, AND there is a latitude/longitude to display
if(isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID'] != '') {

	if($_SESSION['geocode_integration'] == 1) {

		$SQL = "SELECT * FROM geocode_param WHERE 1";
		$ErrMsg = _('An error occurred in retrieving the information');
		$result = DB_query($SQL, $ErrMsg);
		if(DB_num_rows($result) == 0) {
			prnMsg( _('You must first setup the geocode parameters') . ' ' . '<a href="' . $RootPath . '/GeocodeSetup.php">' . _('here') . '</a>', 'error');
			include('includes/footer.php');
			exit;
		}
		$myrow = DB_fetch_array($result);
		$API_key = $myrow['geocode_key'];
		$center_long = $myrow['center_long'];
		$center_lat = $myrow['center_lat'];
		$map_height = $myrow['map_height'];
		$map_width = $myrow['map_width'];
		$map_host = $myrow['map_host'];
		if($map_host == '') {$map_host = 'maps.googleapis.com';}// If $map_host is empty, use a default map host.

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
		$ErrMsg = _('An error occurred in retrieving the information');
		$result2 = DB_query($SQL, $ErrMsg);
		$myrow2 = DB_fetch_array($result2);
		$Lat = $myrow2['lat'];
		$Lng = $myrow2['lng'];

		if($Lat == 0 and $myrow2["braddress1"] != '' and $_SESSION['BranchCode'] != '') {
			$delay = 0;
			$base_url = "https://" . $map_host . "/maps/api/geocode/xml?address=";

			$geocode_pending = true;
			while ($geocode_pending) {
				$address = urlencode($myrow2["braddress1"] . "," . $myrow2["braddress2"] . "," . $myrow2["braddress3"] . "," . $myrow2["braddress4"]);
				$id = $myrow2["branchcode"];
				$debtorno =$myrow2["debtorno"];
				$request_url = $base_url . $address . ',&sensor=true';

				$buffer = file_get_contents($request_url)/* or die("url not loading")*/;
				$xml = simplexml_load_string($buffer);
				// echo $xml->asXML();

				$status = $xml->status;
				if(strcmp($status, "OK") == 0) {
					$geocode_pending = false;

					$Lat = $xml->result->geometry->location->lat;
					$Lng = $xml->result->geometry->location->lng;

					$query = sprintf("UPDATE custbranch " .
							" SET lat = '%s', lng = '%s' " .
							" WHERE branchcode = '%s' " .
						" AND debtorno = '%s' LIMIT 1;",
							($Lat),
							($Lng),
							($id),
							($debtorno));
					$update_result = DB_query($query);

					if($update_result == 1) {
						prnMsg( _('GeoCode has been updated for CustomerID') . ': ' . $id . ' - ' . _('Latitude') . ': ' . $Lat . ' ' . _('Longitude') . ': ' . $Lng ,'info');
					}
				} else {
					$geocode_pending = false;
					prnMsg(_('Unable to update GeoCode for CustomerID') . ': ' . $id . ' - ' . _('Received status') . ': ' . $status , 'error');
				}
				usleep($delay);
			}
		}

		echo '<br />';
		if($Lat == 0) {
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Customer.') . '</div>';
		} else {
			echo '<table cellpadding="4">
				<thead>
					<tr>
						<th style="width:auto">', _('Customer Mapping'), '</th>
					</tr>
					<tr>
						<th style="width:auto">', _('Mapping is enabled, Map will display below.'), '</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><div class="centre" id="map" style="height:', $map_height . 'px; margin: 0 auto; width:', $map_width, 'px;"></div></td>
					</tr>
				</tbody>
				</table>';

		// Reference: Google Maps JavaScript API V3, https://developers.google.com/maps/documentation/javascript/reference.
	    echo '
<script type="text/javascript">
var map;
function initMap() {

	var myLatLng = {lat: ', $Lat, ', lng: ', $Lng, '};', /* Fills with customer's coordinates. */'

	var map = new google.maps.Map(document.getElementById(\'map\'), {', /* Creates the map with the road map view. */'
		center: myLatLng,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		zoom: 14
	});

	var contentString =', /* Fills the content to be displayed in the InfoWindow. */'
		\'<div style="overflow: auto;">\' +
		\'<div><b>', $BranchName, '</b></div>\' +
		\'<div>', $myrow2['braddress1'], '</div>\' +
		\'<div>', $myrow2['braddress2'], '</div>\' +
		\'<div>', $myrow2['braddress3'], '</div>\' +
		\'<div>', $myrow2['braddress4'], '</div>\' +
		\'</div>\';

	var infowindow = new google.maps.InfoWindow({', /* Creates an info window to display the content of 'contentString'. */'
		content: contentString,
		maxWidth: 250
	});

	var marker = new google.maps.Marker({', /* Creates a marker to identify a location on the map. */'
		position: myLatLng,
		map: map,
		title: \'', $CustomerName, '\'
	});

	marker.addListener(\'click\', function() {', /* Creates the event clicking the marker to display the InfoWindow. */'
		infowindow.open(map, marker);
	});
}
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=', $API_key, '&callback=initMap"></script>';
/*		echo '<script src="https://' . $map_host . '/maps/api/js?v=3.exp&key=' . $API_key . '" type="text/javascript"></script>';*/
		}

	}// end if Geocode integration is turned on

	// Extended Customer Info only if selected in Configuration
	if($_SESSION['Extended_CustomerInfo'] == 1) {
		if($_SESSION['CustomerID'] != '') {
			$SQL = "SELECT debtortype.typeid,
							debtortype.typename
						FROM debtorsmaster INNER JOIN debtortype
					ON debtorsmaster.typeid = debtortype.typeid
					WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$result = DB_query($SQL, $ErrMsg);
			$myrow = DB_fetch_array($result);
			$CustomerType = $myrow['typeid'];
			$CustomerTypeName = $myrow['typename'];
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
					FROM debtorsmaster INNER JOIN currencies
					ON debtorsmaster.currcode=currencies.currabrev
					WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
			$DataResult = DB_query($SQL);
			$myrow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) as total
					FROM debtortrans
					WHERE debtorno = '" . $_SESSION['CustomerID'] . "'
					AND type !=12";
			$Total1Result = DB_query($SQL);
			$row = DB_fetch_array($Total1Result);
			echo '<table cellpadding="4" style="width: 45%;">
				<tr>
					<th colspan="3" style="width:auto">', _('Customer Data'), '</th>
				</tr>
				<tr>
					<td class="select" valign="top">';
			/* Customer Data */
			if($myrow['lastpaiddate'] == 0) {
				echo _('No receipts from this customer.'), '</td>
					<td class="select">&nbsp;</td>
					<td class="select">&nbsp;</td>
				</tr>';
			} else {
				echo _('Last Paid Date'), ':</td>
					<td class="select"><b>' . ConvertSQLDate($myrow['lastpaiddate']), '</b></td>
					<td class="select">', $myrow['lastpaiddays'], ' ', _('days'), '</td>
				</tr>';
			}
			echo '<tr>
					<td class="select">', _('Last Paid Amount (inc tax)'), ':</td>
					<td class="select"><b>', locale_number_format($myrow['lastpaid'], $myrow['currdecimalplaces']), '</b></td>
					<td class="select">&nbsp;</td>
				</tr>';
			echo '<tr>
					<td class="select">', _('Customer since'), ':</td>
					<td class="select"><b>', ConvertSQLDate($myrow['clientsince']), '</b></td>
					<td class="select">', $myrow['customersincedays'], ' ', _('days'), '</td>
				</tr>';
			if($row['total'] == 0) {
				echo '<tr>
						<td class="select"><b>', _('No Spend from this Customer.'), '</b></td>
						<td class="select">&nbsp;</td>
						<td class="select">&nbsp;</td>
					</tr>';
			} else {
				echo '<tr>
						<td class="select">' . _('Total Spend from this Customer (inc tax)') . ':</td>
						<td class="select"><b>' . locale_number_format($row['total'], $myrow['currdecimalplaces']) . '</b></td>
						<td class="select"></td>
						</tr>';
			}
			echo '<tr>
					<td class="select">', _('Customer Type'), ':</td>
					<td class="select"><b>', $CustomerTypeName, '</b></td>
					<td class="select">&nbsp;</td>
				</tr>';
			echo '</table>';
		}// end if $_SESSION['CustomerID'] != ''

		// Customer Contacts
		$SQL = "SELECT * FROM custcontacts
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY contid";
		$result = DB_query($SQL);

		if(DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" />' . ' ' . _('Customer Contacts') . '</div>';
			echo '<br /><table width="45%">
 					<thead>
						<tr>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Role') . '</th>
							<th class="ascending">' . _('Phone Number') . '</th>
							<th class="ascending">' . _('Email') . '</th>
							<th class="text">' . _('Statement') . '</th>
							<th class="text">', _('Notes'), '</th>
							<th class="noprint">', _('Edit'), '</th>
							<th class="noprint">' . _('Delete') . '</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="7"><a href="AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', _('Add New Contact'), '</a></th>
						</tr>
					</tfoot>
					<tbody>';

			while ($myrow = DB_fetch_array($result)) {
				echo '<tr class="striped_row">
					<td>' , $myrow[2] , '</td>
					<td>' , $myrow[3] , '</td>
					<td>' , $myrow[4] , '</td>
					<td><a href="mailto:' , $myrow[6] , '">' , $myrow[6] . '</a></td>
					<td>' , ($myrow[7]==0) ? _('No') : _('Yes'), '</td>
					<td>' , $myrow[5] , '</td>
					<td><a href="AddCustomerContacts.php?Id=' , $myrow[0] , '&amp;DebtorNo=' , $myrow[1] , '">' , _('Edit') , '</a></td>
					<td><a href="AddCustomerContacts.php?Id=' , $myrow[0] , '&amp;DebtorNo=' , $myrow[1] , '&amp;delete=1">' , _('Delete') , '</a></td>
					</tr>';
			}// END WHILE LIST LOOP

			// Customer Branch Contacts if selected
			if(isset ($_SESSION['BranchCode']) AND $_SESSION['BranchCode'] != '') {
				$SQL = "SELECT
							branchcode,
							brname,
							contactname,
							phoneno,
							email
						FROM custbranch
						WHERE debtorno='" . $_SESSION['CustomerID'] . "'
							AND branchcode='" . $_SESSION['BranchCode'] . "'";
				$result2 = DB_query($SQL);
				$BranchContact = DB_fetch_row($result2);

				echo '<tr class="striped_row">
						<td>' . $BranchContact[2] . '</td>
						<td>' . _('Branch Contact') . ' ' . $BranchContact[0] . '</td>
						<td>' . $BranchContact[3] . '</td>
						<td><a href="mailto:' . $BranchContact[4] . '">' . $BranchContact[4] . '</a></td>
						<td colspan="3"></td>
					</tr>';
			}
			echo '</tbody>
			</table>';
		}// end if there are contact rows returned
		else {
			if($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" /><a href="AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . ' ' . _('Add New Contact') . '</a></div>';
			}
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
		$result = DB_query($SQL);
		if(DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" />' . ' ' . _('Customer Notes') . '</div><br />';
			echo '<table style="width: 45%;">
				<thead>
					<tr>
					<th class="ascending">' . _('Date') . '</th>
					<th>' . _('Note') . '</th>
					<th>' . _('Hyperlink') . '</th>
					<th class="ascending">' . _('Priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . ' ' . _('Add New Note') . '</a> </th>
					</tr>
				</thead>
				<tbody>';

			while ($myrow = DB_fetch_array($result)) {
				echo '<tr class="striped_row">
					<td>' . ConvertSQLDate($myrow['date']) . '</td>
					<td>' . $myrow['note'] . '</td>
					<td><a href="' . $myrow['href'] . '">' . $myrow['href'] . '</a></td>
					<td>' . $myrow['priority'] . '</td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&amp;DebtorNo=' . $myrow['debtorno'] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&amp;DebtorNo=' . $myrow['debtorno'] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			}// END WHILE LIST LOOP
			echo '</tbody></table>';
		}// end if there are customer notes to display
		else {
			if($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" /><a href="AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . ' ' . _('Add New Note for this Customer') . '</a></div>';
			}
		}
		// Custome Type Notes
		$SQL = "SELECT * FROM debtortypenotes
				WHERE typeid='" . $CustomerType . "'
				ORDER BY date DESC";
		$result = DB_query($SQL);
		if(DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/folder_add.png" title="' . _('Customer Type (Group) Notes') . '" alt="" />' . ' ' . _('Customer Type (Group) Notes for:' . '<b> ' . $CustomerTypeName . '</b>') . '</div><br />';
			echo '<table style="width: 45%;">
				<thead>
					<tr>
				 	<th class="ascending">' . _('Date') . '</th>
					<th>' . _('Note') . '</th>
					<th>' . _('File Link / Reference / URL') . '</th>
					<th class="ascending">' . _('Priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . _('Add New Group Note') . '</a></th>
					</tr>
				</thead>
				<tbody>';

			while ($myrow = DB_fetch_array($result)) {
				echo '<tr class="striped_row">
					<td>' . $myrow[4] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[2] . '</td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&amp;DebtorType=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&amp;DebtorType=' . $myrow[1] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			}// END WHILE LIST LOOP
			echo '</tbody></table>';
		}// end if there are customer group notes to display
		else {
			if($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/folder_add.png" title="' . _('Customer Group Notes') . '" alt="" /><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . ' ' . _('Add New Group Note') . '</a></div><br />';
			}
		}
	}// end if Extended_CustomerInfo is turned on
}// end if isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID'] != ''
include('includes/footer.php');
?>
