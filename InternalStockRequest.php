<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineStockRequestClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Create an Internal Materials Request');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

if (isset($_POST['DispatchDate'])){$_POST['DispatchDate'] = ConvertSQLDate($_POST['DispatchDate']);}

if (isset($_GET['New'])) {
	unset($_SESSION['Transfer']);
	$_SESSION['Request'] = new StockRequest();
}

if (isset($_POST['Update'])) {
	$InputError = 0;
	if ($_POST['Department'] == '') {
		prnMsg(__('You must select a Department for the request'), 'error');
		$InputError = 1;
	}
	if ($_POST['Location'] == '') {
		prnMsg(__('You must select a Location to request the items from'), 'error');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$_SESSION['Request']->Department = $_POST['Department'];
		$_SESSION['Request']->Location = $_POST['Location'];
		$_SESSION['Request']->DispatchDate = $_POST['DispatchDate'];
		$_SESSION['Request']->Narrative = $_POST['Narrative'];
	}
}

if (isset($_POST['Edit'])) {
	$_SESSION['Request']->LineItems[$_POST['LineNumber']]->Quantity = $_POST['Quantity'];
}

if (isset($_GET['Delete'])) {
	unset($_SESSION['Request']->LineItems[$_GET['Delete']]);
	echo '<br />';
	prnMsg(__('The line was successfully deleted'), 'success');
	echo '<br />';
}

foreach ($_POST as $key => $Value) {
	if (mb_strstr($key, 'StockID')) {
		$Index = mb_substr($key, 7);
		if (filter_number_format($_POST['Quantity' . $Index]) > 0) {
			$StockID = $Value;
			$ItemDescription = $_POST['ItemDescription' . $Index];
			$DecimalPlaces = $_POST['DecimalPlaces' . $Index];
			$NewItem_array[$StockID] = filter_number_format($_POST['Quantity' . $Index]);
			$_POST['Units' . $StockID] = $_POST['Units' . $Index];
			$_SESSION['Request']->AddLine($StockID, $ItemDescription, $NewItem_array[$StockID], $_POST['Units' . $StockID], $DecimalPlaces);
		}
	}
}

if (isset($_POST['Submit']) and (!empty($_SESSION['Request']->LineItems))) {

	DB_Txn_Begin();
	$InputError = 0;
	if ($_SESSION['Request']->Department == '') {
		prnMsg(__('You must select a Department for the request'), 'error');
		$InputError = 1;
	}
	if ($_SESSION['Request']->Location == '') {
		prnMsg(__('You must select a Location to request the items from'), 'error');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$RequestNo = GetNextTransNo(38);
		$HeaderSQL = "INSERT INTO stockrequest (dispatchid,
											loccode,
											departmentid,
											despatchdate,
											narrative,
											initiator)
										VALUES(
											'" . $RequestNo . "',
											'" . $_SESSION['Request']->Location . "',
											'" . $_SESSION['Request']->Department . "',
											'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
											'" . $_SESSION['Request']->Narrative . "',
											'" . $_SESSION['UserID'] . "')";
		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The request header record could not be inserted because');
		$Result = DB_query($HeaderSQL, $ErrMsg, '', true);

		foreach ($_SESSION['Request']->LineItems as $LineItems) {
			$LineSQL = "INSERT INTO stockrequestitems (dispatchitemsid,
													dispatchid,
													stockid,
													quantity,
													decimalplaces,
													uom)
												VALUES(
													'" . $LineItems->LineNumber . "',
													'" . $RequestNo . "',
													'" . $LineItems->StockID . "',
													'" . $LineItems->Quantity . "',
													'" . $LineItems->DecimalPlaces . "',
													'" . $LineItems->UOM . "')";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The request line record could not be inserted because');
			$Result = DB_query($LineSQL, $ErrMsg, '', true);
		}

		$EmailSQL = "SELECT email
					FROM www_users, departments
					WHERE departments.authoriser = www_users.userid
						AND departments.departmentid = '" . $_SESSION['Request']->Department . "'";
		$EmailResult = DB_query($EmailSQL);
		if ($MyEmail = DB_fetch_array($EmailResult)) {
			$ConfirmationText = __('An internal stock request has been created and is waiting for your authoritation');
			$EmailSubject = __('Internal Stock Request needs your authoritation');
			SendEmailFromWebERP($SysAdminEmail,
								$MyEmail['email'],
								$EmailSubject,
								$ConfirmationText,
								'',
								false);
		}
	}
	DB_Txn_Commit();
	prnMsg(__('The internal stock request has been entered and now needs to be authorised'), 'success');
	echo '<br /><div class="centre"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?New=Yes">', __('Create another request'), '</a></div>';
	include('includes/footer.php');
	unset($_SESSION['Request']);
	exit();
} elseif (isset($_POST['Submit'])) {
	prnMsg(__('There are no items added to this request'), 'error');
}

