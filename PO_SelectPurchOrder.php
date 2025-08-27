<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Search Purchase Orders');
$ViewTopic = 'PurchaseOrdering';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Purchase Orders') . '" alt=""  />' . ' ' . __('Purchase Orders') .
	'</p>';

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])) {
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['SelectedSupplier'])) {
	$SelectedSupplier = $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])) {
	$SelectedSupplier = $_POST['SelectedSupplier'];
}
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}
if (isset($OrderNumber) AND $OrderNumber != '') {
	if (!is_numeric($OrderNumber)) {
		prnMsg(__('The Order Number entered') . ' <U>' . __('MUST') . '</U> ' . __('be numeric'), 'error');
		unset($OrderNumber);
	} else {
		echo __('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo __('For supplier') . ': ' . $SelectedSupplier . ' ' . __('and') . ' ';
		echo '<input type="hidden" name="SelectedSupplier" value="' . $SelectedSupplier . '" />';
	}
}
if (isset($_POST['SearchParts'])) {
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) as qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.description " . LIKE  . " '" . $SearchString ."'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif ($_POST['StockCode']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord,
				stockmaster.units
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.stockid " . LIKE  . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	}
	$ErrMsg = __('No stock items were returned by the SQL because');
	$StockItemsResult = DB_query($SQL, $ErrMsg);
}
/* Not appropriate really to restrict search by date since user may miss older
* ouststanding orders
* $OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/
if (!isset($OrderNumber) or $OrderNumber == "") {
	echo '<fieldset>
			<legend>', __('Order Selection Options'), '</legend>';
	if (isset($SelectedStockItem)) {
		echo __('For the part') . ':<b>' . $SelectedStockItem . '</b> ' . __('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
	}
	echo '<field>
			<label for="OrderNumber">', __('Order Number') . ':</label>
			<input class="integer" name="OrderNumber" autofocus="autofocus" maxlength="8" size="9" />
		</field>';

	echo '<field>
			<label for="StockLocation">' . __('Into Stock Location') . ':</label>
			<select name="StockLocation"> ';

	$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$ResultStkLocs = DB_query($SQL);
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_POST['StockLocation'])) {
			if ($MyRow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Status">' . __('Order Status:') .'</label>
			<select name="Status">';
 	if (!isset($_POST['Status']) OR $_POST['Status']=='Pending_Authorised_Completed'){
		echo '<option selected="selected" value="Pending_Authorised_Completed">' . __('Pending/Authorised/Completed') . '</option>';
	} else {
		echo '<option value="Pending_Authorised_Completed">' . __('Pending/Authorised/Completed') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Pending'){
		echo '<option selected="selected" value="Pending">' . __('Pending') . '</option>';
	} else {
		echo '<option value="Pending">' . __('Pending') . '</option>';
	}
 	if (isset($_POST['Status']) AND $_POST['Status']=='Authorised'){
		echo '<option selected="selected" value="Authorised">' . __('Authorised') . '</option>';
	} else {
		echo '<option value="Authorised">' . __('Authorised') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Completed'){
		echo '<option selected="selected" value="Completed">' . __('Completed') . '</option>';
	} else {
		echo '<option value="Completed">' . __('Completed') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Cancelled'){
		echo '<option selected="selected" value="Cancelled">' . __('Cancelled') . '</option>';
	} else {
		echo '<option value="Cancelled">' . __('Cancelled') . '</option>';
	}
	if (isset($_POST['Status']) AND $_POST['Status']=='Rejected'){
		echo '<option selected="selected" value="Rejected">' . __('Rejected') . '</option>';
	} else {
		echo '<option value="Rejected">' . __('Rejected') . '</option>';
	}
 	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchOrders" value="' . __('Search Purchase Orders') . '" />
		</div>';
}
$SQL = "SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);
echo '<fieldset>
		<legend>', __('To search for purchase orders for a specific part use the part selection facilities below') . '</legend>';
echo '<field>
		<label foe="StockCat">' . __('Select a stock category') . ':</label>
		<select name="StockCat">';
while ($MyRow1 = DB_fetch_array($Result1)) {
	if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">' . __('Enter text extracts in the') . ' <b>' . __('description') . '</b>:</label>
		<input type="text" name="Keywords" size="20" maxlength="25" />
	</field>
	<field>
		<label for="StockCode">' . '<b>'. __('OR'). ' </b>'. __('Enter extract of the') . '<b> ' . __('Stock Code') . '</b>:</label>
		<input type="text" name="StockCode" size="15" maxlength="18" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="SearchParts" value="' . __('Search Parts Now') . '" />
		<input type="submit" name="ResetPart" value="' . __('Show All') . '" />
	</div>';

if (isset($StockItemsResult)) {
	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Code') . '</th>
				<th class="SortedColumn">' . __('Description') . '</th>
				<th class="SortedColumn">' . __('On Hand') . '</th>
				<th class="SortedColumn">' . __('Orders') . '<br />' . __('Outstanding') . '</th>
				<th class="SortedColumn">' . __('Units') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($StockItemsResult)) {
		echo '<tr class="striped_row">
			<td><input type="submit" name="SelectedStockItem" value="' . $MyRow['stockid'] . '"</td>
			<td>' . $MyRow['description'] . '</td>
			<td class="number">' . locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($MyRow['qord'],$MyRow['decimalplaces']) . '</td>
			<td>' . $MyRow['units'] . '</td>
			</tr>';
	}
	//end of while loop
	echo '</tbody></table>';
}
//end if stock search results to show
else {
	//figure out the SQL required from the inputs available

	if (!isset($_POST['Status']) OR $_POST['Status']=='Pending_Authorised_Completed'){
		$StatusCriteria = " AND (purchorders.status='Pending' OR purchorders.status='Authorised' OR purchorders.status='Printed' OR purchorders.status='Completed') ";
	}elseif ($_POST['Status']=='Authorised'){
		$StatusCriteria = " AND (purchorders.status='Authorised' OR purchorders.status='Printed')";
	}elseif ($_POST['Status']=='Pending'){
		$StatusCriteria = " AND purchorders.status='Pending' ";
	}elseif ($_POST['Status']=='Rejected'){
		$StatusCriteria = " AND purchorders.status='Rejected' ";
	}elseif ($_POST['Status']=='Cancelled'){
		$StatusCriteria = " AND purchorders.status='Cancelled' ";
	} elseif($_POST['Status']=='Completed'){
		$StatusCriteria = " AND purchorders.status='Completed' ";
	}
	if (isset($OrderNumber) AND $OrderNumber != '') {
		$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.deliverydate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						purchorders.status,
						suppliers.currcode,
						currencies.decimalplaces AS currdecimalplaces,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
					FROM purchorders
					INNER JOIN purchorderdetails
					ON purchorders.orderno = purchorderdetails.orderno
					INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
					INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
					WHERE purchorders.orderno='" . filter_number_format($OrderNumber) . "'
					GROUP BY purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						purchorders.status,
						suppliers.currcode,
						currencies.decimalplaces";
	} else {
		/* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */
		if (empty($_POST['StockLocation'])) {
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
		}
		if (isset($SelectedSupplier)) {
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE  purchorderdetails.itemcode='" . $SelectedStockItem . "'
							AND purchorders.supplierno='" . $SelectedSupplier . "'
							AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			} else {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE purchorders.supplierno='" . $SelectedSupplier . "'
							AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE purchorderdetails.itemcode='" . $SelectedStockItem . "'
							AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			} else {
				$SQL = "SELECT purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								purchorders.status,
								suppliers.currcode,
								currencies.decimalplaces AS currdecimalplaces,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders
							INNER JOIN purchorderdetails
							ON purchorders.orderno = purchorderdetails.orderno
							INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
							INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'
							" . $StatusCriteria . "
							GROUP BY purchorders.orderno,
								suppliers.suppname,
								purchorders.orddate,
								purchorders.initiator,
								purchorders.requisitionno,
								purchorders.allowprint,
								suppliers.currcode,
								currencies.decimalplaces";
			}
		} //end selected supplier

	} //end not order number selected
	$ErrMsg = __('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PurchOrdersResult) > 0) {
		/*show a table of the orders returned by the SQL */
		echo '<table cellpadding="2" width="90%" class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . __('View') . '</th>
					<th class="SortedColumn">' . __('Supplier') . '</th>
					<th class="SortedColumn">' . __('Currency') . '</th>
					<th class="SortedColumn">' . __('Requisition') . '</th>
					<th class="SortedColumn">' . __('Order Date') . '</th>
					<th class="SortedColumn">' . __('Delivery Date') . '</th>
					<th class="SortedColumn">' . __('Initiator') . '</th>
					<th class="SortedColumn">' . __('Order Total') . '</th>
					<th class="SortedColumn">' . __('Status') . '</th>
				</tr>
			</thead>
			</tbody>';

		while ($MyRow = DB_fetch_array($PurchOrdersResult)) {
			$ViewPurchOrder = $RootPath . '/PO_OrderDetails.php?OrderNo=' . $MyRow['orderno'];
			$FormatedOrderDate = ConvertSQLDate($MyRow['orddate']);
			$FormatedDeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
			$FormatedOrderValue = locale_number_format($MyRow['ordervalue'], $MyRow['currdecimalplaces']);

			echo '<tr class="striped_row">
					<td><a href="' . $ViewPurchOrder . '">' . $MyRow['orderno'] . '</a></td>
					<td>' . $MyRow['suppname'] . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $MyRow['requisitionno'] . '</td>
					<td class="date">' . $FormatedOrderDate . '</td>
					<td class="date">' . $FormatedDeliveryDate . '</td>
					<td>' . $MyRow['initiator'] . '</td>
					<td class="number">' . $FormatedOrderValue . '</td>
					<td>' . __($MyRow['status']) .  '</td>
					</tr>';
				//$MyRow['status'] is a string which has gettext translations from PO_Header.php script
		}
		//end of while loop
		echo '</tbody></table>';
	} // end if purchase orders to show
}
echo '</div>
      </form>';
include('includes/footer.php');
