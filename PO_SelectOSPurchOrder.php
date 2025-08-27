<?php

$PricesSecurity = 12;

require(__DIR__ . '/includes/session.php');

$Title = __('Search Outstanding Purchase Orders');
$ViewTopic = 'PurchaseOrdering';
$BookMark = '';
include('includes/header.php');

include('includes/DefinePOClass.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = trim($_GET['SelectedStockItem']);
}
elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = trim($_POST['SelectedStockItem']);
}

if (isset($_GET['OrderNumber'])) {
	$OrderNumber = $_GET['OrderNumber'];
}
elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = $_POST['OrderNumber'];
}

if (isset($_GET['SelectedSupplier'])) {
	$SelectedSupplier = trim($_GET['SelectedSupplier']);
}
elseif (isset($_POST['SelectedSupplier'])) {
	$SelectedSupplier = trim($_POST['SelectedSupplier']);
}

if (empty($_GET['identifier'])) {
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}

if (isset($SelectedSupplier)) {
	echo '<a class="toplink" href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SupplierID=' . $SelectedSupplier . '">' . __('Add Purchase Order') . '</a>';
} else {
	echo '<a class="toplink" href="' . $RootPath . '/PO_Header.php?NewOrder=Yes">' . __('Add Purchase Order') . '</a>';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}

if (isset($OrderNumber) AND $OrderNumber != '') {
	if (!is_numeric($OrderNumber)) {
		echo '<br /><b>' . __('The Order Number entered') . ' <u>' . __('MUST') . '</u> ' . __('be numeric') . '.</b><br />';
		unset($OrderNumber);
	} else {
		echo __('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo '<br />
				<div class="page_help_text">' . __('For supplier') . ': ' . $SelectedSupplier . ' ' . __('and') . ' ';
		echo '<input type="hidden" name="SelectedSupplier" value="' . $SelectedSupplier . '" />
				</div>';
	}
	if (isset($SelectedStockItem)) {
		echo '<input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
	}
}

if (isset($_POST['SearchParts'])) {
	if (isset($_POST['Keywords']) AND isset($_POST['StockCode'])) {
		echo '<div class="page_help_text">' . __('Stock description keywords have been used in preference to the Stock code extract entered') . '.</div>';
	}
	if (isset($_POST['StockCat']) AND $_POST['StockCat'] == 'All'){
		$WhereStockCat = ' ';
	} else {
		$WhereStockCat = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
					stockmaster.decimalplaces,
					stockmaster.description,
					stockmaster.units,
					SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
				FROM stockmaster INNER JOIN purchorderdetails
						ON stockmaster.stockid=purchorderdetails.itemcode
					INNER JOIN purchorders on purchorders.orderno=purchorderdetails.orderno
				WHERE purchorderdetails.completed=0
				AND purchorders.status NOT IN ('Completed','Cancelled','Rejected')
				AND stockmaster.description " . LIKE . " '" . $SearchString . "'
				" . $WhereStockCat . "
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				ORDER BY stockmaster.stockid";


	} elseif ($_POST['StockCode']) {

		$SQL = "SELECT stockmaster.stockid,
					stockmaster.decimalplaces,
					stockmaster.description,
					SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord,
					stockmaster.units
				FROM stockmaster INNER JOIN purchorderdetails
				ON stockmaster.stockid=purchorderdetails.itemcode
				INNER JOIN purchorders on purchorders.orderno=purchorderdetails.orderno
				WHERE purchorderdetails.completed=0
				AND purchorders.status NOT IN ('Completed','Cancelled','Rejected')
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				" . $WhereStockCat . "
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				ORDER BY stockmaster.stockid";

	} elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL = "SELECT stockmaster.stockid,
					stockmaster.decimalplaces,
					stockmaster.description,
					stockmaster.units,
					SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
				FROM stockmaster INNER JOIN purchorderdetails
				ON stockmaster.stockid=purchorderdetails.itemcode
				INNER JOIN purchorders on purchorders.orderno=purchorderdetails.orderno
				WHERE purchorderdetails.completed=0
				AND purchorders.status NOT IN ('Completed','Cancelled','Rejected')
				" . $WhereStockCat . "
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				ORDER BY stockmaster.stockid";
	}

	$ErrMsg = __('No stock items were returned by the SQL because');
	$StockItemsResult = DB_query($SQL, $ErrMsg);
} //isset($_POST['SearchParts'])