echo '<p class="page_title_text"><img src="', $RootPath, '/css/', $Theme, '/images/supplier.png" title="', __('Dispatch'), '" alt="" />', ' ', $Title, '</p>';

if (isset($_GET['Edit'])) {
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<fieldset>';
	echo '<legend>', __('Edit the Request Line'), '</legend>';
	echo '<field>
			<label>', __('Line number'), '</label>
			<fieldtext>', $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber, '</fieldtext>
		</field>
		<field>
			<label>', __('Stock Code'), '</label>
			<fieldtext>', $_SESSION['Request']->LineItems[$_GET['Edit']]->StockID, '</fieldtext>
		</field>
		<field>
			<label>', __('Item Description'), '</label>
			<fieldtext>', $_SESSION['Request']->LineItems[$_GET['Edit']]->ItemDescription, '</fieldtext>
		</field>
		<field>
			<label>', __('Unit of Measure'), '</label>
			<fieldtext>', $_SESSION['Request']->LineItems[$_GET['Edit']]->UOM, '</fieldtext>
		</field>
		<field>
			<label>', __('Quantity Requested'), '</label>
			<fieldtext><input type="text" class="number" name="Quantity" value="', locale_number_format($_SESSION['Request']->LineItems[$_GET['Edit']]->Quantity, $_SESSION['Request']->LineItems[$_GET['Edit']]->DecimalPlaces), '" /></fieldtext>
		</field>';
	echo '<input type="hidden" name="LineNumber" value="', $_SESSION['Request']->LineItems[$_GET['Edit']]->LineNumber, '" />';
	echo '</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Edit" value="', __('Update Line'), '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
	<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
	<fieldset>
		<legend>', __('Internal Stock Request Details'), '</legend>
	<field>
		<label for="Department">' . __('Department') . ':</label>';
if ($_SESSION['AllowedDepartment'] == 0) {
	// any internal department allowed
	$SQL = "SELECT departmentid,
				description
			FROM departments
			ORDER BY description";
} else {
	// just 1 internal department allowed
	$SQL = "SELECT departmentid,
				description
			FROM departments
			WHERE departmentid = '" . $_SESSION['AllowedDepartment'] . "'
			ORDER BY description";
}
$Result = DB_query($SQL);
echo '<select name="Department">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['Request']->Department) and $_SESSION['Request']->Department == $MyRow['departmentid']) {
		echo '<option selected value="', $MyRow['departmentid'], '">', htmlspecialchars($MyRow['description'], ENT_QUOTES, 'UTF-8'), '</option>';
	} else {
		echo '<option value="', $MyRow['departmentid'], '">', htmlspecialchars($MyRow['description'], ENT_QUOTES, 'UTF-8'), '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Location">' . __('Location from which to request stock') . ':</label>';
$SQL = "SELECT locations.loccode,
			locationname
		FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" . $_SESSION['UserID'] . "' AND locationusers.canupd=1
		WHERE internalrequest = 1
		ORDER BY locationname";

$Result = DB_query($SQL);
echo '<select name="Location">
		<option value="">', __('Select a Location'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['Request']->Location) and $_SESSION['Request']->Location == $MyRow['loccode']) {
		echo '<option selected value="', $MyRow['loccode'], '">', $MyRow['loccode'], ' - ', htmlspecialchars($MyRow['locationname'], ENT_QUOTES, 'UTF-8'), '</option>';
	} else {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['loccode'], ' - ', htmlspecialchars($MyRow['locationname'], ENT_QUOTES, 'UTF-8'), '</option>';
	}
}
echo '</select>
	</field>
	<field>
		<label for="DispatchDate">', __('Date when required'), ':</label>
		<input type="date" name="DispatchDate" maxlength="10" size="11" value="', FormatDateForSQL($_SESSION['Request']->DispatchDate), '" />
	</field>
	<field>
		<label for="Narrative">', __('Narrative'), ':</label>
		<textarea name="Narrative" cols="30" rows="5">', $_SESSION['Request']->Narrative, '</textarea>
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="Update" value="', __('Update'), '" />
	</div>
	</form>';

