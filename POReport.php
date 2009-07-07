<?php
/* $Revision: 1.3 $ */
// POReport.php
// Inquiry on Purchase Orders
// If Date Type is Order, the main file is purchorderdetails
// If Date Type is Delivery, the main file is grns
$PageSecurity=2;

include('includes/session.inc');
$title = _('Purchase Order Report');
include('includes/header.inc');

# Sets default date range for current month
if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['PartNumber'])){
	$PartNumber = trim(strtoupper($_POST['PartNumber']));
} elseif (isset($_GET['PartNumber'])){
	$PartNumber = trim(strtoupper($_GET['PartNumber']));
}

# Part Number operator - either LIKE or =
$PartNumberOp = $_POST['PartNumberOp'];

if (isset($_POST['SupplierId'])){
	$SupplierId = trim(strtoupper($_POST['SupplierId']));
} elseif (isset($_GET['SupplierId'])){
	$SupplierId = trim(strtoupper($_GET['SupplierId']));
}

$SupplierIdOp = $_POST['SupplierIdOp'];

if (isset($_POST['SupplierName'])){
	$SupplierName = trim(strtoupper($_POST['SupplierName']));
} elseif (isset($_GET['SupplierName'])){
	$SupplierName = trim(strtoupper($_GET['SupplierName']));
}

$SupplierNameOp = $_POST['SupplierNameOp'];

// Save $_POST['SummaryType'] in $savesummarytype because change $_POST['SummaryType'] when
// create $sql
$savesummarytype = $_POST['SummaryType'];

// Had to add supplierid to SummaryType when do summary by name because there could be several accounts
// with the same name. Tried passing 'suppname,supplierid' in form, but it only read 'suppname'
if ($_POST['SummaryType'] == 'suppname') {
    $_POST['SummaryType'] = 'suppname,suppliers.supplierid';
}

