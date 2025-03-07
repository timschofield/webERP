<?php
// POReport.php
// Inquiry on Purchase Orders

// If Date Type is Order, the main file is purchorderdetails
// If Date Type is Delivery, the main file is grns

include('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);};
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);};
$ViewTopic = 'PurchaseOrdering';/* ?????????? */
$BookMark = 'POReport';
$Title = _('Purchase Order Report');
include('includes/header.php');

# Sets default date range for current month
if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['submit']) or isset($_POST['submitcsv'])) {
	if (isset($_POST['PartNumber'])){
		$PartNumber = trim(mb_strtoupper($_POST['PartNumber']));
	} elseif (isset($_GET['PartNumber'])){
		$PartNumber = trim(mb_strtoupper($_GET['PartNumber']));
	}

	# Part Number operator - either LIKE or =
	$PartNumberOp = $_POST['PartNumberOp'];

	if (isset($_POST['SupplierId'])){
		$SupplierId = trim(mb_strtoupper($_POST['SupplierId']));
	} elseif (isset($_GET['SupplierId'])){
		$SupplierId = trim(mb_strtoupper($_GET['SupplierId']));
	}

	$SupplierIdOp = $_POST['SupplierIdOp'];

	$SupplierNameOp = $_POST['SupplierNameOp'];

	// Save $_POST['SummaryType'] in $SaveSummaryType because change $_POST['SummaryType'] when
	// create $SQL
	$SaveSummaryType = $_POST['SummaryType'];
}

if (isset($_POST['SupplierName'])){
	$SupplierName = trim(mb_strtoupper($_POST['SupplierName']));
} elseif (isset($_GET['SupplierName'])){
	$SupplierName = trim(mb_strtoupper($_GET['SupplierName']));
}

// Had to add supplierid to SummaryType when do summary by name because there could be several accounts
// with the same name. Tried passing 'suppname,supplierid' in form, but it only read 'suppname'
if (isset($_POST['SummaryType']) and $_POST['SummaryType'] == 'suppname') {
	$_POST['SummaryType'] = "suppname, suppliers.supplierid";
}