/* Not appropriate really to restrict search by date since user may miss older ouststanding orders
$OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/

if (!isset($OrderNumber) or $OrderNumber == '') {
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<fieldset>
			<legend>', __('Order Search Options'), '</legend>
			<field>
				<label for="OrderNumber">' . __('Order Number') . ':</label>
				<input type="text" name="OrderNumber" autofocus="autofocus" maxlength="8" size="9" />
			</field>
			<field>
				<label for="StockLocation">' . __('Into Stock Location') . ':</label>
				<select name="StockLocation">';

	if (!isset($_POST['DateFrom'])) {
		$DateSQL = "SELECT min(orddate) as fromdate,
							max(orddate) as todate
						FROM purchorders";
		$DateResult = DB_query($DateSQL);
		$DateRow = DB_fetch_array($DateResult);
		if ($DateRow['fromdate'] != null) {
			$DateFrom = $DateRow['fromdate'];
			$DateTo = $DateRow['todate'];
		} else {
			$DateFrom = date('Y-m-d');
			$DateTo = date('Y-m-d');
		}
	} else {
		$DateFrom = FormatDateForSQL($_POST['DateFrom']);
		$DateTo = FormatDateForSQL($_POST['DateTo']);
	}

	$SQL = "SELECT locations.loccode, locationname,(SELECT count(*) FROM locations) AS total FROM locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$ErrMsg = __('Failed to retrieve location data');
	$ResultStkLocs = DB_query($SQL, $ErrMsg);
	$UserLocations = DB_num_rows($ResultStkLocs);
	$AllListed = false;
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if(!isset($LocQty)){
			$LocQty = $MyRow['total'];
		}
		if (isset($_POST['StockLocation'])) {//The user has selected location
			if ($_POST['StockLocation'] == 'ALLLOC'){//user have selected all locations
				if($AllListed === false) {//it's the first loop
					echo '<option selected="selected" value="ALLLOC">' . __('All') . '</option>';
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
					$AllListed = true;
				} else { //it's not the first loop
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				}

			} else {//user have not selected all locations; There are two possibilities that users have right, but not choose all; or vice visa
				if ($MyRow['total'] == $UserLocations) { //user have allloc right
					if($AllListed === false){//first loop
						echo '<option value="ALLLOC">' . __('All') . '</option>';
						$AllListed = true;
					}
				}
				if ($MyRow['loccode'] == $_POST['StockLocation']){
					echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				} else {
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				}
			}
		} else {//users have not selected locations
			if($MyRow['total'] == $UserLocations){//users have right to submit All locations
				if($AllListed === false){//first loop
					echo '<option selected="selected" value="ALLLOC">' . __('All') . '</option>';//default value is all
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
					$AllListed = true;
				} else {//not first loop
					echo '<option value="' . $MyRow['loccode'] . '" >' . $MyRow['locationname'] . '</option>';
				}
			} else {//no right to submit all locations
				if ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
					echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				} else {
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				}
			}

		}
	}
	echo '</select>
		</field>
		<field>
			<label for="Status">' . __('Order Status:') . '</label>
			<select name="Status">';
	if (!isset($_POST['Status']) OR $_POST['Status'] == 'Pending_Authorised') {
		echo '<option selected="selected" value="Pending_Authorised">' . __('Pending and Authorised') . '</option>';
	} else {
		echo '<option value="Pending_Authorised">' . __('Pending and Authorised') . '</option>';
	}
	if(isset($_POST['Status'])){
		if ($_POST['Status'] == 'Pending') {
			echo '<option selected="selected" value="Pending">' . __('Pending') . '</option>';
		} else {
			echo '<option value="Pending">' . __('Pending') . '</option>';
		}
		if ($_POST['Status'] == 'Authorised') {
			echo '<option selected="selected" value="Authorised">' . __('Authorised') . '</option>';
		} else {
			echo '<option value="Authorised">' . __('Authorised') . '</option>';
		}
		if ($_POST['Status'] == 'Cancelled') {
			echo '<option selected="selected" value="Cancelled">' . __('Cancelled') . '</option>';
		} else {
			echo '<option value="Cancelled">' . __('Cancelled') . '</option>';
		}
		if ($_POST['Status'] == 'Rejected') {
			echo '<option selected="selected" value="Rejected">' . __('Rejected') . '</option>';
		} else {
			echo '<option value="Rejected">' . __('Rejected') . '</option>';
		}
	}
	$Checked = (isset($_POST['PODetails']))?'checked="checked"':'';
	echo '</select>
		</field>';

	echo '<field>
			<label for="DateFrom">' . __('Orders Between') . '</label>
			<input name="DateFrom" value="' . date('Y-m-d',strtotime($DateFrom)) . '"  type="date" size="10" />
		' . __('and') . ':&nbsp;
			<input name="DateTo" value="' . date('Y-m-d',strtotime($DateTo)) . '"  type="date" size="10" />
		</field>
		<field>
			<label for="PODetails">' . __('Show PO Details') . '</label>
			<input type="checkbox" name="PODetails" ' . $Checked . ' />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="SearchOrders" value="' . __('Search Purchase Orders') . '" />
		</div>';
} //!isset($OrderNumber) or $OrderNumber == ''

$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$Result1 = DB_query($SQL);

echo '<div class="page_help_text">' . __('To search for purchase orders for a specific part use the part selection facilities below') . '</div>';
echo '<fieldset>
		<legend>', __('Item Search Options'), '</legend>';

echo '<field>
		<label for="StockCat">' . __('Select a stock category') . ':</label>
		<select name="StockCat">';
if (DB_num_rows($Result1)>0){
	echo '<option value="All">' . __('All') . '</option>';
}
while ($MyRow1 = DB_fetch_array($Result1)) {
	if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
} //end loop through categories
echo '</select>
	</field>';

echo '<field>
		<label for="Keywords">' . __('Enter text extracts in the') .' '. '<b>' . __('description') . '</b>:</label>
		<input type="text" name="Keywords" size="20" maxlength="25" />
	</field>
	';

echo '<field>
		<label for="StockCode">' . '<b>' . __('OR') . ' </b>' . __('Enter extract of the') .' '. '<b>' . __('Stock Code') . '</b>:</label>
		<input type="text" name="StockCode" size="15" maxlength="18" />
	</field>
</fieldset>
';
echo '<div class="centre">
		<input type="submit" name="SearchParts" value="' . __('Search Parts Now') . '" />
		<input type="submit" name="ResetPart" value="' . __('Show All') . '" />
	</div>';

if (isset($StockItemsResult)) {
	echo '<table cellpadding="2" class="selection">
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

	$StocksStr = '(';
	$q = 0;
	while ($MyRow = DB_fetch_array($StockItemsResult)){
		if ($q>0) {
			$StockStr .=',';
		}
		$StockStr .="'".$MyRow['stockid']."'";

	}
	$StockStr .=')';
	$QOHSQL = "SELECT stockid, sum(quantity) FROM locstock INNER JOIN locationusers ON locationusers.loccode=locationusers.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' GROUP BY stockid";
	$ErrMsg = __('Failed to retrieve qoh');
	$QOHResult = DB_query($QOHSQL, $ErrMsg);
	$QOH = array();
	while ($MyRow=DB_fetch_array($QOHResult)){
		$QOH[$MyRow['stockid']] = $MyRow[1];
	}
	DB_data_seek($StockItemsResult,0);

	while ($MyRow = DB_fetch_array($StockItemsResult)) {
		$MyRow['qoh'] = $QOH[$MyRow['stockid']];

		echo '<tr class="striped_row">
				<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '"</td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['qord'],$MyRow['decimalplaces']), '</td>
				<td>', $MyRow['units'], '</td>
			</tr>';
	} //end of while loop through search items

	echo '</tbody></table>';

} //end if stock search results to show
else {
	//figure out the SQL required from the inputs available

	if (!isset($_POST['Status']) OR $_POST['Status'] == 'Pending_Authorised') {
		$StatusCriteria = " AND (purchorders.status='Pending' OR purchorders.status='Authorised' OR purchorders.status='Printed') ";
	} elseif ($_POST['Status'] == 'Authorised') {
		$StatusCriteria = " AND (purchorders.status='Authorised' OR purchorders.status='Printed')";
	} elseif ($_POST['Status'] == 'Pending') {
		$StatusCriteria = " AND purchorders.status='Pending' ";
	} elseif ($_POST['Status'] == 'Rejected') {
		$StatusCriteria = " AND purchorders.status='Rejected' ";
	} elseif ($_POST['Status'] == 'Cancelled') {
		$StatusCriteria = " AND purchorders.status='Cancelled' ";
	}
	if (isset($OrderNumber) AND $OrderNumber != '') {
		$SQL = "SELECT purchorders.orderno,
						purchorders.realorderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.deliverydate,
						purchorders.initiator,
						purchorders.status,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode,
						currencies.decimalplaces AS currdecimalplaces,
						group_concat(CASE WHEN quantityord>quantityrecd THEN CONCAT(itemcode,'--',round(quantityord-quantityrecd)) ELSE '' END) as bal,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
				FROM purchorders INNER JOIN purchorderdetails
				ON purchorders.orderno=purchorderdetails.orderno
				INNER JOIN locationusers
				ON purchorders.intostocklocation=locationusers.loccode
				AND userid='" . $_SESSION['UserID'] . "' AND canview = 1
				INNER JOIN suppliers
				ON purchorders.supplierno = suppliers.supplierid
				INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE purchorderdetails.completed=0
				AND purchorders.orderno='" . $OrderNumber . "'
				GROUP BY purchorders.orderno,
					suppliers.suppname,
					purchorders.orddate,
					purchorders.status,
					purchorders.initiator,
					purchorders.requisitionno,
					purchorders.allowprint,
					suppliers.currcode
				ORDER BY purchorders.orderno ASC";
	} else {
		//$OrderNumber is not set
		if (isset($SelectedSupplier)) {
			if (!isset($_POST['StockLocation'])) {
				if (isset($UserLocations) AND isset($LocQty) AND $UserLocations == $LocQty) {
					$WhereStockLocation = " AND purchorders.intostocklocation ='" . $_POST['StockLocation'] . "' ";
				} else {
					$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
					$WhereStockLocation = " AND purchorders.intostocklocation ='" . $_POST['StockLocation'] . "' ";
				}
			} else {
				if ($_POST['StockLocation'] == 'ALLLOC'){
					$WhereStockLocation = ' ';
				} else {
					$WhereStockLocation = " AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "' ";
				}
			}

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.realorderno,
							purchorders.orderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.deliverydate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces AS currdecimalplaces,
							group_concat(CASE WHEN quantityord>quantityrecd THEN CONCAT(itemcode,'--',round(quantityord-quantityrecd)) ELSE '' END) as bal,
							SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
						FROM purchorders INNER JOIN purchorderdetails
						ON purchorders.orderno = purchorderdetails.orderno
						INNER JOIN suppliers
						ON  purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies
						ON suppliers.currcode=currencies.currabrev
						INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE purchorderdetails.completed=0
						AND orddate>='" . $DateFrom . "'
						AND orddate<='" . $DateTo . "'
						AND purchorderdetails.itemcode='" . $SelectedStockItem . "'
						AND purchorders.supplierno='" . $SelectedSupplier . "'
						" . $WhereStockLocation
						 . $StatusCriteria . "
						GROUP BY purchorders.orderno,
							purchorders.realorderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces
						ORDER BY purchorders.orderno ASC";
			} else {
				$SQL = "SELECT purchorders.realorderno,
							purchorders.orderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.deliverydate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces AS currdecimalplaces,
							group_concat(CASE WHEN quantityord>quantityrecd THEN CONCAT(itemcode,'--',round(quantityord-quantityrecd)) ELSE '' END) as bal,
							SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
						FROM purchorders INNER JOIN purchorderdetails
						ON purchorders.orderno = purchorderdetails.orderno
						INNER JOIN suppliers
						ON  purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies
						ON suppliers.currcode=currencies.currabrev
						INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE purchorderdetails.completed=0
						AND orddate>='" . $DateFrom . "'
						AND orddate<='" . $DateTo . "'
						AND purchorders.supplierno='" . $SelectedSupplier . "'
						" . $WhereStockLocation
						 . $StatusCriteria . "
						GROUP BY purchorders.orderno,
							purchorders.realorderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces
						ORDER BY purchorders.orderno ASC";
			}
		} //isset($SelectedSupplier)
		else { //no supplier selected
			if (!isset($_POST['StockLocation'])) {
				if (isset($UserLocations) AND isset($LocQty) AND $UserLocations == $LocQty) {
					$WhereStockLocation = " ";
					$_POST['StockLocation'] = 'ALLLOC';
				} else {
					$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
					$WhereStockLocation = " AND purchorders.intostocklocation ='" . $_POST['StockLocation'] . "' ";
				}
			} else {
				if ($_POST['StockLocation'] == 'ALLLOC'){
					$WhereStockLocation = ' ';
				} else {
					$WhereStockLocation = " AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "'";
				}
			}
			if (isset($SelectedStockItem) AND isset($_POST['StockLocation'])) {
				$SQL = "SELECT purchorders.realorderno,
							purchorders.orderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.deliverydate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces AS currdecimalplaces,
							group_concat(CASE WHEN quantityord>quantityrecd THEN CONCAT(itemcode,'--',round(quantityord-quantityrecd)) ELSE '' END) as bal,
							SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
						FROM purchorders INNER JOIN purchorderdetails
						ON purchorders.orderno = purchorderdetails.orderno
						INNER JOIN suppliers
						ON  purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies
						ON suppliers.currcode=currencies.currabrev
						INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE purchorderdetails.completed=0
						AND orddate>='" . $DateFrom . "'
						AND orddate<='" . $DateTo . "'
						AND purchorderdetails.itemcode='" . $SelectedStockItem . "'
						" . $WhereStockLocation .
						 $StatusCriteria . "
						GROUP BY purchorders.orderno,
							purchorders.realorderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces
						ORDER BY purchorders.orderno ASC";
			} else {
				$SQL = "SELECT purchorders.realorderno,
							purchorders.orderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.deliverydate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces AS currdecimalplaces,
							group_concat(CASE WHEN quantityord>quantityrecd THEN CONCAT(itemcode,'--',round(quantityord-quantityrecd)) ELSE '' END) as bal,
							SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
						FROM purchorders INNER JOIN purchorderdetails
						ON purchorders.orderno = purchorderdetails.orderno
						INNER JOIN suppliers
						ON  purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies
						ON suppliers.currcode=currencies.currabrev
						INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE purchorderdetails.completed=0
						AND orddate>='" . $DateFrom . "'
						AND orddate<='" . $DateTo . "'
						" . $WhereStockLocation .
						  $StatusCriteria . "
						GROUP BY purchorders.orderno,
							purchorders.realorderno,
							suppliers.suppname,
							purchorders.orddate,
							purchorders.status,
							purchorders.initiator,
							purchorders.requisitionno,
							purchorders.allowprint,
							suppliers.currcode,
							currencies.decimalplaces
						ORDER BY purchorders.orderno ASC";
			}
		} //end selected supplier
	} //end not order number selected

	$ErrMsg = __('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PurchOrdersResult) > 0) {
	/*show a table of the orders returned by the SQL */

		echo '<table cellpadding="2" width="97%" class="selection">
			<thead>';

	if (isset($_POST['PODetails'])) {
		$BalHead = '<th class="SortedColumn">' . __('Balance') .' (' . __('Stock ID') . '--' . __('Quantity') . ' )</th>';
	} else {
		$BalHead = '';
	}
	echo '<tr>
			<th class="SortedColumn">' . __('Order #') . '</th>
			<th class="SortedColumn">' . __('Order Date') . '</th>
			<th class="SortedColumn">' . __('Delivery Date') . '</th>
			<th class="SortedColumn">' . __('Initiated by') . '</th>
			<th class="SortedColumn">' . __('Supplier') . '</th>
			' . $BalHead . '
			<th class="SortedColumn">' . __('Currency') . '</th>';

	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
		echo '<th class="SortedColumn">' . __('Order Total') . '</th>';
	}
	echo '<th class="SortedColumn">' . __('Status') . '</th>
			<th>' . __('Print') . '</th>
			<th>' . __('Receive') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($PurchOrdersResult)) {
		$Bal = '';
		if (isset($_POST['PODetails'])) {
			//lets retrieve the PO balance here to make it a standard sql query.
			$BalSql = "SELECT itemcode, quantityord - quantityrecd as balance FROM purchorderdetails WHERE orderno = '" . $MyRow['orderno'] . "'";
			$ErrMsg = __('Failed to retrieve purchorder details');
			$BalResult = DB_query($BalSql, $ErrMsg);
			if (DB_num_rows($BalResult)>0) {
				while ($BalRow = DB_fetch_array($BalResult)) {
					$Bal .= '<br/>' . $BalRow['itemcode'] . ' -- ' . $BalRow['balance'];
				}
			}
		}
		if (isset($_POST['PODetails'])) {
			$BalRow = '<td width="250" style="word-break:break-all">' . $Bal . '</td>';
		} else {
			$BalRow = '';
		}

		$ModifyPage = $RootPath . '/PO_Header.php?identifier=' . $identifier . '&ModifyOrderNumber=' . $MyRow['orderno'];
		if ($MyRow['status'] == 'Printed') {
			$ReceiveOrder = '<a href="' . $RootPath . '/GoodsReceived.php?PONumber=' . $MyRow['orderno'] . '">' . __('Receive') . '</a>';
		} else {
			$ReceiveOrder = '';
		}
		if ($MyRow['status'] == 'Authorised' AND $MyRow['allowprint'] == 1) {
			$PrintPurchOrder = '<a target="_blank" href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $MyRow['orderno'] . '">' . __('Print') . '</a>';
		} elseif ($MyRow['status'] == 'Authorisied' AND $MyRow['allowprint'] == 0) {
			$PrintPurchOrder = __('Printed');
		} elseif ($MyRow['status'] == 'Printed') {
			$PrintPurchOrder = '<a target="_blank" href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $MyRow['orderno'] . '&amp;realorderno=' . $MyRow['realorderno'] . '&amp;ViewingOnly=2">
				' . __('Print Copy') . '</a>';
		} else {
			$PrintPurchOrder = __('N/A');
		}


		$FormatedOrderDate = ConvertSQLDate($MyRow['orddate']);
		$FormatedDeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
		$FormatedOrderValue = locale_number_format($MyRow['ordervalue'], $MyRow['currdecimalplaces']);
		$SQL = "SELECT realname FROM www_users WHERE userid='" . $MyRow['initiator'] . "'";
		$UserResult = DB_query($SQL);
		$MyUserRow = DB_fetch_array($UserResult);
		if (isset($MyUserRow['realname'])) {
			$InitiatorName = $MyUserRow['realname'];
		} else {
			$InitiatorName = '';
		}

		echo '<tr class="striped_row">
				<td><a href="' . $ModifyPage . '">' . $MyRow['orderno'] . '</a></td>
				<td class="date">' . $FormatedOrderDate . '</td>
				<td class="date">' . $FormatedDeliveryDate . '</td>
				<td>' . $InitiatorName . '</td>
				<td>' . $MyRow['suppname'] . '</td>
				' . $BalRow . '
				<td>' . $MyRow['currcode'] . '</td>';
		if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
			echo '<td class="number">' . $FormatedOrderValue . '</td>';
		}
		echo '<td>' . __($MyRow['status']) . '</td>
				<td>' . $PrintPurchOrder . '</td>
				<td>' . $ReceiveOrder . '</td>
			</tr>';
	} //end of while loop around purchase orders retrieved

		echo '</tbody></table>';
	}
}
echo '</div>
      </form>';
include('includes/footer.php');
