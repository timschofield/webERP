<?php

/* Selection of items. All item maintenance, transactions and inquiries start with this script. */

$PricesSecurity = 12; //don't show pricing info unless security token 12 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user
$CostSecurity = 18; //don't show cost info unless security token 18 available to user

include('includes/session.php');

$Title = __('Search Inventory Items');
$ViewTopic = 'Inventory';
$BookMark = 'SelectingInventory';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');
include('includes/ImageFunctions.php');

if (isset($_GET['StockID'])) {
	//The page is called with a StockID
	$_GET['StockID'] = trim(mb_strtoupper($_GET['StockID']));
	$_POST['Select'] = trim(mb_strtoupper($_GET['StockID']));
}

if (isset($_GET['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['StockCode'])) {
	$_POST['StockCode'] = trim(mb_strtoupper($_POST['StockCode']));
}
// Always show the search facilities
$SQL = "SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);
if (DB_num_rows($Result1) == 0) {
	prnMsg(__('There are no stock categories currently defined. Please use the link below to set them up'), 'warn');
	echo '<a class="toplink" href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a><br /><br />';
	include('includes/footer.php');
	exit();
}
// end of showing search facilities
/* displays item options if there is one and only one selected */
$TableHead =
	'<table cellpadding="4" width="90%" class="selection">
		<thead>
			<tr>
				<th style="width:33%">' .
					'<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/reports.png" title="' . __('Inquiries and Reports') . '" />' .
					__('Item Inquiries') . '</th>
				<th style="width:33%">' .
					'<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . __('Transactions') . '" />' .
					__('Item Transactions') . '</th>
				<th style="width:33%">' .
					'<img alt="" src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Maintenance') . '" />' .
					__('Item Maintenance') . '</th>
			</tr>
		</thead>
		<tbody>';
if (!isset($_POST['Search']) AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {
	if (isset($_POST['Select'])) {
		$_SESSION['SelectedStockItem'] = $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}
	$Result = DB_query("SELECT stockmaster.description,
								stockmaster.longdescription,
								stockmaster.mbflag,
								stockcategory.stocktype,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.actualcost AS cost,
								stockmaster.discontinued,
								stockmaster.eoq,
								stockmaster.volume,
								stockmaster.grossweight,
								stockcategory.categorydescription,
								stockmaster.categoryid
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE stockid='" . $StockID . "'");
	$MyRow = DB_fetch_array($Result);
	$Its_A_Kitset_Assembly_Or_Dummy = false;
	$Its_A_Dummy = false;
	$Its_A_Kitset = false;
	$Its_A_Labour_Item = false;
	if ($MyRow['discontinued']==1){
		$ItemStatus = '<p class="bad">' .__('Obsolete') . '</p>';
	} else {
		$ItemStatus = '';
	}
/*	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/inventory.png" title="', // Icon image.
		__('Inventory Item'), '" /> ', // Icon title.
		'<b title="', $MyRow['longdescription'], '">',
		__('Inventory Item'), ': ', $StockID, ' - ', $MyRow['description'], '</b> ', $ItemStatus, '</p>';// Page title.*/
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/inventory.png" title="', // Icon image.
		__('Inventory Item'), '" /> ', // Icon title.
		__('Inventory Item'), ': ', $StockID, ' - ', $MyRow['description'], ' ', $ItemStatus, '</p>';// Page title.

	echo '<table width="90%">
		<tr>
			<td style="width:40%" valign="top">
			<table>'; //nested table
	echo '<tr><th class="number">' . __('Category') . ':</th> <td colspan="6" class="select">' . $MyRow['categorydescription'] , '</td></tr>';
	echo '<tr><th class="number">' . __('Item Type') . ':</th>
			<td colspan="2" class="select">';
	switch ($MyRow['mbflag']) {
		case 'A':
			echo __('Assembly Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
		break;
        case 'G':
            echo __('Phantom Assembly Item');
            $Its_A_Kitset_Assembly_Or_Dummy = True;
            $Its_A_Kitset = True;
        break;
		case 'K':
			echo __('Kitset Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			$Its_A_Kitset = True;
		break;
		case 'D':
			echo __('Service/Labour Item');
			$Its_A_Kitset_Assembly_Or_Dummy = True;
			$Its_A_Dummy = True;
			if ($MyRow['stocktype'] == 'L') {
				$Its_A_Labour_Item = True;
			}
		break;
		case 'B':
			echo __('Purchased Item');
		break;
		default:
			echo __('Manufactured Item');
		break;
	}
	echo '</td><th class="number">' . __('Control Level') . ':</th><td class="select">';
	if ($MyRow['serialised'] == 1) {
		echo __('serialised');
	} elseif ($MyRow['controlled'] == 1) {
		echo __('Batchs/Lots');
	} else {
		echo __('N/A');
	}
	echo '</td><th class="number">' . __('Units') . ':</th>
			<td class="select">' . $MyRow['units'] . '</td></tr>';
	echo '<tr><th class="number">' . __('Volume') . ':</th>
			<td class="select" colspan="2">' . locale_number_format($MyRow['volume'], 3) . '</td>
			<th class="number">' . __('Weight') . ':</th>
			<td class="select">' . locale_number_format($MyRow['grossweight'], 3) . '</td>
			<th class="number">' . __('EOQ') . ':</th>
			<td class="select">' . locale_number_format($MyRow['eoq'], $MyRow['decimalplaces']) . '</td></tr>';
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
		$PriceResult = DB_query("SELECT typeabbrev,
										price
								FROM prices
								WHERE currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
									AND typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'
									AND debtorno=''
									AND branchcode=''
									AND startdate <= CURRENT_DATE
									AND enddate >= CURRENT_DATE
									AND stockid='" . $StockID . "'");
		if ($MyRow['mbflag'] == 'K' OR $MyRow['mbflag'] == 'A' OR $MyRow['mbflag'] == 'G') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockmaster.actualcost)) AS cost
									FROM bom INNER JOIN stockmaster
									ON bom.component=stockmaster.stockid
									WHERE bom.parent='" . $StockID . "'
										AND bom.effectiveafter <= CURRENT_DATE
										AND bom.effectiveto > CURRENT_DATE");
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $MyRow['cost'];
		}
		echo '<tr>
				<th class="number">' . __('Price') . ':</th>';
		if (DB_num_rows($PriceResult) == 0) {
			echo '<td class="select" colspan="2">' . __('No Default Price Set') . '</td>';
			$Price = 0;
		} else {
			$PriceRow = DB_fetch_row($PriceResult);
			$Price = $PriceRow[1];
			echo '<td class="select" colspan="2" style="text-align:right">' . locale_number_format($Price, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
		}
		if (in_array($CostSecurity,$_SESSION['AllowedPageSecurityTokens'])) {
		echo '<th class="number">' . __('Cost') . ':</th>
			<td class="select" style="text-align:right">' . locale_number_format($Cost, $_SESSION['StandardCostDecimalPlaces']) . '</td>
			<th class="number">' . __('Gross Profit') . ':</th>
			<td class="select" style="text-align:right">';
		if ($Price > 0) {
			echo locale_number_format(($Price - $Cost) * 100 / $Price, 1) . '%';
		} else {
			echo __('N/A');
		}
		echo '</td>';
		}
		echo '</tr>';
	} //end of if PricesSecurity allows viewing of prices
	echo '</table>'; //end of first nested table
	// Item Category Property mod: display the item properties
	echo '<table>';

	$SQL = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue
				FROM stockcatproperties
				WHERE categoryid ='" . $MyRow['categoryid'] . "'
				AND reqatsalesorder =0
				ORDER BY stkcatpropid";
	$PropertiesResult = DB_query($SQL);
	$PropertyCounter = 0;
	$PropertyWidth = array();
	while($PropertyRow = DB_fetch_array($PropertiesResult)) {
		$PropValResult = DB_query("SELECT value
									FROM stockitemproperties
									WHERE stockid='" . $StockID . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid']."'");
		$PropValRow = DB_fetch_row($PropValResult);
		if (DB_num_rows($PropValResult)==0){
			$PropertyValue = __('Not Set');
		} else {
			$PropertyValue = $PropValRow[0];
		}
		echo '<tr>
				<th align="right">' . $PropertyRow['label'] . ':</th>';
		switch ($PropertyRow['controltype']) {
			case 0:
			case 1:
				echo '<td class="select" style="width:60px">' . $PropertyValue;
			break;
			case 2; //checkbox
				echo '<td class="select" style="width:60px">';
				if ($PropertyValue == __('Not Set')){
					echo __('Not Set');
				} elseif ($PropertyValue == 1){
					echo __('Yes');
				} else {
					echo __('No');
				}
			break;
		} //end switch
		echo '</td></tr>';
		$PropertyCounter++;
	} //end loop round properties for the item category
	echo '</table></td>'; //end of Item Category Property mod
	echo '<td style="width:15%; vertical-align:top">
				<table>'; //nested table to show QOH/orders
	$QOH = 0;
	switch ($MyRow['mbflag']) {
		case 'A':
		case 'D':
		case 'K':
			$QOH = __('N/A');
			$QOO = __('N/A');
		break;
		case 'M':
		case 'B':
			// get the QOH for all locations. Function defined in StockFunctions.php
			$QOH = GetQuantityOnHand($StockID, 'ALL');
			// Get the QOO
			$QOO = GetQuantityOnOrder($StockID, 'ALL');
		break;
	}

	$Demand = GetDemand($StockID, 'ALL');

	echo '<tr>
			<th class="number" style="width:15%">' . __('Quantity On Hand') . ':</th>
			<td style="width:17%; text-align:right" class="select">' . locale_number_format($QOH, $MyRow['decimalplaces']) . '</td>
		</tr>
		<tr>
			<th class="number" style="width:15%">' . __('Quantity Demand') . ':</th>
			<td style="width:17%; text-align:right" class="select">' . locale_number_format($Demand, $MyRow['decimalplaces']) . '</td>
		</tr>
		<tr>
			<th class="number" style="width:15%">' . __('Quantity On Order') . ':</th>
			<td style="width:17%; text-align:right" class="select">' . locale_number_format($QOO, $MyRow['decimalplaces']) . '</td>
		</tr>
		</table>'; //end of nested table
	echo '</td>'; //end cell of master table

	if (($MyRow['mbflag'] == 'B' OR ($MyRow['mbflag'] == 'M'))
		AND (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))){

		echo '<td style="width:50%" valign="top"><table>
				<tr><th style="width:20%">' . __('Supplier') . '</th>
					<th style="width:15%">' . __('Code') . '</th>
					<th style="width:15%">' . __('Cost') . '</th>
					<th style="width:5%">' . __('Curr') . '</th>
					<th style="width:10%">' . __('Lead Time') . '</th>
					<th style="width:10%">' . __('Min Order Qty') . '</th>
					<th style="width:5%">' . __('Prefer') . '</th></tr>';
		$SuppResult = DB_query("SELECT suppliers.suppname,
										suppliers.currcode,
										suppliers.supplierid,
										purchdata.price,
										purchdata.suppliers_partno,
										purchdata.leadtime,
										purchdata.conversionfactor,
										purchdata.minorderqty,
										purchdata.preferred,
										currencies.decimalplaces,
										purchdata.effectivefrom
									FROM purchdata INNER JOIN suppliers
									ON purchdata.supplierno=suppliers.supplierid
									INNER JOIN currencies
									ON suppliers.currcode=currencies.currabrev
									WHERE purchdata.stockid = '" . $StockID . "'
									AND purchdata.effectivefrom=(SELECT max(a.effectivefrom) FROM purchdata a WHERE purchdata.supplierno=a.supplierno and a.stockid=purchdata.stockid)
									ORDER BY purchdata.preferred DESC");

		while ($SuppRow = DB_fetch_array($SuppResult)) {
			echo '<tr>
					<td class="select">' . $SuppRow['suppname'] . '</td>
					<td class="select">' . $SuppRow['suppliers_partno'] . '</td>
					<td class="select" style="text-align:right">' . locale_number_format($SuppRow['price'] / $SuppRow['conversionfactor'], $SuppRow['decimalplaces']) . '</td>
					<td class="select">' . $SuppRow['currcode'] . '</td>
					<td class="select" style="text-align:right">' . $SuppRow['leadtime'] . '</td>
					<td class="select" style="text-align:right">' . $SuppRow['minorderqty'] . '</td>';

			if ($SuppRow['preferred']==1) { //then this is the preferred supplier
				echo '<td class="select">' . __('Yes') . '</td>';
			} else {
				echo '<td class="select">' . __('No') . '</td>';
			}
			echo '<td class="select"><a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SelectedSupplier=' .
				$SuppRow['supplierid'] . '&amp;StockID=' . urlencode($StockID) . '&amp;Quantity='.$SuppRow['minorderqty'].'&amp;LeadTime='.$SuppRow['leadtime'] . '">' . __('Order') . ' </a></td>';
			echo '</tr>';
		}
		echo '</table>';
		DB_data_seek($Result, 0);
	}
	echo '</td>
		</tr>
		</table>',// End first item details table
		'<div class="page_help_text">', __('Select a menu option to operate using this inventory item.'), '</div>',// Page help text.
		'<br />',
		$TableHead,
			'<tr>
				<td valign="top" class="select">';
	/*Stock Inquiry Options */
	echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockID) . '">' . __('Show Stock Movements') . '</a><br />';
	if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
		echo '<a href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($StockID) . '">' . __('Show Stock Status') . '</a><br />';
		echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockID) . '">' . __('Show Stock Usage') . '</a><br />';
	}
	echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . __('Search Outstanding Sales Orders') . '</a><br />';
	echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . __('Search Completed Sales Orders') . '</a><br />';
	if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
		echo '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . __('Search Outstanding Purchase Orders') . '</a><br />';
		echo '<a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedStockItem=' . urlencode($StockID) . '">' . __('Search All Purchase Orders') . '</a><br />';

		$PossibleImageFiles = glob($_SESSION['part_pics_dir'] . '/' . $StockID . '.{png,jpg,jpeg}', GLOB_BRACE);
		if (count($PossibleImageFiles)>0) {
			$ImageFile =  $PossibleImageFiles[0];
		} else {
			$ImageFile ='';
		}
		echo '<a href="' . $RootPath . '/' . $ImageFile . '" target="_blank">' . __('Show Part Picture (if available)') . '</a><br />';
	}
	if ($Its_A_Dummy == False) {
		echo '<a href="' . $RootPath . '/BOMInquiry.php?StockID=' . urlencode($StockID) . '">' . __('View Costed Bill Of Material') . '</a><br />';
		echo '<a href="' . $RootPath . '/WhereUsedInquiry.php?StockID=' . urlencode($StockID) . '">' . __('Where This Item Is Used') . '</a><br />';
	}
	if ($Its_A_Labour_Item == True) {
		echo '<a href="' . $RootPath . '/WhereUsedInquiry.php?StockID=' . urlencode($StockID) . '">' . __('Where This Labour Item Is Used') . '</a><br />';
	}
	wikiLink('Product', $StockID);
	echo '</td><td valign="top" class="select">';
	/* Stock Transactions */
	if ($Its_A_Kitset_Assembly_Or_Dummy == false) {
		echo '<a href="' . $RootPath . '/StockAdjustments.php?StockID=' . urlencode($StockID) . '">' . __('Quantity Adjustments') . '</a><br />';
		echo '<a href="' . $RootPath . '/StockTransfers.php?StockID=' . urlencode($StockID) . '&amp;NewTransfer=true">' . __('Location Transfers') . '</a><br />';

		//show the item image if it has been uploaded
		$StockImgLink = GetImageLink($ImageFile, $StockID, 200, 200, "", "");

		echo '<div class="centre">' . $StockImgLink . '</div>';

		if (($MyRow['mbflag'] == 'B')
			AND (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))
			AND $MyRow['discontinued']==0){
			echo '<br />';
			$SuppResult = DB_query("SELECT suppliers.suppname,
											suppliers.supplierid,
											purchdata.preferred,
											purchdata.minorderqty,
											purchdata.leadtime
										FROM purchdata INNER JOIN suppliers
										ON purchdata.supplierno=suppliers.supplierid
										WHERE purchdata.stockid='" . $StockID . "'
										ORDER BY purchdata.effectivefrom DESC");
			$LastSupplierShown = "";
			while ($SuppRow = DB_fetch_array($SuppResult)) {
				if ($LastSupplierShown != $SuppRow['supplierid']){
					if (($MyRow['eoq'] < $SuppRow['minorderqty'])) {
						$EOQ = $SuppRow['minorderqty'];
					} else {
						$EOQ = $MyRow['eoq'];
					}
					echo '<a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes' . '&amp;SelectedSupplier=' . $SuppRow['supplierid'] . '&amp;StockID=' . urlencode($StockID) . '&amp;Quantity='.$EOQ.'&amp;LeadTime='.$SuppRow['leadtime'].'">' .  __('Purchase this Item from') . ' ' . $SuppRow['suppname'] . '</a>
					<br />';
					$LastSupplierShown = $SuppRow['supplierid'];
				}
				/**/
			} /* end of while */
		} /* end of $MyRow['mbflag'] == 'B' */
	} /* end of ($Its_A_Kitset_Assembly_Or_Dummy == False) */
	echo '</td><td valign="top" class="select">';
	/* Stock Maintenance Options */
	echo '<a href="' . $RootPath . '/Stocks.php?">' . __('Insert New Item') . '</a><br />';
	echo '<a href="' . $RootPath . '/Stocks.php?StockID=' . urlencode($StockID) . '">' . __('Modify Item Details') . '</a><br />';
	if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
		echo '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Reorder Levels') . '</a><br />';
		echo '<a href="' . $RootPath . '/StockCostUpdate.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Standard Cost') . '</a><br />';
		echo '<a href="' . $RootPath . '/PurchData.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Purchasing Data') . '</a><br />';
		echo '<a href="' . $RootPath . '/CustItem.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Customer Item Data') . '</a><br />';
	}
	if ($Its_A_Labour_Item == True) {
		echo '<a href="' . $RootPath . '/StockCostUpdate.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Standard Cost') . '</a><br />';
	}
	if (!$Its_A_Kitset) {
		echo '<a href="' . $RootPath . '/Prices.php?Item=' . urlencode($StockID) . '">' . __('Maintain Pricing') . '</a><br />';
		if (isset($_SESSION['CustomerID'])
			AND $_SESSION['CustomerID'] != ''
			AND mb_strlen($_SESSION['CustomerID']) > 0) {
			echo '<a href="' . $RootPath . '/Prices_Customer.php?Item=' . urlencode($StockID) . '">' . __('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</a><br />';
		}
		echo '<a href="' . $RootPath . '/DiscountCategories.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Discount Category') . '</a><br />';
	    echo '<a href="' . $RootPath . '/StockClone.php?OldStockID=' . urlencode($StockID) . '">' . __('Clone This Item') . '</a><br />';
		echo '<a href="' . $RootPath . '/RelatedItemsUpdate.php?Item=' . urlencode($StockID) . '">' . __('Maintain Related Items') . '</a><br />';
		echo '<a href="' . $RootPath . '/PriceMatrix.php?StockID=' . urlencode($StockID) . '">' . __('Maintain Price Matrix') . '</a><br />';
	}
	echo '</td></tr><tbody></table>';
} else {
	// options (links) to pages. This requires stock id also to be passed.

	// Inventory Item is not selected yet
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/inventory.png" title="', // Icon image.
		__('Inventory Items'), '" /> ', // Icon title.
		__('Inventory Items'), '</p>',// Page title.
		'<br />',
		$TableHead,
		'<tr>',
			'<td class="select"></td>',// Item inquiries options.
			'<td class="select"></td>',// Item transactions options.
			'<td class="select"><a href="', $RootPath, '/Stocks.php?">', __('Insert New Item'), '</a></td>',// Stock Maintenance Options.
		'</tr><tbody></table>';
}// end displaying item options if there is one and only one record
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for Inventory Items'). '</p>';

