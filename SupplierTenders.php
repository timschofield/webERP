<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineOfferClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Tendering');
$ViewTopic = 'SupplierTenders';
$BookMark = '';
include('includes/header.php');

include('includes/ImageFunctions.php');

$Maximum_Number_Of_Parts_To_Show=50;

if (isset($_GET['TenderType'])) {
	$_POST['TenderType']=$_GET['TenderType'];
}

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other supplier tender sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (!isset($_POST['SupplierID'])) {
	$SQL="SELECT supplierid FROM www_users WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_array($Result);
	if ($MyRow['supplierid']=='') {
		prnMsg(__('This functionality can only be accessed via a supplier login.'), 'warning');
		include('includes/footer.php');
		exit();
	} else {
		$_POST['SupplierID']=$MyRow['supplierid'];
	}
}

if (isset($_GET['Delete'])) {
	$_POST['SupplierID']=$_SESSION['offer'.$identifier]->SupplierID;
	$_POST['TenderType']=$_GET['Type'];
	$_SESSION['offer'.$identifier]->remove_from_offer($_GET['Delete']);
}

$SQL="SELECT suppname,
			currcode
		FROM suppliers
		WHERE supplierid='" . $_POST['SupplierID'] . "'";
$Result = DB_query($SQL);
$MyRow=DB_fetch_array($Result);
$Supplier=$MyRow['suppname'];
$Currency=$MyRow['currcode'];

if (isset($_POST['Confirm'])) {
	$_SESSION['offer'.$identifier]->Save();
	$_SESSION['offer'.$identifier]->EmailOffer();
	$SQL="UPDATE tendersuppliers
			SET responded=1
			WHERE supplierid='" . $_SESSION['offer'.$identifier]->SupplierID . "'
			AND tenderid='" . $_SESSION['offer'.$identifier]->TenderID . "'";
	$Result = DB_query($SQL);
}