if (!isset($_SESSION['Request']->Location)) {
	include('includes/footer.php');
	exit();
}

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
	<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
	<table class="selection">
	<thead>
	<tr>
		<th colspan="7"><h4>', __('Details of Items Requested'), '</h4></th>
	</tr>
	<tr>
		<th>', __('Line Number'), '</th>
		<th class="SortedColumn">', __('Item Code'), '</th>
		<th class="SortedColumn">', __('Item Description'), '</th>
		<th class="SortedColumn">', __('Quantity Required'), '</th>
		<th>', __('UOM'), '</th>
		</tr>
	</thead>
	<tbody>';

if (isset($_SESSION['Request']->LineItems)) {
	foreach ($_SESSION['Request']->LineItems as $LineItems) {
		echo '<tr class="striped_row">
				<td>', $LineItems->LineNumber, '</td>
				<td>', $LineItems->StockID, '</td>
				<td>', $LineItems->ItemDescription, '</td>
				<td class="number">', locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces), '</td>
				<td>', $LineItems->UOM, '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Edit=', urlencode($LineItems->LineNumber), '">', __('Edit'), '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?Delete=', urlencode($LineItems->LineNumber), '">', __('Delete'), '</a></td>
			</tr>';
	}
}

echo '</tbody>
	</table>
	<div class="centre">
		<input type="submit" name="Submit" value="', __('Submit'), '" />
	</div>
    </form>';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $Theme, '/images/magnifier.png" title="', __('Search'), '" alt="" />', ' ', __('Search for Inventory Items'), '</p>
	<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">
	<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$SQL = "SELECT stockcategory.categoryid,
				stockcategory.categorydescription
		FROM stockcategory
		INNER JOIN internalstockcatrole
			ON stockcategory.categoryid = internalstockcatrole.categoryid
		WHERE internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
			ORDER BY stockcategory.categorydescription";

$Result1 = DB_query($SQL);
if (DB_num_rows($Result1) == 0) {
	echo '<p class="bad">', __('Problem Report'), ':<br />', __('There are no stock categories currently defined please use the link below to set them up'), '</p>
		<a href="', $RootPath, '/StockCategories.php">', __('Define Stock Categories'), '</a>';
	exit();
}

echo '<fieldset>
		<legend>', __('Stock Selection'), '</legend>
		<field>
			<label for="StockCat">' . __('In Stock Category') . ':</label>
			<select name="StockCat">';

if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = 'All';
}

if ($_POST['StockCat'] == 'All') {
	echo '<option selected value="All">' . __('All Authorized') . '</option>';
} else {
	echo '<option value="All">' . __('All Authorized') . '</option>';
}

