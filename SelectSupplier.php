<?php

/* Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier */

include('includes/session.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['SupplierID'])) {
	$_SESSION['SupplierID']=$_GET['SupplierID'];
}
if (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}
// only get geocode information if integration is on, and supplier has been selected
if ($_SESSION['geocode_integration'] == 1 AND isset($_SESSION['SupplierID'])) {
	$SQL = "SELECT * FROM geocode_param";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SQL = "SELECT suppliers.supplierid,
				suppliers.lat,
				suppliers.lng
			FROM suppliers
			WHERE suppliers.supplierid = '" . $_SESSION['SupplierID'] . "'
			ORDER BY suppliers.supplierid";
	$Result2 = DB_query($SQL);
	$MyRow2 = DB_fetch_array($Result2);
	$lat = $MyRow2['lat'];
	$lng = $MyRow2['lng'];
	$APIKey = $MyRow['geocode_key'];
	$center_long = $MyRow['center_long'];
	$center_lat = $MyRow['center_lat'];
	$map_height = $MyRow['map_height'];
	$map_width = $MyRow['map_width'];
	$MapHost = $MyRow['map_host'];
	$ExtraHeadContent = '<script src="https://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $APIKey . '"></script>' . "\n";
	$ExtraHeadContent .= ' <script>' . "\n";
	$ExtraHeadContent .= '	function load() {
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("map"));
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());';
	$ExtraHeadContent .= '			map.setCenter(new GLatLng(' . $lat . ', ' . $lng . '), 11);';
	$ExtraHeadContent .= '			var marker = new GMarker(new GLatLng(' . $lat . ', ' . $lng . '));';
	$ExtraHeadContent .= '			map.addOverlay(marker);
			GEvent.addListener(marker, "click", function() {
				marker.openInfoWindowHtml(WINDOW_HTML);
			});
			marker.openInfoWindowHtml(WINDOW_HTML);
		}
	}
</script>
';
}

$Title = __('Search Suppliers');
$ViewTopic = 'AccountsPayable';
$BookMark = 'SelectSupplier';
$BodyOnLoad='load();';
include('includes/header.php');

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['Search'])
	OR isset($_POST['Go'])
	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {

	if (mb_strlen($_POST['Keywords']) > 0 AND mb_strlen($_POST['SupplierCode']) > 0) {
		prnMsg( __('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info' );
	}
	if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
		$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4,
					telephone,
					email,
					url
				FROM suppliers
				ORDER BY suppname";
	} else {
		if (mb_strlen($_POST['Keywords']) > 0) {
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4,
							telephone,
							email,
							url
						FROM suppliers
						WHERE suppname " . LIKE . " '" . $SearchString . "'
						ORDER BY suppname";
		} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
			$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4,
							telephone,
							email,
							url
						FROM suppliers
						WHERE supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'
						ORDER BY supplierid";
		}
	} //one of keywords or SupplierCode was more than a zero length string
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_row($Result);
		$SingleSupplierReturned = $MyRow[0];
	}
	if (isset($SingleSupplierReturned)) { /*there was only one supplier returned */
 	   $_SESSION['SupplierID'] = $SingleSupplierReturned;
	   unset($_POST['Keywords']);
	   unset($_POST['SupplierCode']);
	   unset($_POST['Search']);
        } else {
               unset($_SESSION['SupplierID']);
        }
} //end of if search

$TableHead =
	'<table cellpadding="4" width="90%" class="selection">
		<thead>
			<tr>
				<th style="width:33%">' .
					'<img style="margin-right:4px" alt="" src="' . $RootPath . '/css/' . $Theme . '/images/reports.png" title="' . __('Inquiries and Reports') . '" />' .
					__('Supplier Inquiries') . '</th>
				<th style="width:33%">' .
					'<img style="margin-right:4px" alt="" src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . __('Transactions') . '" />' .
					__('Supplier Transactions') . '</th>
				<th style="width:33%">' .
					'<img style="margin-right:4px" alt="" src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Maintenance') . '" />' .
					__('Supplier Maintenance') . '</th>
			</tr>
		</thead>
		<tbody>';