echo '<fieldset>
		<legend class="search">', __('Search for Stock Item'), '</legend>';

echo '<field>
		<label for="StockCat">' . __('In Stock Category') . ':</label>';
echo '<select name="StockCat">';
if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] ='';
}
if ($_POST['StockCat'] == 'All') {
	echo '<option selected="selected" value="All">' . __('All') . '</option>';
} else {
	echo '<option value="All">' . __('All') . '</option>';
}
while ($MyRow1 = DB_fetch_array($Result1)) {
	if ($MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">' . __('Enter partial') . '<b> ' . __('Description') . '</b>:</label>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . __('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . __('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
}
echo '</field>';

echo '<field>
		<label for="StockCode">' . '<b>' . __('OR') . ' </b>' . __('Enter partial') . ' <b>' . __('Stock Code') . '</b>:</label>';
if (isset($_POST['StockCode'])) {
	echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . __('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
} else {
	echo '<input type="text" name="StockCode" title="' . __('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
}
echo '<field>';

echo '<field>
		<label>' . '<b>' . __('OR') . ' </b>' . __('Enter partial') . ' <b>' . __('Supplier Stock Code') . '</b>:</label>';
if (isset($_POST['SupplierStockCode'])) {
	echo '<input type="text" name="SupplierStockCode" value="' . $_POST['SupplierStockCode'] . '" title="" size="15" maxlength="18" />
		<fieldhelp>' . __('Enter text that you wish to search for in the supplier\'s item code') . '</fieldhelp';
} else {
	echo '<input type="text" name="SupplierStockCode" title="" size="15" maxlength="18" />
		<fieldhelp>' . __('Enter text that you wish to search for in the supplier\'s item code') . '</fieldhelp';
}
echo '</field>
	</fieldset>';

echo '<div class="centre"><input type="submit" name="Search" value="' . __('Search Now') . '" /></div>';
echo '</form>';
// query for list of record(s)
if (isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['Search']='Search';
}
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	$SQL = GenerateStockmasterQuery($_POST);
	$ErrMsg = __('No stock items were returned by the SQL because');
	$SearchResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) AND !isset($_POST['Select'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
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
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
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
				<input type="submit" name="Next" value="' . __('Next') . '" />
				<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'" />
				<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'" />
				<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'" />
				<br />
				</div>';
		}
		echo '<table id="ItemSearchTable" class="selection">
			<thead>
				<tr>
							<th>' . __('Stock Status') . '</th>
							<th class="SortedColumn">' . __('Code') . '</th>
                            				<th>'. __('Image').'</th>
							<th class="SortedColumn">' . __('Description') . '</th>
							<th>' . __('Total Qty On Hand') . '</th>
							<th>' . __('Units') . '</th>
				</tr>
			</thead>
			<tbody>';

		$RowIndex = 0;

		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($MyRow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($MyRow['mbflag'] == 'D') {
				$QOH = __('N/A');
			} else {
				$QOH = locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']);
			}
			if ($MyRow['discontinued']==1){
				$ItemStatus = '<p class="bad">' . __('Obsolete') . '</p>';
			} else {
				$ItemStatus ='';
			}

			$PossibleImageFiles = glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{png,jpg,jpeg}', GLOB_BRACE);
			if (count($PossibleImageFiles)>0) {
				$ImageFile =  $PossibleImageFiles[0];
			} else {
				$ImageFile ='';
			}
			$StockImgLink = GetImageLink($ImageFile, $MyRow['stockid'], 100, 100, "", "");

			echo '<tr class="striped_row">
				<td>' . $ItemStatus . '</td>
			<td><input type="submit" name="Select" value="' . $MyRow['stockid'] . '" /></td>
			<td>'.$StockImgLink.'</td>
			<td title="'. $MyRow['longdescription'] . '">' . $MyRow['description'] . '</td>
			<td class="number">' . $QOH . '</td>
			<td>' . $MyRow['units'] . '</td>
			<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($MyRow['stockid']).'">' . __('View') . '</a></td>
			</tr>';

			$RowIndex = $RowIndex + 1;
		}
		//end of while loop
		echo '</tbody></table>
              </div>
              </form>
              <br />';
	}
}
/* end display list if there is more than one record */