while ($MyRow1 = DB_fetch_array($Result1)) {
	if ($MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">', __('Enter partial'), '<b> ', __('Description'), '</b>:</label>';

if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
}

echo '</field>';

echo '<field>
		<label>', '<b>' . __('OR') . ' </b>' . __('Enter partial'), ' <b>', __('Stock Code'), '</b>:</label>';

if (isset($_POST['StockCode'])) {
	echo '<input type="text" autofocus="autofocus" name="StockCode" value="', $_POST['StockCode'], '" size="15" maxlength="18" />';
} else {
	echo '<input type="text" name="StockCode" size="15" maxlength="18" />';
}

echo '</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="Search" value="', __('Search Now'), '" />
	</div>
	</form>';

if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	if ($_POST['Keywords'] != '' and $_POST['StockCode'] == '') {
		prnMsg(__('Order Item description has been used in search'), 'warn');
	} elseif ($_POST['StockCode'] != '' and $_POST['Keywords'] == '') {
		prnMsg(__('Stock Code has been used in search'), 'warn');
	} elseif ($_POST['Keywords'] == '' and $_POST['StockCode'] == '') {
		prnMsg(__('Stock Category has been used in search'), 'warn');
	}

	if (isset($_POST['Keywords']) and mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units as stockunits,
							stockmaster.decimalplaces
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN internalstockcatrole
						ON stockcategory.categoryid = internalstockcatrole.categoryid
					WHERE stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units as stockunits,
							stockmaster.decimalplaces
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN internalstockcatrole
						ON stockcategory.categoryid = internalstockcatrole.categoryid
					WHERE stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} elseif (mb_strlen($_POST['StockCode']) > 0) {

		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		$SearchString = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units as stockunits,
							stockmaster.decimalplaces
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN internalstockcatrole
						ON stockcategory.categoryid = internalstockcatrole.categoryid
					WHERE stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units as stockunits,
							stockmaster.decimalplaces
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN internalstockcatrole
						ON stockcategory.categoryid = internalstockcatrole.categoryid
					WHERE stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units as stockunits,
							stockmaster.decimalplaces
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN internalstockcatrole
						ON stockcategory.categoryid = internalstockcatrole.categoryid
					WHERE stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units as stockunits,
							stockmaster.decimalplaces
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN internalstockcatrole
						ON stockcategory.categoryid = internalstockcatrole.categoryid
					WHERE stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}
	}

	if (isset($_POST['Next'])) {
		$Offset = $_POST['NextList'];
	}
	if (isset($_POST['Previous'])) {
		$Offset = $_POST['PreviousList'];
	}
	if (!isset($Offset) or $Offset < 0) {
		$Offset = 0;
	}
	$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'] . ' OFFSET ' . ($_SESSION['DisplayRecordsMax'] * $Offset);

	$ErrMsg = __('There is a problem selecting the part records to display because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('There are no products available meeting the criteria specified'), 'info');
	}
	if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']) {
		$Offset = 0;
	}

} //end of if search
if (isset($SearchResult)) {
	$j = 1;
	echo '<br />
		<div class="page_help_text">', __('Select an item by entering the quantity required.  Click Order when ready.'), '</div>
		<br />
		<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post" id="orderform">
		<div>
		<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
		<table class="table1">
		<thead>
		<tr>
			<td>
					<input type="hidden" name="PreviousList" value="', ($Offset - 1), '" />
					<input tabindex="', ($j + 8), '" type="submit" name="Previous" value="', __('Previous'), '" /></td>
				<td class="centre" colspan="6">
				<input type="hidden" name="order_items" value="1" />
					<input tabindex="', ($j + 9), '" type="submit" value="', __('Add to Requisition'), '" /></td>
			<td>
					<input type="hidden" name="NextList" value="', ($Offset + 1), '" />
					<input tabindex="', ($j + 10), '" type="submit" name="Next" value="', __('Next'), '" /></td>
			</tr>
			<tr>
				<th class="SortedColumn">', __('Code'), '</th>
				<th class="SortedColumn">', __('Description'), '</th>
				<th>', __('Units'), '</th>
				<th class="SortedColumn">', __('On Hand'), '</th>
				<th class="SortedColumn">', __('On Demand'), '</th>
				<th class="SortedColumn">', __('On Order'), '</th>
				<th class="SortedColumn">', __('Available'), '</th>
				<th class="SortedColumn">', __('Quantity'), '</th>
			</tr>
		</thead>
		<tbody>';

	$ImageSource = __('No Image');

	$i = 0;
	while ($MyRow = DB_fetch_array($SearchResult)) {
		$DecimalPlaces = $MyRow['decimalplaces'];

		$QOH = GetQuantityOnHand($MyRow['stockid'], $_SESSION['Request']->Location);

		$DemandQty = GetDemand($MyRow['stockid'], $_SESSION['Request']->Location);

		$OnOrder = GetQuantityOnOrder($MyRow['stockid'], 'ALL');

		$Available = $QOH - $DemandQty + $OnOrder;

		echo '<tr class="striped_row">
				<td>', $MyRow['stockid'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['stockunits'], '</td>
				<td class="number">', locale_number_format($QOH, $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($DemandQty, $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($OnOrder, $DecimalPlaces), '</td>
				<td class="number">', locale_number_format($Available, $DecimalPlaces), '</td>
				<td><input class="number" ', ($i == 0 ? 'autofocus="autofocus"' : ''), ' tabindex="', ($j + 7), '" type="text" size="6" name="Quantity', $i, '" value="0" />
				<input type="hidden" name="StockID', $i, '" value="', $MyRow['stockid'], '" />
				</td>
			</tr>
			<input type="hidden" name="DecimalPlaces', $i, '" value="', $MyRow['decimalplaces'], '" />
			<input type="hidden" name="ItemDescription', $i, '" value="', $MyRow['description'], '" />
			<input type="hidden" name="Units', $i, '" value="', $MyRow['stockunits'], '" />';
		$i++;
	}
	#end of while loop
	echo '</tbody>
		<tfoot>
			<tr>
				<td><input type="hidden" name="PreviousList" value="', ($Offset - 1), '" />
					<input tabindex="', ($j + 7), '" type="submit" name="Previous" value="', __('Previous'), '" /></td>
			<td class="centre" colspan="6"><input type="hidden" name="order_items" value="1" />
					<input tabindex="', ($j + 8), '" type="submit" value="', __('Add to Requisition'), '" /></td>
				<td><input type="hidden" name="NextList" value="', ($Offset + 1), '" />
					<input tabindex="', ($j + 9), '" type="submit" name="Next" value="', __('Next'), '" /></td>
			</tr>
		</tfoot>
		</table>
       </div>
       </form>';
} #end if SearchResults to show
//*********************************************************************************************************
include('includes/footer.php');