if (isset($_SESSION['SupplierID'])) {
	// A supplier is selected
	$SupplierName = '';
	$SQL = "SELECT suppliers.suppname
			FROM suppliers
			WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL);
	if (DB_num_rows($SupplierNameResult) == 1) {
		$MyRow = DB_fetch_row($SupplierNameResult);
		$SupplierName = $MyRow[0];
	}

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/supplier.png" title="', // Icon image.
		__('Supplier'), '" /> ', // Icon title.
		__('Supplier'), ': ', $_SESSION['SupplierID'], ' - ', $SupplierName, '</p>',// Page title.
		'<div class="page_help_text">', __('Select a menu option to operate using this supplier.'), '</div>',// Page help text.
		'<br />',
		$TableHead,
			'<tr>
				<td valign="top" class="select">';
	// Supplier inquiries options:
	echo '<a href="' . $RootPath . '/SupplierInquiry.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Supplier Account Inquiry') . '</a>
		<br />
		<a href="' . $RootPath . '/SupplierGRNAndInvoiceInquiry.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '&amp;SupplierName='.urlencode($SupplierName).'">' . __('Supplier Delivery Note AND GRN inquiry') . '</a>
		<br />
		<br />';

	echo '<br /><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . __('Add / Receive / View Outstanding Purchase Orders') . '</a>';
	echo '<br /><a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . __('View All Purchase Orders') . '</a><br />';
	wikiLink('Supplier', $_SESSION['SupplierID']);
	echo '<br /><a href="' . $RootPath . '/ShiptsList.php?SupplierID=' . $_SESSION['SupplierID'] . '&amp;SupplierName=' . urlencode($SupplierName) . '">' . __('List all open shipments for') .' '.$SupplierName. '</a>';
	echo '<br /><a href="' . $RootPath . '/Shipt_Select.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . __('Search / Modify / Close Shipments') . '</a>';
	echo '<br /><a href="' . $RootPath . '/SuppPriceList.php?SelectedSupplier=' . $_SESSION['SupplierID'] . '">' . __('Supplier Price List') . '</a>';
	echo '</td><td valign="top" class="select">'; /* Supplier Transactions */
	echo '<a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Enter a Purchase Order for This Supplier') . '</a><br />';
	echo '<a href="' . $RootPath . '/SupplierInvoice.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Enter a Suppliers Invoice') . '</a><br />';
	echo '<a href="' . $RootPath . '/SupplierCredit.php?New=true&amp;SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Enter a Suppliers Credit Note') . '</a><br />';
	echo '<a href="' . $RootPath . '/Payments.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Enter a Payment to, or Receipt from the Supplier') . '</a><br />';
	echo '<br />';
	echo '<br /><a href="' . $RootPath . '/ReverseGRN.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Reverse an Outstanding Goods Received Note (GRN)') . '</a>';
	echo '</td><td valign="top" class="select">'; /* Supplier Maintenance */
	echo '<a href="' . $RootPath . '/Suppliers.php">' . __('Add a New Supplier') . '</a>
		<br /><a href="' . $RootPath . '/Suppliers.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Modify Or Delete Supplier Details') . '</a>
		<br /><a href="' . $RootPath . '/SupplierContacts.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Add/Edit/Delete Supplier Contacts') . '</a>
		<br />
		<br /><a href="' . $RootPath . '/SellThroughSupport.php?SupplierID=' . $_SESSION['SupplierID'] . '">' . __('Set Up Sell Through Support Deals') . '</a>
		<br /><a href="' . $RootPath . '/Shipments.php?NewShipment=Yes">' . __('Set Up A New Shipment') . '</a>
		<br /><a href="' . $RootPath . '/SuppLoginSetup.php">' . __('Supplier Login Configuration') . '</a>
		</td>
		</tr>
		<tbody></table>';
} else {
	// Supplier is not selected yet
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/supplier.png" title="', // Icon image.
		__('Suppliers'), '" /> ', // Icon title.
		__('Suppliers'), '</p>',// Page title.
		'<br />',
		$TableHead,
		'<tr>',
			'<td class="select"></td>',// Supplier inquiries options.
			'<td class="select"></td>',// Supplier transactions options.
			'<td class="select"><a href="', $RootPath, '/Suppliers.php">', __('Add a New Supplier'), '</a></td>',// Supplier Maintenance options.
		'</tr><tbody></table>';
}
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for Suppliers') . '</p>';

echo '<fieldset>
		<legend class="search">', __('Search Criteria'), '</legend>
		<field>
			<label for="Keywords">' . __('Enter a partial Name') . ':</label>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
}
echo '<field>
		<label for="SupplierCode">' . '<b>' . __('OR') . ' </b>' . __('Enter a partial Code') . ':</label>';
if (isset($_POST['SupplierCode'])) {
	echo '<input type="text" autofocus="autofocus" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
} else {
	echo '<input type="text" autofocus="autofocus" name="SupplierCode" size="15" maxlength="18" />';
}
echo '</field>
	</fieldset>';