include('includes/footer.php');

/**
 * Code mostly generated by Gemini 2.0
 * Generates an SQL query for stockmaster data based on user-provided filters.
 *
 * The function constructs a SELECT query with JOINs to retrieve stock information,
 * including quantity on hand (qoh).  It supports filtering by keywords,
 * stock code, supplier stock code, and stock category.  The query is ordered
 * by discontinued status and stock ID.
 *
 * @param array $post An array containing user input, typically from $_POST.
 * Expected keys:
 * - 'Keywords':  String to search for in stock descriptions.
 * - 'StockCode': String to search for in stock IDs.
 * - 'SupplierStockCode': String to search for in supplier part numbers.
 * - 'StockCat':  Category ID to filter by, or 'All' for all categories.
 *
 * @return string The generated SQL query string.  Returns an empty string if
 * no valid search criteria are provided.
 */
function GenerateStockmasterQuery(array $post): string {

    // Helper function to sanitize and prepare search strings.
    function PrepareSearchString(string $InputString): string {
        $InputString = mb_strtoupper($InputString); // Consistent case for comparisons.
        return '%' . str_replace(' ', '%', $InputString) . '%'; // Add wildcards.
    }

    // Initialize the SQL query.
    $SQL = "SELECT stockmaster.stockid,
                   stockmaster.description,
                   stockmaster.longdescription,
                   SUM(locstock.quantity) AS qoh,
                   stockmaster.units,
                   stockmaster.mbflag,
                   stockmaster.discontinued,
                   stockmaster.decimalplaces
            FROM stockmaster ";

    // Common JOIN and WHERE clauses.
    $JoinsSQL = "";
    $WhereSQL = " WHERE stockmaster.stockid = locstock.stockid "; // Corrected initial where clause

    // Determine the filter and build the query.
    if (isset($post['Keywords']) && mb_strlen($post['Keywords']) > 0) {
        $SearchString = PrepareSearchString($post['Keywords']);
        $JoinsSQL .= "LEFT JOIN stockcategory
						ON stockmaster.categoryid = stockcategory.categoryid
					LEFT JOIN locstock
						ON stockmaster.stockid = locstock.stockid "; // Added locstock to the join.
        $WhereSQL .= "AND stockmaster.description LIKE '$SearchString' ";
    } elseif (isset($post['StockCode']) && mb_strlen($post['StockCode']) > 0) {
        $SearchString = PrepareSearchString($post['StockCode']);
        $JoinsSQL .= "INNER JOIN stockcategory
						ON stockmaster.categoryid = stockcategory.categoryid
					INNER JOIN locstock
						ON stockmaster.stockid = locstock.stockid "; //Added locstock join
        $WhereSQL .= "AND stockmaster.stockid LIKE '$SearchString' ";
    } elseif (isset($post['SupplierStockCode']) && mb_strlen($post['SupplierStockCode']) > 0) {
        $SearchString = PrepareSearchString($post['SupplierStockCode']);
        $JoinsSQL .= "INNER JOIN purchdata
						ON stockmaster.stockid = purchdata.stockid
					INNER JOIN locstock
						ON stockmaster.stockid = locstock.stockid
					LEFT JOIN stockcategory
						ON stockmaster.categoryid = stockcategory.categoryid"; // Added locstock join
        $WhereSQL .= "AND purchdata.suppliers_partno LIKE '$SearchString' ";
    } else {
        $JoinsSQL .= "LEFT JOIN stockcategory
						ON stockmaster.categoryid = stockcategory.categoryid
					LEFT JOIN locstock
						ON stockmaster.stockid = locstock.stockid "; // Added locstock to the join.
    }

    // Category filter.
    if ($post['StockCat'] != 'All') {
        $WhereSQL .= "AND stockmaster.categoryid = '" . $post['StockCat'] . "' ";
    }

    // Complete the query.
    $SQL .= $JoinsSQL;
    $SQL .= $WhereSQL;
    $SQL .= "GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.longdescription,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.discontinued,
                    stockmaster.decimalplaces
             ORDER BY stockmaster.discontinued,
			 		stockmaster.stockid";

    return $SQL;
}