if (isset($_POST['submit'])) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';
	submit($PartNumber,$PartNumberOp,$SupplierId,$SupplierIdOp,$SupplierName,$SupplierNameOp,$SaveSummaryType);
} else if (isset($_POST['submitcsv'])) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';
	submitcsv($PartNumber,$PartNumberOp,$SupplierId,$SupplierIdOp,$SupplierName,$SupplierNameOp,$SaveSummaryType);
} else {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . $Title . '</p>';
	display();
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($PartNumber,$PartNumberOp,$SupplierId,$SupplierIdOp,$SupplierName,$SupplierNameOp,$SaveSummaryType) {

	global $RootPath;
	//initialize no input errors
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
	}

	# Add more to WHERE statement, if user entered something for the part number,supplierid, name
	$WherePart = ' ';
	if (mb_strlen($PartNumber) > 0 && $PartNumberOp == 'LIKE') {
		$PartNumber = $PartNumber . '%';
	} else {
		$PartNumberOp = '=';
	}
	if (mb_strlen($PartNumber) > 0) {
		$WherePart = " AND purchorderdetails.itemcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	} else {
		$WherePart=' ';
	}

	$WhereSupplierID = ' ';
	if ($SupplierIdOp == 'LIKE') {
		$SupplierId = $SupplierId . '%';
	} else {
		$SupplierIdOp = '=';
	}
	if (mb_strlen($SupplierId) > 0) {
		$WhereSupplierID = " AND purchorders.supplierno " . $SupplierIdOp . " '" . $SupplierId . "'  ";
	} else {
		$WhereSupplierID=' ';
	}

	$WhereSupplierName = ' ';
	if (mb_strlen($SupplierName) > 0 AND $SupplierNameOp == 'LIKE') {
		$SupplierName = $SupplierName . '%';
	} else {
		$SupplierNameOp = '=';
	}
	if (mb_strlen($SupplierName) > 0) {
		$WhereSupplierName = " AND suppliers.suppname " . $SupplierNameOp . " '" . $SupplierName . "'  ";
	} else {
		$WhereSupplierName=' ';
	}

	if (mb_strlen($_POST['OrderNo']) > 0) {
		$WhereOrderNo = " AND purchorderdetails.orderno = '" . $_POST['OrderNo'] . "'  ";
	} else {
		$WhereOrderNo=' ';
	}

	$WhereLineStatus = ' ';
	# Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
	#in WHERE clause because the WHERE clause didn't recognize
	# that had used the IF statement to create a field called linestatus
	if ($_POST['LineStatus'] != 'All') {
		if ($_POST['DateType'] == 'Order') {
			$WhereLineStatus = " AND IF(purchorderdetails.quantityord = purchorderdetails.qtyinvoiced ||
			  purchorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
		 } else {
			$WhereLineStatus = " AND IF(grns.qtyrecd - grns.quantityinv <> 0,'Open','Completed') = '"
			. $_POST['LineStatus'] . "'";
		 }
	}


	$WhereCategory = ' ';
	if ($_POST['Category'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
	}

	if ($InputError !=1) {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		if ($_POST['ReportType'] == 'Detail') {
			if ($_POST['DateType'] == 'Order') {
				$SQL = "SELECT purchorderdetails.orderno,
							   purchorderdetails.itemcode,
							   purchorderdetails.deliverydate,
							   purchorders.supplierno,
							   purchorders.orddate,
							   purchorderdetails.quantityord,
							   purchorderdetails.quantityrecd,
							   purchorderdetails.qtyinvoiced,
							   (purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
							   (purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
							   IF(purchorderdetails.quantityord = purchorderdetails.qtyinvoiced ||
								  purchorderdetails.completed = 1,'Completed','Open') as linestatus,
							   suppliers.suppname,
							   stockmaster.decimalplaces,
							   stockmaster.description
							   FROM purchorderdetails
						LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
						LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
						WHERE purchorders.orddate >='$FromDate'
						 AND purchorders.orddate <='$ToDate'
						$WherePart
						$WhereSupplierID
						$WhereSupplierName
						$WhereOrderNo
						$WhereLineStatus
						$WhereCategory
						ORDER BY " . $_POST['SortBy'];
			} else {
				// Selects by delivery date from grns
				$SQL = "SELECT purchorderdetails.orderno,
							   purchorderdetails.itemcode,
							   grns.deliverydate,
							   purchorders.supplierno,
							   purchorders.orddate,
							   purchorderdetails.quantityord as quantityrecd,
							   grns.qtyrecd as quantityord,
							   grns.quantityinv as qtyinvoiced,
							   (grns.qtyrecd * purchorderdetails.unitprice) as extprice,
							   (grns.qtyrecd * grns.stdcostunit) as extcost,
							   IF(grns.qtyrecd - grns.quantityinv <> 0,'Open','Completed') as linestatus,
							   suppliers.suppname,
							   stockmaster.decimalplaces,
							   stockmaster.description
							   FROM grns
						LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
						LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
						LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
						WHERE grns.deliverydate >='$FromDate'
						 AND grns.deliverydate <='$ToDate'
						$WherePart
						$WhereSupplierID
						$WhereSupplierName
						$WhereOrderNo
						$WhereLineStatus
						$WhereCategory
						ORDER BY " . $_POST['SortBy'];
			}
		} else {
			// sql for Summary report
			$OrderBy = $_POST['SummaryType'];
			// The following is because the 'extprice' summary is a special case - with the other
			// summaries, you group and order on the same field; with 'extprice', you are actually
			// grouping on the stkcode and ordering by extprice descending
			if ($_POST['SummaryType'] == 'extprice') {
				$_POST['SummaryType'] = 'itemcode';
				$OrderBy = 'extprice DESC';
			}
			if ($_POST['DateType'] == 'Order') {
				if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'itemcode') {
					$SQL = "SELECT purchorderdetails.itemcode,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   stockmaster.decimalplaces,
								   stockmaster.description
								   FROM purchorderdetails
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							',stockmaster.decimalplaces,
							  stockmaster.description
							ORDER BY ' . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT purchorderdetails.orderno,
								   purchorders.supplierno,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM purchorderdetails
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							',purchorders.supplierno,
							  suppliers.suppname
							ORDER BY ' . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'supplierno' || $_POST['SummaryType'] == 'suppname,suppliers.supplierid') {
					$SQL = "SELECT purchorders.supplierno,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM purchorderdetails
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",purchorders.supplierno,
							  suppliers.suppname
							ORDER BY " . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from purchorders.orddate) as month,
								   CONCAT(MONTHNAME(purchorders.orddate),' ',YEAR(purchorders.orddate)) as monthname,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost
								   FROM purchorderdetails
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", monthname
							ORDER BY " . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   stockmaster.categoryid,
								   stockcategory.categorydescription
								   FROM purchorderdetails
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", categorydescription
							ORDER BY " . $OrderBy;
				}
			} else {
					// Selects by delivery date from grns
				if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'itemcode') {
					$SQL = "SELECT purchorderdetails.itemcode,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost,
								   stockmaster.description
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", stockmaster.description
							ORDER BY " . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT purchorderdetails.orderno,
								   purchorders.supplierno,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							', purchorders.supplierno,
							   suppliers.suppname
							ORDER BY ' . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'supplierno' || $_POST['SummaryType'] == 'suppname,suppliers.supplierid') {
					$SQL = "SELECT purchorders.supplierno,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							', purchorders.supplierno,
							   suppliers.suppname
							ORDER BY ' . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from purchorders.orddate) as month,
								   CONCAT(MONTHNAME(purchorders.orddate),' ',YEAR(purchorders.orddate)) as monthname,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							',monthname
							ORDER BY ' . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",categorydescription
							ORDER BY " . $OrderBy;
				}
			}
		} // End of if ($_POST['ReportType']
		//echo "<br/>$SQL<br/>";
		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$Result = DB_query($SQL,$ErrMsg);
		$ctr = 0;
		$TotalQty = 0;
		$TotalRecdQty = 0;
		$TotalExtCost = 0;
		$TotalExtPrice = 0;
		$TotalInvQty = 0;

		// Create array for summary type to display in header. Access it with $SaveSummaryType
		$Summary_Array['orderno'] =  _('Order Number');
		$Summary_Array['itemcode'] =  _('Part Number');
		$Summary_Array['extprice'] =  _('Extended Price');
		$Summary_Array['supplierno'] =  _('Customer Number');
		$Summary_Array['suppname'] =  _('Customer Name');
		$Summary_Array['month'] =  _('Month');
		$Summary_Array['categoryid'] =  _('Stock Category');

		// Create array for sort for detail report to display in header
		$Detail_Array['purchorderdetails.orderno'] = _('Order Number');
		$Detail_Array['purchorderdetails.itemcode'] = _('Part Number');
		$Detail_Array['suppliers.supplierid,purchorderdetails.orderno'] = _('Supplier Number');
		$Detail_Array['suppliers.suppname,suppliers.supplierid,purchorderdetails.orderno'] = _('Supplier Name');

		// Display Header info
		echo '<table class="selection">';
		if ($_POST['ReportType'] == 'Summary') {
			$SortBy_Display = $Summary_Array[$SaveSummaryType];
		} else {
			$SortBy_Display = $Detail_Array[$_POST['SortBy']];
		}
		echo '<tr>
				<th colspan="2">' . _('Header Details') . '</th>
			</tr>';
		echo '<tr class="striped_row">
				<td>' . _('Purchase Order Report') . '</td>
				<td>' . $_POST['ReportType'] . ' ' . _('By') . ' '.$SortBy_Display  . '</td>
			</tr>';
		echo '<tr class="striped_row">
				<td>' . _('Date Type') . '</td>
				<td>' . $_POST['DateType'] . '</td>
			</tr>';
		echo '<tr class="striped_row">
				<td>' . _('Date Range') . '</td>
				<td>' . $_POST['FromDate'] . ' ' . _('To') . ' ' .  $_POST['ToDate'] . '</td>
			</tr>';
		if (mb_strlen(trim($PartNumber)) > 0) {
			echo '<tr class="striped_row">
					<td>' . _('Part Number') . '</td>
					<td>' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '</td>
				</tr>';
		}
		if (mb_strlen(trim($_POST['SupplierId'])) > 0) {
			echo '<tr class="striped_row">
					<td>' . _('Supplier Number') . '</td>
					<td>' . $_POST['SupplierIdOp'] . ' ' . $_POST['SupplierId'] . '</td>
				</tr>';
		}
		if (mb_strlen(trim($_POST['SupplierName'])) > 0) {
			echo '<tr class="striped_row">
					<td>' . _('Supplier Name') . '</td>
					<td>' . $_POST['SupplierNameOp'] . ' ' . $_POST['SupplierName'] . '</td>
				</tr>';
		}
		echo '<tr class="striped_row">
				<td>' . _('Line Item Status') . '</td>
				<td>' . $_POST['LineStatus'] . '</td>
			</tr>';
		echo '<tr class="striped_row">
				<td>' . _('Stock Category') . '</td>
				<td>' . $_POST['Category'] . '</td>
			</tr>
		</table>';

		if ($_POST['ReportType'] == 'Detail') {
			echo '<table class="selection" width="98%">';
			if ($_POST['DateType'] == 'Order') {
				echo '<tr>
						<th>' . _('Order No') . '</th>
						<th>' . _('Part Number') . '</th>
						<th>' . _('Order Date') . '</th>
						<th>' . _('Supplier No') . '</th>
						<th>' . _('Supplier Name') . '</th>
						<th>' . _('Order Qty') . '</th>
						<th>' . _('Qty Received') . '</th>
						<th>' . _('Extended Cost') . '</th>
						<th>' . _('Extended Price') . '</th>
						<th>' . _('Invoiced Qty') . '</th>
						<th>' . _('Line Status') . '</th>
						<th>' . _('Item Due') . '</th>
						<th>' . _('Part Description') . '</th>
					</tr>';

				$Linectr = 0;

				while ($MyRow = DB_fetch_array($Result)) {
					$Linectr++;

				   // Detail for both DateType of Order
					echo '<tr class="striped_row">
							<td><a href="'. $RootPath . '/PO_OrderDetails.php?OrderNo=', $MyRow['orderno'], '">', $MyRow['orderno'], '</a></td>
							<td>', $MyRow['itemcode'], '</td>
							<td>', ConvertSQLDate($MyRow['orddate']), '</td>
							<td>', $MyRow['supplierno'], '</td>
							<td>', $MyRow['suppname'], '</td>
							<td class="number">', locale_number_format($MyRow['quantityord'],$MyRow['decimalplaces']), '</td>
							<td class="number">', locale_number_format($MyRow['quantityrecd'],$MyRow['decimalplaces']), '</td>
							<td class="number">', locale_number_format($MyRow['extcost'],2), '</td>
							<td class="number">', locale_number_format($MyRow['extprice'],2), '</td>
							<td class="number">', locale_number_format($MyRow['qtyinvoiced'],$MyRow['decimalplaces']), '</td>
							<td>', $MyRow['linestatus'], '</td>
							<td>', ConvertSQLDate($MyRow['deliverydate']), '</td>
							<td>', $MyRow['description'], '</td>
						</tr>';
							$LastDecimalPlaces = $MyRow['decimalplaces'];
							$TotalQty += $MyRow['quantityord'];
							$TotalRecdQty += $MyRow['quantityrecd'];
							$TotalExtCost += $MyRow['extcost'];
							$TotalExtPrice += $MyRow['extprice'];
							$TotalInvQty += $MyRow['qtyinvoiced'];
				} //END WHILE LIST LOOP
				// Print totals
					echo '<tr class="total_row">
							<td>', _('Totals'), '</td>
							<td>', _('Lines - ') . $Linectr, '</td>
							<td>', ' ', '</td>
							<td>', ' ', '</td>
							<td>', ' ', '</td>
							<td class="number">', locale_number_format($TotalQty,2), '</td>
							<td class="number">', locale_number_format($TotalRecdQty,2), '</td>
							<td class="number">', locale_number_format($TotalExtCost,2), '</td>
							<td class="number">', locale_number_format($TotalExtPrice,2), '</td>
							<td class="number">', locale_number_format($TotalInvQty,2), '</td>
							<td>', ' ', '</td>
							<td colspan="2"></td>
							</tr>';
			} else {
			  // Header for Date Type of Delivery Date
				echo '<tr>
						<th>' . _('Order No') . '</th>
						<th>' . _('Part Number') . '</th>
						<th>' . _('Order Date') . '</th>
						<th>' . _('Supplier No') . '</th>
						<th>' . _('Supplier Name') . '</th>
						<th>' . _('Order Qty') . '</th>
						<th>' . _('Received')  . '</th>
						<th>' . _('Extended Cost') . '</th>
						<th>' . _('Extended Price') . '</th>
						<th>' . _('Invoiced Qty') . '</th>
						<th>' . _('Line Status') . '</th>
						<th>' . _('Delivered') . '</th>
						<th>' . _('Part Description') . '</th>
						</tr>';

				$Linectr = 0;

				while ($MyRow = DB_fetch_array($Result)) {
					$Linectr++;

					// Detail for both DateType of Ship
					// In sql, had to alias grns.qtyrecd as quantityord so could use same name here
					echo '<tr class="striped_row">
							<td>', $MyRow['orderno'], '</td>
							<td>', $MyRow['itemcode'], '</td>
							<td>', ConvertSQLDate($MyRow['orddate']), '</td>
							<td>', $MyRow['supplierno'], '</td>
							<td>', $MyRow['suppname'], '</td>
							<td class="number">', locale_number_format($MyRow['quantityrecd'],$MyRow['decimalplaces']), '</td>
							<td class="number">', locale_number_format($MyRow['quantityord'],$MyRow['decimalplaces']), '</td>
							<td class="number">', locale_number_format($MyRow['extcost'],2), '</td>
							<td class="number">', locale_number_format($MyRow['extprice'],2), '</td>
							<td class="number">', locale_number_format($MyRow['qtyinvoiced'],$MyRow['decimalplaces']), '</td>
							<td>', $MyRow['linestatus'], '</td>
							<td>', ConvertSQLDate($MyRow['deliverydate']), '</td>
							<td>', $MyRow['description'], '</td>
						</tr>';

					$LastDecimalPlaces = $MyRow['decimalplaces'];
					$TotalQty += $MyRow['quantityord'];
					$TotalExtCost += $MyRow['extcost'];
					$TotalExtPrice += $MyRow['extprice'];
					$TotalInvQty += $MyRow['qtyinvoiced'];
				} //END WHILE LIST LOOP
				// Print totals
					echo '<tr class="total_row">
							<td>', _('Totals'), '</td>
							<td>', _('Lines - ') . $Linectr, '</td>
							<td>', ' ', '</td>
							<td>', ' ', '</td>
							<td>', ' ', '</td>
							<td class="number">', locale_number_format($TotalQty,$LastDecimalPlaces), '</td>
							<td class="number">', locale_number_format($TotalExtCost,2), '</td>
							<td class="number">', locale_number_format($TotalExtPrice,2), '</td>
							<td class="number">', locale_number_format($TotalInvQty,$LastDecimalPlaces), '</td>
							<td>', ' ', '</td>
							<td>', ' ', '</td>
						</tr>';
			}
			echo '</table>';
		} else {
		  // Print summary stuff
			echo '<br /><table class="selection" width="98%">';
			$SummaryType = $_POST['SummaryType'];
			// For SummaryType 'suppname' had to add supplierid to it for the GROUP BY in the sql,
			// but have to take it away for $MyRow[$SummaryType] to be valid
			// Set up description based on the Summary Type
			if ($SummaryType == "suppname,suppliers.supplierid") {
				$SummaryType = "suppname";
				$Description = 'supplierno';
				$SummaryHeader = _('Supplier Name');
				$DescriptionHeader = _('Supplier Number');
			}
			if ($SummaryType == 'itemcode' || $SummaryType == 'extprice') {
				$Description = 'description';
				$SummaryHeader = _('Part Number');
				$DescriptionHeader = _('Part Description');
			}
			if ($SummaryType == 'supplierno') {
				$Description = 'suppname';
				$SummaryHeader = _('Supplier Number');
				$DescriptionHeader = _('Supplier Name');
			}
			if ($SummaryType == 'orderno') {
				$Description = 'supplierno';
				$SummaryHeader = _('Order Number');
				$DescriptionHeader = _('Supplier Number');
			}
			if ($SummaryType == 'categoryid') {
				$Description = 'categorydescription';
				$SummaryHeader = _('Stock Category');
				$DescriptionHeader = _('Category Description');
			}
			$SummaryDesc = $SummaryHeader;
			if ($OrderBy == 'extprice DESC') {
				$SummaryDesc = _('Extended Price');
			}
			if ($SummaryType == 'month') {
				$Description = 'monthname';
				$SummaryHeader = _('Month');
				$DescriptionHeader = _('Month');
			}
			echo '<tr>
					<th>', _($SummaryHeader), '</th>
					<th>', _($DescriptionHeader), '</th>
					<th>', _('Quantity'), '</th>
					<th>', _('Extended Cost'), '</th>
					<th>', _('Extended Price'), '</th>
					<th>', _('Invoiced Qty'), '</th>
					<th></th>
				</tr>';

			$SuppName = ' ';
			$Linectr = 0;
			DB_free_result($Result);

			while ($MyRow = DB_fetch_array($Result)) {
				$Linectr++;
				if ($SummaryType == 'orderno') {
					$SuppName = $MyRow['suppname'];
				}

				echo '<tr class="striped_row">
						<td>', $MyRow[$SummaryType], '</td>
						<td>', $MyRow[$Description], '</td>
						<td class="number">', $MyRow['quantityord'], '</td>
						<td class="number">', locale_number_format($MyRow['extcost'],2), '</td>
						<td class="number">', locale_number_format($MyRow['extprice'],2), '</td>
						<td class="number">', $MyRow['qtyinvoiced'], '</td>
						<td>', $SuppName, '</td>
					</tr>';
				$TotalQty += $MyRow['quantityord'];
				$TotalExtCost += $MyRow['extcost'];
				$TotalExtPrice += $MyRow['extprice'];
				$TotalInvQty += $MyRow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
				echo '<tr class="total_row">
						<td>', _('Totals'), '</td>
						<td>', _('Lines - ') . $Linectr, '</td>
						<td class="number">', locale_number_format($TotalQty,2), '</td>
						<td class="number">', locale_number_format($TotalExtCost,2), '</td>
						<td class="number">', locale_number_format($TotalExtPrice,2), '</td>
						<td class="number">', locale_number_format($TotalInvQty,2), '</td>
						<td></td>
					</tr>';
			echo '</table>';
		} // End of if ($_POST['ReportType']
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="ReportType" value="'.$_POST['ReportType'].'" />';
		echo '<input type="hidden" name="DateType" value="'.$_POST['DateType'].'" />';
		echo '<input type="hidden" name="FromDate" value="'.$_POST['FromDate'].'" />';
		echo '<input type="hidden" name="ToDate" value="'.$_POST['ToDate'].'" />';
		echo '<input type="hidden" name="PartNumberOp" value="'.$_POST['PartNumberOp'].'" />';
		echo '<input type="hidden" name="PartNumber" value="'.$_POST['PartNumber'].'" />';
		echo '<input type="hidden" name="SupplierIdOp" value="'.$_POST['SupplierIdOp'].'" />';
		echo '<input type="hidden" name="SupplierId" value="'.$_POST['SupplierId'].'" />';
		echo '<input type="hidden" name="SupplierNameOp" value="'.$_POST['SupplierNameOp'].'" />';
		echo '<input type="hidden" name="SupplierName" value="'.$_POST['SupplierName'].'" />';
		echo '<input type="hidden" name="OrderNo" value="'.$_POST['OrderNo'].'" />';
		echo '<input type="hidden" name="LineStatus" value="'.$_POST['LineStatus'].'" />';
		echo '<input type="hidden" name="Category" value="'.$_POST['Category'].'" />';
		echo '<input type="hidden" name="SortBy" value="'.$_POST['SortBy'].'" />';
		echo '<input type="hidden" name="SummaryType" value="'.$_POST['SummaryType'].'" />';
		echo '<br /><div class="centre"><input type="submit" name="submitcsv" value="' . _('Export as csv file') . '" /></div>';
		echo '</div>
			  </form>';
	} // End of if inputerror != 1
} // End of function submit()

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submitcsv( $PartNumber,
					$PartNumberOp,
					$SupplierId,
					$SupplierIdOp,
					$SupplierName,
					$SupplierNameOp,
					$SaveSummaryType) {

	//initialize no input errors
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
	}
	if (!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
	}

	# Add more to WHERE statement, if user entered something for the part number,supplierid, name
	$WherePart = ' ';
	if (mb_strlen($PartNumber) > 0 && $PartNumberOp == 'LIKE') {
		$PartNumber = $PartNumber . '%';
	} else {
		$PartNumberOp = '=';
	}
	if (mb_strlen($PartNumber) > 0) {
		$WherePart = " AND purchorderdetails.itemcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	} else {
		$WherePart=' ';
	}

	$WhereSupplierID = ' ';
	if ($SupplierIdOp == 'LIKE') {
		$SupplierId = $SupplierId . '%';
	} else {
		$SupplierIdOp = '=';
	}
	if (mb_strlen($SupplierId) > 0) {
		$WhereSupplierID = " AND purchorders.supplierno " . $SupplierIdOp . " '" . $SupplierId . "'  ";
	} else {
		$WhereSupplierID=' ';
	}

	$WhereSupplierName = ' ';
	if (mb_strlen($SupplierName) > 0 && $SupplierNameOp == 'LIKE') {
		$SupplierName = $SupplierName . '%';
	} else {
		$SupplierNameOp = '=';
	}
	if (mb_strlen($SupplierName) > 0) {
		$WhereSupplierName = " AND suppliers.suppname " . $SupplierNameOp . " '" . $SupplierName . "'  ";
	} else {
		$WhereSupplierName=' ';
	}

	if (mb_strlen($_POST['OrderNo']) > 0) {
		$WhereOrderNo = " AND purchorderdetails.orderno = '" . $_POST['OrderNo'] . "'  ";
	} else {
		$WhereOrderNo=' ';
	}

	$WhereLineStatus = ' ';
	# Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
	#in WHERE clause because the WHERE clause didn't recognize
	# that had used the IF statement to create a field called linestatus
	if ($_POST['LineStatus'] != 'All') {
		if ($_POST['DateType'] == 'Order') {
			$WhereLineStatus = " AND IF(purchorderdetails.quantityord = purchorderdetails.qtyinvoiced ||
			  purchorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
		 } else {
			$WhereLineStatus = " AND IF(grns.qtyrecd - grns.quantityinv <> 0,'Open','Completed') = '"
			. $_POST['LineStatus'] . "'";
		 }
	}


	$WhereCategory = ' ';
	if ($_POST['Category'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
	}

	if ($InputError !=1) {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		if ($_POST['ReportType'] == 'Detail') {
			if ($_POST['DateType'] == 'Order') {
				$SQL = "SELECT purchorderdetails.orderno,
							   purchorderdetails.itemcode,
							   purchorderdetails.deliverydate,
							   purchorders.supplierno,
							   purchorders.orddate,
							   purchorderdetails.quantityrecd,
							   purchorderdetails.quantityord,
							   purchorderdetails.qtyinvoiced,
							   (purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
							   (purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
							   IF(purchorderdetails.quantityord = purchorderdetails.qtyinvoiced ||
								  purchorderdetails.completed = 1,'Completed','Open') as linestatus,
							   suppliers.suppname,
							   stockmaster.decimalplaces,
							   stockmaster.description
							   FROM purchorderdetails
						INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
						INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
						WHERE purchorders.orddate >='$FromDate'
						 AND purchorders.orddate <='$ToDate'
						$WherePart
						$WhereSupplierID
						$WhereSupplierName
						$WhereOrderNo
						$WhereLineStatus
						$WhereCategory
						ORDER BY " . $_POST['SortBy'];
			} else {
				// Selects by delivery date from grns
				$SQL = "SELECT purchorderdetails.orderno,
							   purchorderdetails.itemcode,
							   grns.deliverydate,
							   purchorders.supplierno,
							   purchorders.orddate,
							   purchorderdetails.quantityord as quantityrecd,
							   grns.qtyrecd as quantityord,
							   grns.quantityinv as qtyinvoiced,
							   (grns.qtyrecd * purchorderdetails.unitprice) as extprice,
							   (grns.qtyrecd * grns.stdcostunit) as extcost,
							   IF(grns.qtyrecd - grns.quantityinv <> 0,'Open','Completed') as linestatus,
							   suppliers.suppname,
							   stockmaster.decimalplaces,
							   stockmaster.description
							   FROM grns
						LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
						INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
						INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
						WHERE grns.deliverydate >='$FromDate'
						 AND grns.deliverydate <='$ToDate'
						$WherePart
						$WhereSupplierID
						$WhereSupplierName
						$WhereOrderNo
						$WhereLineStatus
						$WhereCategory
						ORDER BY " . $_POST['SortBy'];
		   }
		} else {
		  // sql for Summary report
		  $OrderBy = $_POST['SummaryType'];
		  // The following is because the 'extprice' summary is a special case - with the other
		  // summaries, you group and order on the same field; with 'extprice', you are actually
		  // grouping on the stkcode and ordering by extprice descending
		  if ($_POST['SummaryType'] == 'extprice') {
			  $_POST['SummaryType'] = 'itemcode';
			  $OrderBy = 'extprice DESC';
		  }
		  if ($_POST['DateType'] == 'Order') {
				if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'itemcode') {
					$SQL = "SELECT purchorderdetails.itemcode,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   stockmaster.decimalplaces,
								   stockmaster.description
								   FROM purchorderdetails
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",stockmaster.decimalplaces,
							  stockmaster.description
							ORDER BY " . $OrderBy;
			   } elseif ($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT purchorderdetails.orderno,
								   purchorders.supplierno,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM purchorderdetails
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",purchorders.supplierno,
							  suppliers.suppname
							ORDER BY " . $OrderBy;
			} elseif ($_POST['SummaryType'] == 'supplierno' || $_POST['SummaryType'] == 'suppname,suppliers.supplierid') {
					$SQL = "SELECT purchorders.supplierno,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM purchorderdetails
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",purchorders.supplierno,
							  suppliers.suppname
							ORDER BY " . $OrderBy;
			} elseif ($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from purchorders.orddate) as month,
								   CONCAT(MONTHNAME(purchorders.orddate),' ',YEAR(purchorders.orddate)) as monthname,
								   SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost
								   FROM purchorderdetails
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", monthname
							ORDER BY " . $OrderBy;
			} elseif ($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT SUM(purchorderdetails.quantityord) as quantityord,
								   SUM(purchorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) as extprice,
								   SUM(purchorderdetails.quantityord * purchorderdetails.stdcostunit) as extcost,
								   stockmaster.categoryid,
								   stockcategory.categorydescription
								   FROM purchorderdetails
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE purchorders.orddate >='$FromDate'
							 AND purchorders.orddate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", categorydescription
							ORDER BY " . $OrderBy;
			}
		} else {
					// Selects by delivery date from grns
				if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'itemcode') {
					$SQL = "SELECT purchorderdetails.itemcode,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost,
								   stockmaster.description
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", stockmaster.description
							ORDER BY " . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT purchorderdetails.orderno,
								   purchorders.supplierno,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", purchorders.supplierno,
							   suppliers.suppname
							ORDER BY " . $OrderBy;
			} elseif ($_POST['SummaryType'] == 'supplierno' || $_POST['SummaryType'] == 'suppname,suppliers.supplierid') {
					$SQL = "SELECT purchorders.supplierno,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost,
								   suppliers.suppname
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							", purchorders.supplierno,
							   suppliers.suppname
							ORDER BY " . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from purchorders.orddate) as month,
								   CONCAT(MONTHNAME(purchorders.orddate),' ',YEAR(purchorders.orddate)) as monthname,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",monthname
							ORDER BY " . $OrderBy;
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(grns.qtyrecd) as quantityord,
								   SUM(grns.quantityinv) as qtyinvoiced,
								   SUM(grns.qtyrecd * purchorderdetails.unitprice) as extprice,
								   SUM(grns.qtyrecd * grns.stdcostunit) as extcost
								   FROM grns
							LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem
							INNER JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
							INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
							LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
							INNER JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							WHERE grns.deliverydate >='$FromDate'
							 AND grns.deliverydate <='$ToDate'
							$WherePart
							$WhereSupplierID
							$WhereSupplierName
							$WhereOrderNo
							$WhereLineStatus
							$WhereCategory
							GROUP BY " . $_POST['SummaryType'] .
							",categorydescription
							ORDER BY " . $OrderBy;
				}
			}
		} // End of if ($_POST['ReportType']
		//echo "<br/>$SQL<br/>";
		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$Result = DB_query($SQL,$ErrMsg);
		$ctr = 0;
		$TotalQty = 0;
		$TotalExtCost = 0;
		$TotalExtPrice = 0;
		$TotalInvQty = 0;
		$FileName = $_SESSION['reports_dir'] .'/POReport.csv';
		$FileHandle = fopen($FileName, 'w');
		// Create array for summary type to display in header. Access it with $SaveSummaryType
		$Summary_Array['orderno'] =  _('Order Number');
		$Summary_Array['itemcode'] =  _('Part Number');
		$Summary_Array['extprice'] =  _('Extended Price');
		$Summary_Array['supplierno'] =  _('Customer Number');
		$Summary_Array['suppname'] =  _('Customer Name');
		$Summary_Array['month'] =  _('Month');
		$Summary_Array['categoryid'] =  _('Stock Category');

		// Create array for sort for detail report to display in header
		$Detail_Array['purchorderdetails.orderno'] = _('Order Number');
		$Detail_Array['purchorderdetails.itemcode'] = _('Part Number');
		$Detail_Array['suppliers.supplierid,purchorderdetails.orderno'] = _('Supplier Number');
		$Detail_Array['suppliers.suppname,suppliers.supplierid,purchorderdetails.orderno'] = _('Supplier Name');

		// Display Header info
		if ($_POST['ReportType'] == 'Summary') {
			$SortBy_Display = $Summary_Array[$SaveSummaryType];
		} else {
			$SortBy_Display = $Detail_Array[$_POST['SortBy']];
		}
		fprintf($FileHandle, '"'. _('Purchase Order Report') . '","' . $_POST['ReportType'] . ' '._('By').' '.$SortBy_Display ."\n");
		fprintf($FileHandle, '"'. _('Date Type') . '","' . $_POST['DateType'] . '"'. "\n");
		fprintf($FileHandle, '"'. _('Date Range') . '","' . $_POST['FromDate'] . ' ' . _('To') . ' ' .  $_POST['ToDate'] . '"'."\n");
		if (mb_strlen(trim($PartNumber)) > 0) {
			fprintf($FileHandle, '"'. _('Part Number') . '","' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '"'."\n");
		}
		if (mb_strlen(trim($_POST['SupplierId'])) > 0) {
			fprintf($FileHandle, '"'. _('Supplier Number') . '","' . $_POST['SupplierIdOp'] . ' ' . $_POST['SupplierId'] . '"'."\n");
		}
		if (mb_strlen(trim($_POST['SupplierName'])) > 0) {
			fprintf($FileHandle, '"'. _('Supplier Name') . '","' . $_POST['SupplierNameOp'] . ' ' . $_POST['SupplierName'] . '"'."\n");
		}
		fprintf($FileHandle, '"'._('Line Item Status') . '","' . $_POST['LineStatus'] . '"'."\n");
		fprintf($FileHandle, '"'. _('Stock Category') . '","' . $_POST['Category'] . '"'."\n");

		if ($_POST['ReportType'] == 'Detail') {
			if ($_POST['DateType'] == 'Order') {
				fprintf($FileHandle, '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"'."\n",
					 _('Order No'),
					 _('Part Number'),
					 _('Order Date'),
					 _('Supplier No'),
					 _('Supplier Name'),
					 _('Order Qty'),
					 _('Qty Received'),
					 _('Extended Cost'),
					 _('Extended Price'),
					 _('Invoiced Qty'),
					 _('Line Status'),
					 _('Item Due'),
					 _('Part Description'));
					$Linectr = 0;
				while ($MyRow = DB_fetch_array($Result)) {
					$Linectr++;
				   // Detail for both DateType of Order
					fprintf($FileHandle, '"%s","%s","%s","%s","%s",%s,%s,%s,%s,%s,"%s","%s","%s"'."\n",
					$MyRow['orderno'],
					$MyRow['itemcode'],
					ConvertSQLDate($MyRow['orddate']),
					$MyRow['supplierno'],
					str_replace(',',' ',$MyRow['suppname']),
					round($MyRow['quantityord'],$MyRow['decimalplaces']),
					round($MyRow['quantityrecd'],$MyRow['decimalplaces']),
					round($MyRow['extcost'],2),
					round($MyRow['extprice'],2),
					round($MyRow['qtyinvoiced'],$MyRow['decimalplaces']),
					$MyRow['linestatus'],
					ConvertSQLDate($MyRow['deliverydate']),
					str_replace(',',' ',$MyRow['description']));
					$LastDecimalPlaces = $MyRow['decimalplaces'];
					$TotalQty += $MyRow['quantityord'];
					$TotalExtCost += $MyRow['extcost'];
					$TotalExtPrice += $MyRow['extprice'];
					$TotalInvQty += $MyRow['qtyinvoiced'];
				} //END WHILE LIST LOOP
				// Print totals
					fprintf($FileHandle, '"%s","%s","%s","%s","%s",%s,%s,%s,%s,"%s","%s"'."\n",
					'Totals',
					_('Lines - ') . $Linectr,
					' ',
					' ',
					' ',
					round($TotalQty,2),
					round($TotalExtCost,2),
					round($TotalExtPrice,2),
					round($TotalInvQty,2),
					' ',
					' ');
			} else {
			  // Header for Date Type of Delivery Date
				fprintf($FileHandle, '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"'."\n",
					_('Order No'),
					_('Part Number'),
					 _('Order Date'),
					 _('Supplier No'),
					 _('Supplier Name'),
					 _('Order Qty'),
					 _('Received'),
					 _('Extended Cost'),
					 _('Extended Price'),
					 _('Invoiced Qty'),
					 _('Line Status'),
					 _('Delivered'),
					 _('Part Description'));
					$Linectr = 0;
				while ($MyRow = DB_fetch_array($Result)) {
					$Linectr++;
				   // Detail for both DateType of Ship
				   // In sql, had to alias grns.qtyrecd as quantityord so could use same name here
					fprintf($FileHandle, '"%s","%s","%s","%s","%s",%s,%s,%s,%s,%s,"%s","%s","%s"'."\n",
					$MyRow['orderno'],
					$MyRow['itemcode'],
					ConvertSQLDate($MyRow['orddate']),
					$MyRow['supplierno'],
					str_replace(',',' ',$MyRow['suppname']),
					round($MyRow['quantityrecd'],$MyRow['decimalplaces']),
					round($MyRow['quantityord'],$MyRow['decimalplaces']),
					round($MyRow['extcost'],2),
					round($MyRow['extprice'],2),
					round($MyRow['qtyinvoiced'],$MyRow['decimalplaces']),
					$MyRow['linestatus'],
					ConvertSQLDate($MyRow['deliverydate']),
					str_replace(',',' ',$MyRow['description']));
					$LastDecimalPlaces = $MyRow['decimalplaces'];
					$TotalQty += $MyRow['quantityord'];
					$TotalExtCost += $MyRow['extcost'];
					$TotalExtPrice += $MyRow['extprice'];
					$TotalInvQty += $MyRow['qtyinvoiced'];
				} //END WHILE LIST LOOP
				// Print totals
					fprintf($FileHandle, '"%s","%s","%s","%s","%s",%s,%s,%s,%s,"%s","%s"'."\n",
					'Totals',
					_('Lines - ') . $Linectr,
					' ',
					' ',
					' ',
					round($TotalQty,$LastDecimalPlaces),
					round($TotalExtCost,2),
					round($TotalExtPrice,2),
					round($TotalInvQty,$LastDecimalPlaces),
					" ",
					" ");
			}
		} else {
		  // Print summary stuff
			$SummaryType = $_POST['SummaryType'];
			// For SummaryType 'suppname' had to add supplierid to it for the GROUP BY in the sql,
			// but have to take it away for $MyRow[$SummaryType] to be valid
			// Set up description based on the Summary Type
			if ($SummaryType == 'suppname,suppliers.supplierid') {
				$SummaryType = 'suppname';
				$Description = 'supplierno';
				$SummaryHeader = _('Supplier Name');
				$DescriptionHeader = _('Supplier Number');
			}
			if ($SummaryType == 'itemcode' || $SummaryType == 'extprice') {
				$Description = 'description';
				$SummaryHeader = _('Part Number');
				$DescriptionHeader = _('Part Description');
			}
			if ($SummaryType == 'supplierno') {
				$Description = 'suppname';
				$SummaryHeader = _('Supplier Number');
				$DescriptionHeader = _('Supplier Name');
			}
			if ($SummaryType == 'orderno') {
				$Description = 'supplierno';
				$SummaryHeader = _('Order Number');
				$DescriptionHeader = _('Supplier Number');
			}
			if ($SummaryType == 'categoryid') {
				$Description = 'categorydescription';
				$SummaryHeader = _('Stock Category');
				$DescriptionHeader = _('Category Description');
			}
			$SummaryDesc = $SummaryHeader;
			if ($OrderBy == 'extprice DESC') {
				$SummaryDesc = _('Extended Price');
			}
			if ($SummaryType == 'month') {
				$Description = 'monthname';
				$SummaryHeader = _('Month');
				$DescriptionHeader = _('Month');
			}
			fprintf($FileHandle, '"%s","%s","%s","%s","%s","%s"'."\n",
				 _($SummaryHeader),
				 _($DescriptionHeader),
				 _('Quantity'),
				 _('Extended Cost'),
				 _('Extended Price'),
				 _('Invoiced Qty'));

				$SuppName = ' ';
				$Linectr = 0;
			while ($MyRow = DB_fetch_array($Result)) {
				$Linectr++;
				if ($SummaryType == 'orderno') {
					$SuppName = $MyRow['suppname'];
				}
				fprintf($FileHandle, '"%s","%s",%s,%s,%s,%s,"%s"'."\n",
				$MyRow[$SummaryType],
				$MyRow[$Description],
				round($MyRow['quantityord'],$MyRow['decimalplaces']),
				round($MyRow['extcost'],2),
				round($MyRow['extprice'],2),
				round($MyRow['qtyinvoiced'],$MyRow['decimalplaces']),
				$SuppName);
				print '<br/>';
				$LastDecimalPlaces = $MyRow['decimalplaces'];
				$TotalQty += $MyRow['quantityord'];
				$TotalExtCost += $MyRow['extcost'];
				$TotalExtPrice += $MyRow['extprice'];
				$TotalInvQty += $MyRow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
				fprintf($FileHandle, '"%s","%s",%s,%s,%s,%s,"%s"'."\n",
				'Totals',
				_('Lines - ') . $Linectr,
				round($TotalQty,$LastDecimalPlaces),
				round($TotalExtCost,2),
				round($TotalExtPrice,2),
				round($TotalInvQty,$LastDecimalPlaces),
				' ');
		} // End of if ($_POST['ReportType']
		fclose($FileHandle);
		echo '<div class="centre"><p>' . _('The report has been exported as a csv file.') . '</p>';
		echo '<p><a href="' .  $FileName . '">' . _('click here') . '</a> ' . _('to view the file') . '</div></p>';

	} // End of if inputerror != 1
} // End of function submitcvs()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="ReportType">' . _('Report Type') . ':</label>
			<select required="required" autofocus="autofocus" name="ReportType">
				<option selected="selected" value="Detail">' . _('Detail') . '</option>
				<option value="Summary">' . _('Summary') . '</option>
			</select>
		</field>
		<field>
			<label for="DateType">' . _('Date Type') . ':</label>
			<select required="required" name="DateType">
				<option selected="selected" value="Order">' . _('Order Date') . '</option>
				<option value="Delivery">' . _('Delivery Date') . '</option>
			</select>
		</field>
		<field>
			<label from="FromDate">' . _('Date Range') . ':</label>
			<input required="required" type="date" name="FromDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['FromDate']) .'" /> ' . _('To') . '
			<input required="required" type="date" name="ToDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>
		<field>
			<label for="PartNumberOp">' . _('Part Number') . ':</label>
			<select name="PartNumberOp">
				<option selected="selected" value="Equals">' . _('Equals') . '</option>
				<option value="LIKE">' . _('Begins With') . '</option>
			</select>
			<input type="text" name="PartNumber" size="20" maxlength="20" value="';
	if (isset($_POST['PartNumber'])) {
		echo $_POST['PartNumber'] . '" /></td>
			</field>';
	} else {
		echo '" />
			</field>';
	}

	echo '<field>
			<label for="SupplierIdOp">' . _('Supplier Number') . ':</label>
			<select name="SupplierIdOp">
				<option selected="selected" value="Equals">' . _('Equals') . '</option>
				<option value="LIKE">' . _('Begins With') . '</option>
			</select>
			<input type="text" name="SupplierId" size="10" maxlength="10" value="';
	if (isset($_POST['SupplierId'])) {
		echo $_POST['SupplierId'] . '" />
				</field>';
	} else {
		echo  '" /></td>
			</field>';
	}

	echo '<field>
			<label for="SupplierNameOp">' . _('Supplier Name') . ':</label>
			<select name="SupplierNameOp">
				<option selected="selected" value="LIKE">' . _('Begins With') . '</option>
				<option value="Equals">' . _('Equals') . '</option>
			</select>
			<input type="text" name="SupplierName" size="30" maxlength="30" value="';
	if (isset($_POST['SupplierName'])) {
		echo $_POST['SupplierName'] . '" />
			</field>';
	} else {
		echo  '" />
			</field>';
	}

	echo '<field>
			<label for="OrderNo">' . _('Order Number') . ':</label>
			<fieldtext>' . _('Equals').'</fieldtext>:<input type="text" name="OrderNo" size="10" maxlength="10" value="';
	if (isset($_POST['OrderNo'])) {
		echo $_POST['OrderNo'] . '" />
				</field>';
	} else {
		echo  '" />
				</field>';
	}

	echo '<field>
			<label for="LineStatus">' . _('Line Item Status') . ':</label>
			<select name="LineStatus">
				<option selected="selected" value="All">' . _('All') . '</option>
				<option value="Completed">' . _('Completed') . '</option>
				<option value="Open">' . _('Not Completed') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Category">' . _('Stock Categories') . ':</label>
			<select name="Category">';
	$SQL="SELECT categoryid, categorydescription FROM stockcategory";
	$CategoryResult= DB_query($SQL);
	echo '<option selected="selected" value="All">' . _('All Categories') . '</option>';
	While ($MyRow = DB_fetch_array($CategoryResult)){
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SortBy">' . _('Sort By') . ':</label>
			<select name="SortBy">
				<option selected="selected" value="purchorderdetails.orderno">' . _('Order Number') . '</option>
				<option value="purchorderdetails.itemcode">' . _('Part Number') . '</option>
				<option value="suppliers.supplierid,purchorderdetails.orderno">' . _('Supplier Number') . '</option>
				<option value="suppliers.suppname,suppliers.supplierid,purchorderdetails.orderno">' . _('Supplier Name') . '</option>
			</select>
		</field>
		<field>
			<label for="SummaryType">' . _('Summary Type') . ':</label>
			<select name="SummaryType">
				<option selected="selected" value="orderno">' . _('Order Number') . '</option>
				<option value="itemcode">' . _('Part Number') . '</option>
				<option value="extprice">' . _('Extended Price') . '</option>
				<option value="supplierno">' . _('Supplier Number') . '</option>
				<option value="suppname">' . _('Supplier Name') . '</option>
				<option value="month">' . _('Month') . '</option>
				<option value="categoryid">' . _('Stock Category') . '</option>
			</select>
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Run Inquiry') . '" />
	</div>
	<div class="centre">
		<input type="submit" name="submitcsv" value="' . _('Export as csv file') . '" />
	</div>
	</form>';

} // End of function display()


include('includes/footer.php');
?>