if (isset($_POST['Process'])) {
	if (isset($_SESSION['offer'.$identifier])) {
		unset($_SESSION['offer'.$identifier]);
	}
	$_SESSION['offer'.$identifier]=new Offer($_POST['SupplierID']);
	$_SESSION['offer'.$identifier]->TenderID=$_POST['Tender'];
	$_SESSION['offer'.$identifier]->CurrCode=$Currency;
	$LineNo=0;
	foreach ($_POST as $key=>$Value) {
		if (mb_substr($key,0,7)=='StockID') {
			$Index = mb_substr($key,7,mb_strlen($key)-7);
			$ItemCode=$Value;
			$Quantity=$_POST['Qty'.$Index];
			$Price=$_POST['Price'.$Index];
			$_SESSION['offer'.$identifier]->add_to_offer(
				$LineNo,
				$ItemCode,
				$Quantity,
				$_POST['ItemDescription'.$Index],
				$Price,
				$_POST['UOM'.$Index],
				$_POST['DecimalPlaces'.$Index],
				$_POST['RequiredByDate'.$Index]);
			$LineNo++;
		}
	}
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Tenders') . '" alt="" />' . ' ' . __('Confirm the Response For Tender') . ' ' . $_SESSION['offer'.$identifier]->TenderID  . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	echo '<input type="hidden" name="TenderType" value="3" />';
	$LocationSQL="SELECT tenderid,
						locations.locationname,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						telephone
					FROM tenders
					INNER JOIN locations
					ON tenders.location=locations.loccode
					WHERE closed=0
					AND tenderid='".$_SESSION['offer'.$identifier]->TenderID."'";
	$LocationResult = DB_query($LocationSQL);
	$MyLocationRow=DB_fetch_row($LocationResult);
	$CurrencySQL="SELECT decimalplaces from currencies WHERE currabrev='".$_SESSION['offer'.$identifier]->CurrCode."'";
	$CurrencyResult = DB_query($CurrencySQL);
	$CurrencyRow=DB_fetch_array($CurrencyResult);
	echo '<tr>
			<td valign="top" style="background-color:#cccce5">' . __('Deliver To') . ':</td>
			<td valign="top" style="background-color:#cccce5">';
	for ($i=1; $i<8; $i++) {
		if ($MyLocationRow[$i]!='') {
			echo $MyLocationRow[$i] . '<br />';
		}
	}
	echo '</td>';
	echo '<th colspan="8" style="vertical-align:top"><font size="2" color="#616161">' . __('Tender Number') . ': ' .$_SESSION['offer'.$identifier]->TenderID . '</font></th>';
	echo '<input type="hidden" value="' . $_SESSION['offer'.$identifier]->TenderID . '" name="Tender" />';
	echo '<tr>
			<th>' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '<br />' . __('Item Code') . '</th>
			<th>' . __('Item Description') . '</th>
			<th>' . __('Quantity') . '<br />' . __('Offered') . '</th>
			<th>' . $Supplier . '<br />' . __('Units of Measure') . '</th>
			<th>' . __('Currency') . '</th>
			<th>' . $Supplier . '<br />' . __('Price') . '</th>
			<th>' . __('Line Value') . '</th>
			<th>' . __('Delivery By') . '</th>
		</tr>';

	foreach ($_SESSION['offer'.$identifier]->LineItems as $LineItem)  {
		echo '<tr><td>' . $LineItem->StockID . '</td>';
		echo '<td>' . $LineItem->ItemDescription . '</td>';
		echo '<td class="number"> ' .locale_number_format($LineItem->Quantity, $LineItem->DecimalPlaces) . '</td>';
		echo '<td>' . $LineItem->Units . '</td>';
		echo '<td>' . $_SESSION['offer'.$identifier]->CurrCode . '</td>';
		echo '<td class="number">' . locale_number_format($LineItem->Price, $CurrencyRow['decimalplaces']) . '</td>';
		echo '<td class="number">' . locale_number_format($LineItem->Price*$LineItem->Quantity,$CurrencyRow['decimalplaces']) . '</td>';
		echo '<td>' . $LineItem->ExpiryDate . '</td>';
	}
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="Confirm" value="' . __('Confirm and Send Email') . '" />
			<br />
			<br />
			<input type="reset" name="Cancel" value="' . __('Cancel Offer') . '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

/* If the supplierID is set then it must be a login from the supplier but if nothing else is
 * set then the supplier must have just logged in so show them the choices.
 */
if (isset($_POST['SupplierID']) AND empty($_POST['TenderType']) AND empty($_POST['Search']) AND empty($_POST['NewItem']) AND empty($_GET['Delete'])) {
	if (isset($_SESSION['offer'.$identifier])) {
		unset($_SESSION['offer'.$identifier]);
	}
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Tenders') . '" alt="" />' . ' ' . __('Create or View Offers from') . ' '.$Supplier . '</p>';
	echo '<fieldset>';
	echo'<field>
			<label for="TenderType">' . __('Select option for tendering') . '</label>
			<select name="TenderType">
				<option value="1">' . __('View or Amend outstanding offers from').' '.$Supplier  . '</option>
				<option value="2">' . __('Create a new offer from').' '.$Supplier  . '</option>
				<option value="3">' . __('View any open tenders without an offer from').' '.$Supplier  . '</option>
			</select>
		</field>';
	echo '<input type="hidden" name="SupplierID" value="'.$_POST['SupplierID'].'" />';
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Select') . '" />
		</div>
		</fieldset>
		</form>';
}

if (isset($_POST['NewItem']) AND !isset($_POST['Refresh'])) {
	foreach ($_POST as $key => $Value) {
		if (mb_substr($key,0,7)=='StockID') {
			$Index = mb_substr($key,7,mb_strlen($key)-7);
			$StockID=$Value;
			$Quantity=filter_number_format($_POST['Qty'.$Index]);
			$Price=filter_number_format($_POST['Price'.$Index]);
			$UOM=$_POST['uom'.$Index];
			if (isset($UOM) AND $Quantity>0) {
				$SQL="SELECT description, decimalplaces FROM stockmaster WHERE stockid='".$StockID."'";
				$Result = DB_query($SQL);
				$MyRow=DB_fetch_array($Result);
				$_SESSION['offer'.$identifier]->add_to_offer($_SESSION['offer'.$identifier]->LinesOnOffer,
												$StockID,
												$Quantity,
												$MyRow['description'],
												$Price,
												$UOM,
												$MyRow['decimalplaces'],
												DateAdd(date($_SESSION['DefaultDateFormat']),'m',3));
				unset($UOM);
			}
		}
	}
}

if (isset($_POST['Refresh']) AND !isset($_POST['NewItem'])) {
	foreach ($_POST as $key => $Value) {
		if (mb_substr($key,0,7)=='StockID') {
			$Index = mb_substr($key,7,mb_strlen($key)-7);
			$StockID=$Value;
			$Quantity=filter_number_format($_POST['Qty'.$Index]);
			$Price=filter_number_format($_POST['Price'.$Index]);
			$ExpiryDate=$_POST['expirydate'.$Index];
		}
		if (isset($ExpiryDate)) {
			$_SESSION['offer'.$identifier]->update_offer_item(
				$Index,
				$Quantity,
				$Price,
				$ExpiryDate);
			unset($ExpiryDate);
		}
	}
}

if (isset($_POST['Update'])) {
	foreach ($_POST as $key => $Value) {
		if (mb_substr($key,0,3)=='Qty') {
			$LineNo=mb_substr($key,3);
			$Quantity=$Value;
		}
		if (mb_substr($key,0,5)=='Price') {
			$Price=$Value;
		}
		if (mb_substr($key,0,10)=='expirydate') {
			$ExpiryDate=$Value;
		}
		if (isset($ExpiryDate)) {
			$_SESSION['offer'.$identifier]->update_offer_item(
				$LineNo,
				$Quantity,
				$Price,
				$ExpiryDate);
			unset($ExpiryDate);
		}
	}
	$_SESSION['offer'.$identifier]->Save('Yes');
	$_SESSION['offer'.$identifier]->EmailOffer();
	unset($_SESSION['offer'.$identifier]);
	include('includes/footer.php');
	exit();
}

if (isset($_POST['Save'])) {
	foreach ($_POST as $key => $Value) {
		if (mb_substr($key,0,3)=='Qty') {
			$LineNo=mb_substr($key,3);
			$Quantity=$Value;
		}
		if (mb_substr($key,0,5)=='Price') {
			$Price=$Value;
		}
		if (mb_substr($key,0,10)=='expirydate') {
			$ExpiryDate=$Value;
		}
		if (isset($ExpiryDate)) {
			$_SESSION['offer'.$identifier]->update_offer_item(
				$LineNo,
				$Quantity,
				$Price,
				$ExpiryDate);
			unset($ExpiryDate);
		}
	}
	$_SESSION['offer'.$identifier]->Save();
	$_SESSION['offer'.$identifier]->EmailOffer();
	unset($_SESSION['offer'.$identifier]);
	include('includes/footer.php');
	exit();
}

/*The supplier has chosen option 1
 */
if (isset($_POST['TenderType']) AND $_POST['TenderType']==1 AND !isset($_POST['Refresh']) AND !isset($_GET['Delete'])) {
	$SQL="SELECT offers.offerid,
				offers.stockid,
				stockmaster.description,
				offers.quantity,
				offers.uom,
				offers.price,
				offers.expirydate,
				stockmaster.decimalplaces
			FROM offers
			INNER JOIN stockmaster
				ON offers.stockid=stockmaster.stockid
			WHERE offers.supplierid='" . $_POST['SupplierID'] . "'
				AND offers.expirydate >= CURRENT_DATE";
	$Result = DB_query($SQL);
	$_SESSION['offer'.$identifier]=new Offer($_POST['SupplierID']);
	$_SESSION['offer'.$identifier]->CurrCode=$Currency;
	while ($MyRow=DB_fetch_array($Result)) {
		$_SESSION['offer'.$identifier]->add_to_offer(	$MyRow['offerid'],
														$MyRow['stockid'],
														$MyRow['quantity'],
														$MyRow['description'],
														$MyRow['price'],
														$MyRow['uom'],
														$MyRow['decimalplaces'],
														ConvertSQLDate($MyRow['expirydate']));
	}
}

if (isset($_POST['TenderType']) and $_POST['TenderType']!=3 and isset($_SESSION['offer'.$identifier]) and $_SESSION['offer'.$identifier]->LinesOnOffer>0 or isset($_POST['Update'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Items to offer from').' '.$Supplier  . '</p>';
	echo '<table>
			<tr>
				<th class="assending">' . __('Stock ID') . '</th>
				<th class="assending">' . __('Description') . '</th>
				<th class="assending">' . __('Quantity') . '</th>
				<th class="assending">' . __('UOM') . '</th>
				<th class="assending">' . __('Price').' ('.$Currency.')</th>
				<th class="assending">' . __('Line Total').' ('.$Currency.')</th>
				<th class="assending">' . __('Expiry Date') . '</th>
			</tr>';

	foreach ($_SESSION['offer'.$identifier]->LineItems as $LineItems) {
		if ($LineItems->Deleted==false) {
			if ($LineItems->ExpiryDate < date('Y-m-d')) {
				echo '<tr style="background-color:#F7A9A9">';
			} else {
				echo '<tr class="striped_row">';
			}

			echo '<input type="hidden" name="StockID'.$LineItems->LineNo.'" value="'.$LineItems->StockID.'" />';
			echo '<td>' . $LineItems->StockID . '</td>
					<td>' . $LineItems->ItemDescription . '</td>
					<td><input type="text" class="number" required="true" name="Qty'.$LineItems->LineNo.'" value="'.locale_number_format($LineItems->Quantity,$LineItems->DecimalPlaces).'" /></td>
					<td>' . $LineItems->Units . '</td>
					<td><input type="text" class="number" required="true" name="Price'.$LineItems->LineNo.'" value="'.locale_number_format($LineItems->Price,2,'.','').'" /></td>
					<td class="number">' . locale_number_format($LineItems->Price*$LineItems->Quantity,2) . '</td>
					<td><input maxlength="10" size="11" type="date" required="true" name="expirydate'.$LineItems->LineNo.'" value="'.$LineItems->ExpiryDate.'" /></td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier.'&Delete=' . $LineItems->LineNo . '&Type=' . $_POST['TenderType'] . '">' . __('Remove') . '</a></td>
				</tr>';
		}
	}
	echo '</table>';
	echo '<input type="hidden" name="TenderType" value="'.$_POST['TenderType'].'" />';
	if ($_POST['TenderType']==1) {
		echo '<br />
				<div class="centre">
					<input type="submit" name="Update" value="Update offer" />
					<input type="submit" name="Refresh" value="Refresh screen" />
				</div>';
	} else if ($_POST['TenderType']==2) {
		echo '<br />
				<div class="centre">
					<input type="submit" name="Save" value="Save offer" />
					<input type="submit" name="Refresh" value="Refresh screen" />
				</div>';
	}
	echo '</form>';
}

/*The supplier has chosen option 2
 */
if (isset($_POST['TenderType'])
	AND $_POST['TenderType']==2
	AND !isset($_POST['Search'])
	OR isset($_GET['Delete'])) {

	if (!isset($_SESSION['offer'.$identifier])) {
		$_SESSION['offer'.$identifier]=new Offer($_POST['SupplierID']);
	}
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for Inventory Items') . '</p>';

	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<p><font size="4" color="red">' . __('Problem Report') . ':</font><br />' .
			__('There are no stock categories currently defined please use the link below to set them up');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a></p>';
		exit();
	}
	echo '<fieldset>
			<legend class="search">', __('Stock Item Search'), '</legend>
			<field>
				<label for="StockCat">' . __('In Stock Category') . ':</label>';
	echo '<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="Keywards">' . __('Enter partial') . '<b> ' . __('Description') . '</b>:</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
	}
	echo '<input type="hidden" name="TenderType" value="'.$_POST['TenderType'].'" />';
	echo '<input type="hidden" name="SupplierID" value="'.$_POST['SupplierID'].'" />';
	echo '</field>';

	echo '<field>
			<label for="StockCode">'. '<b>' . __('OR') . ' </b>' . __('Enter partial') . ' <b>' . __('Stock Code') . '</b>:</label>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" autofocus="autofocus" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" autofocus="autofocus" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
		</div>
		</form>';
}

/*The supplier has chosen option 3
 */
if (isset($_POST['TenderType'])
	AND $_POST['TenderType']==3
	AND !isset($_POST['Search'])
	OR isset($_GET['Delete'])) {

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Tenders') . '" alt="" />' . ' ' . __('Tenders Waiting For Offers') . '</p>';
	$SQL="SELECT DISTINCT tendersuppliers.tenderid,
				suppliers.currcode
			FROM tendersuppliers
			LEFT JOIN suppliers
			ON suppliers.supplierid=tendersuppliers.supplierid
			LEFT JOIN tenders
			ON tenders.tenderid=tendersuppliers.tenderid
			WHERE tendersuppliers.supplierid='" . $_POST['SupplierID'] . "'
			AND tenders.closed=0
			AND tendersuppliers.responded=0
			ORDER BY tendersuppliers.tenderid";
	$Result = DB_query($SQL);
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="13"><font size="3" color="#616161">' . __('Outstanding Tenders Waiting For Offer') . '</font></th>
		</tr>';
	while ($MyRow=DB_fetch_row($Result)) {
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="TenderType" value="3" />';
		$LocationSQL="SELECT tenderid,
							locations.locationname,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							telephone
						FROM tenders
						INNER JOIN locations
						ON tenders.location=locations.loccode
						WHERE closed=0
						AND tenderid='".$MyRow[0]."'";
		$LocationResult = DB_query($LocationSQL);
		$MyLocationRow=DB_fetch_row($LocationResult);
		echo '<tr>
				<td valign="top" style="background-color:#cccce5">' . __('Deliver To') . ':</td>
				<td valign="top" style="background-color:#cccce5">';
		for ($i=1; $i<8; $i++) {
			if ($MyLocationRow[$i]!='') {
				echo $MyLocationRow[$i] . '<br />';
			}
		}
		echo '</td>';
		echo '<th colspan="8" style="vertical-align:top"><font size="2" color="#616161">' . __('Tender Number') . ': ' .$MyRow[0] . '</font></th>';
		echo '<input type="hidden" value="' . $MyRow[0] . '" name="Tender" />';
		echo '<th><input type="submit" value="' . __('Process') . "\n" . __('Tender') . '" name="Process" /></th>
			</tr>';
		$ItemSQL="SELECT tenderitems.tenderid,
						tenderitems.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						purchdata.suppliers_partno,
						tenderitems.quantity,
						tenderitems.units,
						tenders.requiredbydate,
						purchdata.suppliersuom
					FROM tenderitems
					LEFT JOIN stockmaster
					ON tenderitems.stockid=stockmaster.stockid
					LEFT JOIN purchdata
					ON tenderitems.stockid=purchdata.stockid
					AND purchdata.supplierno='".$_POST['SupplierID']."'
					LEFT JOIN tenders
					ON tenders.tenderid=tenderitems.tenderid
					WHERE tenderitems.tenderid='" . $MyRow[0] . "'";
		$ItemResult = DB_query($ItemSQL);
		echo '<tr>
				<th>' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '<br />' . __('Item Code') . '</th>
				<th>' . __('Item Description') . '</th>
				<th>' . $Supplier . '<br />' . __('Item Code') . '</th>
				<th>' . __('Quantity') . '<br />' . __('Required') . '</th>
				<th>' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '<br />' . __('Units of Measure') . '</th>
				<th>' . __('Required By') . '</th>
				<th>' . __('Quantity') . '<br />' . __('Offered') . '</th>
				<th>' . $Supplier . '<br />' . __('Units of Measure') . '</th>
				<th>' . __('Currency') . '</th>
				<th>' . $Supplier . '<br />' . __('Price') . '</th>
				<th>' . __('Delivery By') . '</th>
			</tr>';
		$i=0;
		while ($MyItemRow=DB_fetch_array($ItemResult)) {
			echo '<tr>
					<td>' . $MyItemRow['stockid'] . '</td>
					<td>' . $MyItemRow['description'] . '</td>
					<input type="hidden" name="StockID'. $i . '" value="' . $MyItemRow['stockid'] . '" />
					<input type="hidden" name="ItemDescription'. $i . '" value="' . $MyItemRow['description'] . '" />
					<td>' . $MyItemRow['suppliers_partno'] . '</td>
					<td class="number">' . locale_number_format($MyItemRow['quantity'], $MyItemRow['decimalplaces']) . '</td>
					<td>' . $MyItemRow['units'] . '</td>
					<td>' . ConvertSQLDate($MyItemRow['requiredbydate']) . '</td>';

			if ($MyItemRow['suppliersuom']=='') {
				$MyItemRow['suppliersuom']=$MyItemRow['units'];
			}
			echo '<td><input type="text" class="number" title="'.__('Input must be in numeric format').'" size="10" name="Qty'. $i . '" value="' . locale_number_format($MyItemRow['quantity'], $MyItemRow['decimalplaces']) . '" /></td>
				<input type="hidden" name="UOM'. $i . '" value="' . $MyItemRow['units'] . '" />
				<input type="hidden" name="DecimalPlaces'. $i . '" value="' . $MyItemRow['decimalplaces'] . '" />
				<td>' . $MyItemRow['suppliersuom'] . '</td>
				<td>' . $MyRow[1] . '</td>
				<td><input type="text" class="number" title="'.__('Input must be in numeric format').'"  size="10" name="Price'. $i . '" value="0.00" /></td>
				<td><input type="date" name="RequiredByDate'. $i . '" maxlength="10" size="11" value="' . ConvertSQLDate($MyItemRow['requiredbydate']) . '" /></td>
				</tr>';
			$i++;
		}
		echo '</form>';
	}
	echo '</table>';
}

if (isset($_POST['Search'])){  /*ie seach for stock items */
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . __('Tenders') . '" alt="" />' . ' ' . __('Select items to offer from').' '.$Supplier  . '</p>';

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( __('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}
	}

	$ErrMsg = __('There is a problem selecting the part records to display because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult)==0){
		prnMsg( __('There are no products to display matching the criteria provided'),'warn');
	}
	if (DB_num_rows($SearchResult)==1){

		$MyRow=DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
		DB_data_seek($SearchResult,0);
	}

	if (isset($SearchResult)) {

		echo '<table cellpadding="1">';

		$TableHeader = '<tr>
						<th class="assending">' . __('Code')  . '</th>
						<th class="assending">' . __('Description') . '</th>
						<th class="assending">' . __('Units') . '</th>
						<th class="assending">' . __('Image') . '</th>
						<th class="assending">' . __('Quantity') . '</th>
						<th class="assending">' . __('Price') .' ('.$Currency.')</th>
					</tr>';
		echo $TableHeader;

		$i = 0;
		$PartsDisplayed=0;
		while ($MyRow=DB_fetch_array($SearchResult)) {

			$SupportedImgExt = array('png','jpg','jpeg');
			$Glob = (glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
			$ImageFile = reset($Glob);
			$ImageSource = GetImageLink($ImageFile, $MyRow['stockid'], 64, 64, "", "");

			$UOMsql="SELECT conversionfactor,
						suppliersuom,
						unitsofmeasure.unitname
					FROM purchdata
					LEFT JOIN unitsofmeasure
					ON purchdata.suppliersuom=unitsofmeasure.unitid
					WHERE supplierno='".$_POST['SupplierID']."'
					AND stockid='" . $MyRow['stockid'] . "'";

			$UOMresult=DB_query($UOMsql);
			if (DB_num_rows($UOMresult)>0) {
				$UOMrow=DB_fetch_array($UOMresult);
				if (mb_strlen($UOMrow['suppliersuom'])>0) {
					$UOM=$UOMrow['unitname'];
				} else {
					$UOM=$MyRow['units'];
				}
			} else {
				$UOM=$MyRow['units'];
			}
			echo '<tr class="striped_row">
					<td>' . $MyRow['stockid'] . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . $UOM . '</td>
					<td>' . $ImageSource . '</td>
					<td><input class="number" title="'.__('The input must be numeric').'" type="text" size="6" value="0" name="Qty'.$i.'" /></td>
					<td><input class="number" title="'.__('The input must be numeric').'" type="text" size="12" value="0" name="Price'.$i.'" /></td>
					<input type="hidden" size="12" value="'.$MyRow['stockid'].'" name="StockID'.$i.'" />
					<input type="hidden" value="'.$UOM.'" name="uom'.$i.'" />
					</tr>';
			$i++;
			$PartsDisplayed++;
			if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show){
				break;
			}
#end of page full new headings if
		}
#end of while loop
		echo '</table>';
		if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show){

	/*$Maximum_Number_Of_Parts_To_Show defined in config.php */
			prnMsg( __('Only the first') . ' ' . $Maximum_Number_Of_Parts_To_Show . ' ' . __('can be displayed') . '. ' .
				__('Please restrict your search to only the parts required'),'info');
		}
		echo '<a name="end"></a>
				<br />
				<div class="centre">
					<input type="submit" name="NewItem" value="Add to Offer" />
				</div>';
	}#end if SearchResults to show
	echo '<input type="hidden" name="TenderType" value="'.$_POST['TenderType'].'" />';
	echo '<input type="hidden" name="SupplierID" value="'.$_POST['SupplierID'].'" />';

	echo '</form>';

} //end of if search

include('includes/footer.php');