echo '<div class="centre"><input type="submit" name="Search" value="' . __('Search Now') . '" /></div>';
//if (isset($Result) AND !isset($SingleSupplierReturned)) {
if (isset($_POST['Search'])) {
	$ListCount = DB_num_rows($Result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	if ($ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': </p>';
		echo '<select name="PageOffset">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go" value="' . __('Go') . '" />
			<input type="submit" name="Previous" value="' . __('Previous') . '" />
			<input type="submit" name="Next" value="' . __('Next') . '" />';
		echo '<br />';
	}
	echo '<input type="hidden" name="Search" value="' . __('Search Now') . '" />';
	echo '<br />
		<br />
		<br />
		<table cellpadding="2">
		<thead>
			<tr>
	  		<th class="SortedColumn">' . __('Code') . '</th>
			<th class="SortedColumn">' . __('Supplier Name') . '</th>
			<th class="SortedColumn">' . __('Currency') . '</th>
			<th class="SortedColumn">' . __('Address 1') . '</th>
			<th class="SortedColumn">' . __('Address 2') . '</th>
			<th class="SortedColumn">' . __('Address 3') . '</th>
			<th class="SortedColumn">' . __('Address 4') . '</th>
			<th class="SortedColumn">' . __('Telephone') . '</th>
			<th class="SortedColumn">' . __('Email') . '</th>
			<th class="SortedColumn">' . __('URL') . '</th>
			</tr>
		</thead>
		<tbody>';

	$RowIndex = 0;
	if (DB_num_rows($Result) <> 0) {
		DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	while (($MyRow = DB_fetch_array($Result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		echo '<tr class="striped_row">
				<td><input type="submit" name="Select" value="'.$MyRow['supplierid'].'" /></td>
				<td>' . $MyRow['suppname'] . '</td>
				<td>' . $MyRow['currcode'] . '</td>
				<td>' . $MyRow['address1'] . '</td>
				<td>' . $MyRow['address2'] . '</td>
				<td>' . $MyRow['address3'] . '</td>
				<td>' . $MyRow['address4'] . '</td>
				<td>' . $MyRow['telephone'] . '</td>
				<td><a href="mailto://'.$MyRow['email'].'">' . $MyRow['email']. '</a></td>
				<td><a href="'.$MyRow['url'].'"target="_blank">' . $MyRow['url']. '</a></td>
			</tr>';
		$RowIndex = $RowIndex + 1;
		//end of page full new headings if
	}
	//end of while loop
	echo '</tbody></table>';
}
//end if results to show
if (isset($ListPageMax) and $ListPageMax > 1) {
	echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': </p>';
	echo '<select name="PageOffset">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
		} else {
			echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
		}
		$ListPage++;
	}
	echo '</select>
		<input type="submit" name="Go" value="' . __('Go') . '" />
		<input type="submit" name="Previous" value="' . __('Previous') . '" />
		<input type="submit" name="Next" value="' . __('Next') . '" />';
	echo '<br />';
}
echo '</div>
      </form>';
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['SupplierID']) and $_SESSION['SupplierID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		if ($lat == 0) {
			echo '<br />';
			echo '<div class="centre">' . __('Mapping is enabled, but no Mapping data to display for this Supplier.') . '</div>';
		} else {

			echo '<br />
				<table class="selection">
				<thead>
					<tr>
						<th>', __('Supplier Mapping'), '</th>
					</tr>
				</thead><tbody>
					<tr>
						<td class="centre">', __('Mapping is enabled, Map will display below.'), '</td>
					</tr><tr>
						<td class="centre">', // Mapping:
							'<div class="centre" id="map" style="width: ', $map_width, 'px; height: ', $map_height, 'px"></div>
						</td>
					</tr>
				<tbody></table>';
		}
	}
	// Extended Info only if selected in Configuration
	if ($_SESSION['Extended_SupplierInfo'] == 1) {
		if ($_SESSION['SupplierID'] != '') {
			$SQL = "SELECT suppliers.suppname,
							suppliers.lastpaid,
							suppliers.lastpaiddate,
							suppliersince,
							currencies.decimalplaces AS currdecimalplaces
					FROM suppliers INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
					WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
			$DataResult = DB_query($SQL);
			$MyRow = DB_fetch_array($DataResult);
			// Select some more data about the supplier
			$SQL = "SELECT SUM(ovamount) AS total FROM supptrans WHERE supplierno = '" . $_SESSION['SupplierID'] . "' AND (type = '20' OR type='21')";
			$Total1Result = DB_query($SQL);
			$Row = DB_fetch_array($Total1Result);
			echo '<br />';
			echo '<table width="45%" cellpadding="4">';
			echo '<tr><th style="width:33%" colspan="2">' . __('Supplier Data') . '</th></tr>';
			echo '<tr><td valign="top" class="select">'; /* Supplier Data */
			//echo "Distance to this Supplier: <b>TBA</b><br />";
			if ($MyRow['lastpaiddate'] == 0) {
				echo __('No payments yet to this supplier.') . '</td>
					<td valign="top" class="select"></td>
					</tr>';
			} else {
				echo __('Last Paid:') . '</td>
					<td valign="top" class="select"> <b>' . ConvertSQLDate($MyRow['lastpaiddate']) . '</b></td>
					</tr>';
			}
			echo '<tr>
					<td valign="top" class="select">' . __('Last Paid Amount:') . '</td>
					<td valign="top" class="select">  <b>' . locale_number_format($MyRow['lastpaid'], $MyRow['currdecimalplaces']) . '</b></td></tr>';
			echo '<tr>
					<td valign="top" class="select">' . __('Supplier since:') . '</td>
					<td valign="top" class="select"> <b>' . ConvertSQLDate($MyRow['suppliersince']) . '</b></td>
					</tr>';
			echo '<tr>
					<td valign="top" class="select">' . __('Total Spend with this Supplier:') . '</td>
					<td valign="top" class="select"> <b>' . locale_number_format($Row['total'], $MyRow['currdecimalplaces']) . '</b></td>
					</tr>';
			echo '</table>';
		}
	}
}

include('includes/footer.php');