if (isset($_POST['submit'])) {
    submit($db,$PartNumber,$PartNumberOp,$SupplierId,$SupplierIdOp,$SupplierName,$SupplierNameOp,$savesummarytype);
} else {
    display($db);
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db,$PartNumber,$PartNumberOp,$SupplierId,$SupplierIdOp,$SupplierName,$SupplierNameOp,$savesummarytype)  
{

	//initialize no input errors
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
	} 
	if (!is_date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');
	}
	
	# Add more to WHERE statement, if user entered something for the part number,supplierid, name
	$wherepart = ' ';
	if (strlen($PartNumber) > 0 && $PartNumberOp == 'LIKE') {
	    $PartNumber = $PartNumber . '%';
	} else {
	    $PartNumberOp = '=';
	}
	if (strlen($PartNumber) > 0) {
	    $wherepart = " AND purchorderdetails.itemcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	}

	$wheresupplierid = ' ';
	if ($SupplierIdOp == 'LIKE') {
	    $SupplierId = $SupplierId . '%';
	} else {
	    $SupplierIdOp = '=';
	}
	if (strlen($SupplierId) > 0) {
	    $wheresupplierid = " AND purchorders.supplierno " . $SupplierIdOp . " '" . $SupplierId . "'  ";
	}

	$wheresuppliername = ' ';
	if (strlen($SupplierName) > 0 && $SupplierNameOp == 'LIKE') {
	    $SupplierName = $SupplierName . '%';
	} else {
	    $SupplierNameOp = '=';
	}
	if (strlen($SupplierName) > 0) {
	    $wheresuppliername = " AND suppliers.suppname " . $SupplierNameOp . " '" . $SupplierName . "'  ";
	}

	if (strlen($_POST['OrderNo']) > 0) {
	    $whereorderno = ' AND purchorderdetails.orderno = ' . " '" . $_POST['OrderNo'] . "'  ";
	}

    $wherelinestatus = ' ';
    # Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
    #in WHERE clause because the WHERE clause didn't recognize
    # that had used the IF statement to create a field called linestatus
    if ($_POST['LineStatus'] != 'All') {
        if ($_POST['DateType'] == 'Order') {
			$wherelinestatus = " AND IF(purchorderdetails.quantityord = purchorderdetails.qtyinvoiced ||
			  purchorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
		 } else {
			$wherelinestatus = " AND IF(grns.qtyrecd - grns.quantityinv <> 0,'Open','Completed') = '" 
			. $_POST['LineStatus'] . "'";		 
		 }
    }

	 
 	 $wherecategory = ' ';
    if ($_POST['Category'] != 'All') {
        $wherecategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
    }
 
    if ($inputerror !=1) {
		$fromdate = FormatDateForSQL($_POST['FromDate']);
		$todate = FormatDateForSQL($_POST['ToDate']);
		if ($_POST['ReportType'] == 'Detail') {
		    if ($_POST['DateType'] == 'Order') {
				$sql = "SELECT purchorderdetails.orderno,
							   purchorderdetails.itemcode,
							   purchorderdetails.deliverydate,
							   purchorders.supplierno,
							   purchorders.orddate,
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
						LEFT JOIN purchorders ON purchorders.orderno=purchorderdetails.orderno
						LEFT JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
						LEFT JOIN stockmaster ON purchorderdetails.itemcode = stockmaster.stockid
						WHERE purchorders.orddate >='$fromdate'
						 AND purchorders.orddate <='$todate'
						$wherepart
						$wheresupplierid
						$wheresuppliername
						$whereorderno
						$wherelinestatus
						$wherecategory
						ORDER BY " . $_POST['SortBy'];
			} else {
				// Selects by delivery date from grns
				$sql = "SELECT purchorderdetails.orderno,
							   purchorderdetails.itemcode,
							   grns.deliverydate,
							   purchorders.supplierno,
							   purchorders.orddate,
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
						WHERE grns.deliverydate >='$fromdate'
						 AND grns.deliverydate <='$todate'
						$wherepart
						$wheresupplierid
						$wheresuppliername
						$whereorderno
						$wherelinestatus
						$wherecategory
						ORDER BY " . $_POST['SortBy'];
		   }
		} else {
		  // sql for Summary report
		  $orderby = $_POST['SummaryType'];
		  // The following is because the 'extprice' summary is a special case - with the other
		  // summaries, you group and order on the same field; with 'extprice', you are actually
		  // grouping on the stkcode and ordering by extprice descending
		  if ($_POST['SummaryType'] == 'extprice') {
		      $_POST['SummaryType'] = 'itemcode';
		      $orderby = 'extprice DESC';
		  }
          if ($_POST['DateType'] == 'Order') {
			    if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'itemcode') {
					$sql = "SELECT purchorderdetails.itemcode,
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
							WHERE purchorders.orddate >='$fromdate'
							 AND purchorders.orddate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',stockmaster.decimalplaces,
							  stockmaster.description 
							ORDER BY ' . $orderby;
			   } elseif ($_POST['SummaryType'] == 'orderno') {
					$sql = "SELECT purchorderdetails.orderno,
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
							WHERE purchorders.orddate >='$fromdate'
							 AND purchorders.orddate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',purchorders.supplierno,
							  suppliers.suppname
							ORDER BY ' . $orderby;
			} elseif ($_POST['SummaryType'] == 'supplierno' || $_POST['SummaryType'] == 'suppname,suppliers.supplierid') {
					$sql = "SELECT purchorders.supplierno,
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
							WHERE purchorders.orddate >='$fromdate'
							 AND purchorders.orddate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',purchorders.supplierno,
							  suppliers.suppname
							ORDER BY ' . $orderby;
			} elseif ($_POST['SummaryType'] == 'month') {
					$sql = "SELECT EXTRACT(YEAR_MONTH from purchorders.orddate) as month,
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
							WHERE purchorders.orddate >='$fromdate'
							 AND purchorders.orddate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							', monthname
							ORDER BY ' . $orderby;
			} elseif ($_POST['SummaryType'] == 'categoryid') {
					$sql = "SELECT SUM(purchorderdetails.quantityord) as quantityord,
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
							WHERE purchorders.orddate >='$fromdate'
							 AND purchorders.orddate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							', categorydescription
							ORDER BY ' . $orderby;
			}
		} else {
					// Selects by delivery date from grns
			    if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'itemcode') {
					$sql = "SELECT purchorderdetails.itemcode,
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
							WHERE grns.deliverydate >='$fromdate'
							 AND grns.deliverydate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							', stockmaster.description
							ORDER BY ' . $orderby;
			    } elseif ($_POST['SummaryType'] == 'orderno') {
					$sql = "SELECT purchorderdetails.orderno,
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
							WHERE grns.deliverydate >='$fromdate'
							 AND grns.deliverydate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							', purchorders.supplierno,
							   suppliers.suppname
							ORDER BY ' . $orderby;
			} elseif ($_POST['SummaryType'] == 'supplierno' || $_POST['SummaryType'] == 'suppname,suppliers.supplierid') {
					$sql = "SELECT purchorders.supplierno,
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
							WHERE grns.deliverydate >='$fromdate'
							 AND grns.deliverydate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							', purchorders.supplierno,
							   suppliers.suppname
							ORDER BY ' . $orderby;
			    } elseif ($_POST['SummaryType'] == 'month') {
					$sql = "SELECT EXTRACT(YEAR_MONTH from purchorders.orddate) as month,
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
							WHERE grns.deliverydate >='$fromdate'
							 AND grns.deliverydate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',monthname
							ORDER BY ' . $orderby;
			    } elseif ($_POST['SummaryType'] == 'categoryid') {
					$sql = "SELECT stockmaster.categoryid,
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
							WHERE grns.deliverydate >='$fromdate'
							 AND grns.deliverydate <='$todate'
							$wherepart
							$wheresupplierid
							$wheresuppliername
							$whereorderno
							$wherelinestatus
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',categorydescription
							ORDER BY ' . $orderby;
			    }
			}
		} // End of if ($_POST['ReportType']
		//echo "<br/>$sql<br/>";
		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);
		$ctr = 0;
		echo '<pre>';
		$totalqty = 0;
		$totalextcost = 0;
		$totalextprice = 0;
		$totalinvqty = 0;
		
	// Create array for summary type to display in header. Access it with $savesummarytype
	$summary_array["orderno"] =  _('Order Number');
	$summary_array["itemcode"] =  _('Part Number');
	$summary_array["extprice"] =  _('Extended Price');
	$summary_array["supplierno"] =  _('Customer Number');
	$summary_array["suppname"] =  _('Customer Name');
	$summary_array["month"] =  _('Month');
	$summary_array["categoryid"] =  _('Stock Category');

    // Create array for sort for detail report to display in header
    $detail_array['purchorderdetails.orderno'] = _('Order Number');
	$detail_array['purchorderdetails.itemcode'] = _('Part Number');
	$detail_array['suppliers.supplierid,purchorderdetails.orderno'] = _('Supplier Number');
	$detail_array['suppliers.suppname,suppliers.supplierid,purchorderdetails.orderno'] = _('Supplier Name');

		// Display Header info
		if ($_POST['ReportType'] == 'Summary') {
		    $sortby_display = $summary_array[$savesummarytype];
		} else {
		    $sortby_display = $detail_array[$_POST['SortBy']];
		}		echo '  ' . _('Purchase Order Report') . ' - ' . $_POST['ReportType'] . " By $sortby_display <br/>";
		echo '  ' . _('Date Type') . ' - ' . $_POST['DateType'] . '<br/>';
		echo '  ' . _('Date Range') . ' - ' . $_POST['FromDate'] . _(' To ') .  $_POST['ToDate'] . '<br/>';
		if (strlen(trim($PartNumber)) > 0) {
			echo '  ' . _('Part Number') . ' - ' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '<br/>';
		}
		if (strlen(trim($_POST['SupplierId'])) > 0) {
			echo '  ' . _('Supplier Number') . ' - ' . $_POST['SupplierIdOp'] . ' ' . $_POST['SupplierId'] . '<br/>';
		}
		if (strlen(trim($_POST['SupplierName'])) > 0) {
			echo '  ' . _('Supplier Name') . ' - ' . $_POST['SupplierNameOp'] . ' ' . $_POST['SupplierName'] . '<br/>';
		}
		echo '  ' . _('Line Item Status') . '  - ' . $_POST['LineStatus'] . '<br/>';
		echo '  ' . _('Stock Category') . '  - ' . $_POST['Category'] . '<br/>';

		if ($_POST['ReportType'] == 'Detail') {
		    echo '<br/><br/>';
		    if ($_POST['DateType'] == 'Order') {
				printf("%10s | %-20s | %10s | %-12s | %-40s |  %12s| %14s | %14s | %14s | %12s | %-10s | %-40s ",
					 _('Order No'),
					 _('Part Number'),
					 _('Order Date'),
					 _('Supplier No'),
					 _('Supplier Name'),
					 _('Order Qty'),
					 _('Extended Cost'),
					 _('Extended Price'),
					 _('Invoiced Qty'),
					 _('Line Status'),
					 _('Item Due'),
					 _('Part Description'));
					print '<br/><br/>';
					$linectr = 0;
				while ($myrow = DB_fetch_array($result)) {
					$linectr++;
				   // Detail for both DateType of Order 
					printf("%10s | %-20s | %10s | %-12s | %-40s | %12s | %14s | %14s | %14s | %12s | %-10s | %-40s ",
					$myrow['orderno'],
					$myrow['itemcode'],
					ConvertSQLDate($myrow['orddate']),
					$myrow['supplierno'],
					$myrow['suppname'],
					number_format($myrow['quantityord'],$myrow['decimalplaces']),
					number_format($myrow['extcost'],2),
					number_format($myrow['extprice'],2),
					number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']),
					$myrow['linestatus'],
					ConvertSQLDate($myrow['deliverydate']),
					$myrow['description']);
					print '<br/>';
					$lastdecimalplaces = $myrow['decimalplaces'];
					$totalqty += $myrow['quantityord'];
					$totalextcost += $myrow['extcost'];
					$totalextprice += $myrow['extprice'];
					$totalinvqty += $myrow['qtyinvoiced'];
				} //END WHILE LIST LOOP
				// Print totals
					printf("%10s | %-20s | %10s | %-12s | %-40s | %12s | %14s | %14s | %14s | %12s | %-40s ",
					'Totals',
					_('Lines - ') . $linectr,
					' ',
					' ',
					' ',
					number_format($totalqty,2),
					number_format($totalextcost,2),
					number_format($totalextprice,2),
					number_format($totalinvqty,2),
					' ',
					' ');
				echo '</pre>';
			} else {
			  // Header for Date Type of Delivery Date
				printf("%10s | %-20s | %10s | %-12s | %-40s | %12s | %-14s | %14s | %14s | %12s | %-10s | %-40s ",
					 _('Order No'),
					 _('Part Number'),
					 _('Order Date'),
					 _('Supplier No'),
					 _('Supplier Name'),
					 _('Received'),
					 _('Extended Cost'),
					 _('Extended Price'),
					 _('Invoiced Qty'),
					 _('Line Status'),
					 _('Delivered'),
					 _('Part Description'));			
					print '<br/><br/>';
					$linectr = 0;
				while ($myrow = DB_fetch_array($result)) {
					$linectr++;
				   // Detail for both DateType of Ship
				   // In sql, had to alias grns.qtyrecd as quantityord so could use same name here 
					printf("%10s | %-20s | %10s | %-12s | %-40s | %12s | %14s | %14s | %14s | %12s | %-10s | %-40s ",
					$myrow['orderno'],
					$myrow['itemcode'],
					ConvertSQLDate($myrow['orddate']),
					$myrow['supplierno'],
					$myrow['suppname'],
					number_format($myrow['quantityord'],$myrow['decimalplaces']),
					number_format($myrow['extcost'],2),
					number_format($myrow['extprice'],2),
					number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']),
					$myrow['linestatus'],
					ConvertSQLDate($myrow['deliverydate']),
					$myrow['description']);
					print '<br/>';
					$lastdecimalplaces = $myrow['decimalplaces'];
					$totalqty += $myrow['quantityord'];
					$totalextcost += $myrow['extcost'];
					$totalextprice += $myrow['extprice'];
					$totalinvqty += $myrow['qtyinvoiced'];
				} //END WHILE LIST LOOP
				// Print totals
					printf("%10s | %-20s | %10s | %-12s | %-40s | %12s | %14s | %14s | %14s | %12s | %-40s ",
					'Totals',
					_('Lines - ') . $linectr,
					' ',
					' ',
					' ',
					number_format($totalqty,$lastdecimalplaces),
					number_format($totalextcost,2),
					number_format($totalextprice,2),
					number_format($totalinvqty,$lastdecimalplaces),
					' ',
					' ');
				echo '</pre>';
			}
		} else {
		  // Print summary stuff
			$summarytype = $_POST['SummaryType'];
			// For SummaryType 'suppname' had to add supplierid to it for the GROUP BY in the sql, 
			// but have to take it away for $myrow[$summarytype] to be valid
			// Set up description based on the Summary Type
			if ($summarytype == 'suppname,suppliers.supplierid') {
				$summarytype = 'suppname';
				$description = 'supplierno';
				$summaryheader = _('Supplier Name');
				$descriptionheader = _('Supplier Number');
			}
			if ($summarytype == 'itemcode' || $summarytype == 'extprice') {
				$description = 'description';
				$summaryheader = _('Part Number');
				$descriptionheader = _('Part Description');
			}
			if ($summarytype == 'supplierno') {
				$description = 'suppname';
				$summaryheader = _('Supplier Number');
				$descriptionheader = _('Supplier Name');
			}
			if ($summarytype == 'orderno') {
				$description = 'supplierno';
				$summaryheader = _('Order Number');
				$descriptionheader = _('Supplier Number');
			}				
			if ($summarytype == 'categoryid') {
				$description = 'categorydescription';
				$summaryheader = _('Stock Category');
				$descriptionheader = _('Category Description');
			}
			$summarydesc = $summaryheader;
			if ($orderby == 'extprice DESC') {
				$summarydesc = _('Extended Price');
			}
			if ($summarytype == 'month') {
				$description = 'monthname';
				$summaryheader = _('Month');
				$descriptionheader = _('Month');
			}
            echo '<br/><br/>';
			printf("    %-40s | %-40s | %12s | %14s | %14s | %14s",
				 _($summaryheader),
				 _($descriptionheader),
				 _('Quantity'),
				 _('Extended Cost'),
				 _('Extended Price'),
				 _('Invoiced Qty'));
				print '<br/><br/>';

				$suppname = ' ';
				$linectr = 0;
			while ($myrow = DB_fetch_array($result)) {
			    $linectr++;
				if ($summarytype == 'orderno') {
				    $suppname = $myrow['suppname'];
				}				
				printf("    %-40s | %-40s | %12s | %14s | %14s | %14s  |  %-40s",
				$myrow[$summarytype],
				$myrow[$description],
				number_format($myrow['quantityord'],$myrow['decimalplaces']),
				number_format($myrow['extcost'],2),
				number_format($myrow['extprice'],2),
				number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']),
				$suppname);
				print '<br/>';
				$lastdecimalplaces = $myrow['decimalplaces'];
				$totalqty += $myrow['quantityord'];
				$totalextcost += $myrow['extcost'];
				$totalextprice += $myrow['extprice'];
				$totalinvqty += $myrow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
				printf("    %-40s | %-40s | %12s | %14s | %14s | %14s",
				'Totals',
				_('Lines - ') . $linectr,
				number_format($totalqty,$lastdecimalplaces),
				number_format($totalextcost,2),
				number_format($totalextprice,2),
				number_format($totalinvqty,$lastdecimalplaces),
				' ');
			echo '</pre>';		
		} // End of if ($_POST['ReportType']
		
    } // End of if inputerror != 1
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo "<form action=" . $_SERVER['PHP_SELF'] . "?" . SID ." method=post><br/><br/>";

	echo '<table>';

    echo '<tr><td>' . _('Report Type') . ':</td>';
    echo "<td><select name='ReportType'>";
	echo "<option selected value='Detail'>" . _('Detail');
	echo "<option value='Summary'>" . _('Summary');
	echo '</select></td><td>&nbsp</td></tr>';

    echo '<tr><td>' . _('Date Type') . ':</td>';
    echo "<td><select name='DateType'>";
	echo "<option selected value='Order'>" . _('Order Date');
	echo "<option value='Delivery'>" . _('Delivery Date');
	echo '</select></td><td>&nbsp</td></tr>';

    echo '<tr>
        <td>' . _('Date Range') . ":</td>
	    <td><input type='Text' class=date alt='".$_SESSION['DefaultDateFormat']."' name='FromDate' size=10 maxlength=10 value=" . $_POST['FromDate'] . '></td>
        <td>' . _('To') . ":</td>
	    <td><input type='Text' class=date alt='".$_SESSION['DefaultDateFormat']."' name='ToDate' size=10 maxlength=10 value=" . $_POST['ToDate'] . "></td>
	</tr>";

    echo '<tr><td>' . _('Part Number') . ':</td>';
    echo "<td><select name='PartNumberOp'>";
	echo "<option selected value='Equals'>" . _('Equals');
	echo "<option value='LIKE'>" . _('Begins With');
	echo '</select></td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='PartNumber' size=20 maxlength=20 value=" 
	    . $_POST['PartNumber'] . "></td></tr>";
	
    echo '<tr><td>' . _('Supplier Number') . ':</td>';
    echo "<td><select name='SupplierIdOp'>";
	echo "<option selected value='Equals'>" . _('Equals');
	echo "<option value='LIKE'>" . _('Begins With');
	echo '</select></td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='SupplierId' size=10 maxlength=10 value=" 
	    . $_POST['SupplierId'] . "></td></tr>";

    echo '<tr><td>' . _('Supplier Name') . ':</td>';
    echo "<td><select name='SupplierNameOp'>";
	echo "<option selected value='LIKE'>" . _('Begins With');
	echo "<option value='Equals'>" . _('Equals');
	echo '</select></td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='SupplierName' size=30 maxlength=30 value=" 
	    . $_POST['SupplierName'] . "></td></tr>";

    echo '<tr><td>' . _('Order Number') . ':</td>';
    echo '<td>Equals</td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='OrderNo' size=10 maxlength=10 value=" 
	    . $_POST['OrderNo'] . "></td></tr>";
	    
    echo '<tr><td>' . _('Line Item Status') . ':</td>';
    echo "<td><select name='LineStatus'>";
	echo "<option selected value='All'>" . _('All');
	echo "<option value='Completed'>" . _('Completed');
	echo "<option value='Open'>" . _('Not Completed');
	echo '</select></td><td>&nbsp</td></tr>';

	echo '<tr><td>' . _('Stock Categories') . ":</td><td><select name='Category'>";
	$sql='SELECT categoryid, categorydescription FROM stockcategory';
	$CategoryResult= DB_query($sql,$db);
	echo '<option selected value="All">' . _('All Categories');
	While ($myrow = DB_fetch_array($CategoryResult)){
		echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
	}
	echo '</select></td></tr>';

	echo '<tr><td>&nbsp</td></tr>';
    echo '<tr><td>' . _('Sort By') . ':</td>';
    echo "<td><select name='SortBy'>";
	echo "<option selected value='purchorderdetails.orderno'>" . _('Order Number');
	echo "<option value='purchorderdetails.itemcode'>" . _('Part Number');
	echo "<option value='suppliers.supplierid,purchorderdetails.orderno'>" . _('Supplier Number');
	echo "<option value='suppliers.suppname,suppliers.supplierid,purchorderdetails.orderno'>" . _('Supplier Name');	
	echo '</select></td><td>&nbsp</td></tr>';

   echo '<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td></tr>';

	echo '<tr><td>&nbsp</td></tr>';
    echo '<tr><td>' . _('Summary Type') . ':</td>';
    echo "<td><select name='SummaryType'>";
	echo "<option selected value='orderno'>" . _('Order Number');
	echo "<option value='itemcode'>" . _('Part Number');
	echo "<option value='extprice'>" . _('Extended Price');
	echo "<option value='supplierno'>" . _('Supplier Number');
	echo "<option value='suppname'>" . _('Supplier Name');
	echo "<option value='month'>" . _('Month');
	echo "<option value='categoryid'>" . _('Stock Category');
	echo '</select></td><td>&nbsp</td></tr>';

   echo "
	<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td>
	    <td><input type='submit' name='submit' value='" . _('Run Inquiry') . "'></td>
	</tr>
	</table>
	<br/>";
   echo '</form>';

} // End of function display()


include('includes/footer.inc');
?>
