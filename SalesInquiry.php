<?php
/* $Revision: 1.3 $ */
// SalesInquiry.php
// Inquiry on Sales Orders - If Date Type is Order Date, salesorderdetails is the main table
// If Date Type is Invoice, stockmoves is the main table
$PageSecurity=2;

include('includes/session.inc');
$title = _('Sales Inquiry');
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

if (isset($_POST['DebtorNo'])){
	$DebtorNo = trim(strtoupper($_POST['DebtorNo']));
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = trim(strtoupper($_GET['DebtorNo']));
}

$DebtorNoOp = $_POST['DebtorNoOp'];

if (isset($_POST['DebtorName'])){
	$DebtorName = trim(strtoupper($_POST['DebtorName']));
} elseif (isset($_GET['DebtorName'])){
	$DebtorName = trim(strtoupper($_GET['DebtorName']));
}

$DebtorNameOp = $_POST['DebtorNameOp'];

// Save $_POST['SummaryType'] in $savesummarytype because change $_POST['SummaryType'] when
// create $sql
$savesummarytype = $_POST['SummaryType'];

if (isset($_POST['submit'])) {
    submit($db,$PartNumber,$PartNumberOp,$DebtorNo,$DebtorNoOp,$DebtorName,$DebtorNameOp,$savesummarytype);
} else {
    display($db);
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db,$PartNumber,$PartNumberOp,$DebtorNo,$DebtorNoOp,$DebtorName,$DebtorNameOp,$savesummarytype)  
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

	if ($_POST['ReportType'] == 'Summary' && $_POST['DateType'] == 'Order'  && $_POST['SummaryType'] == 'transno') {
		$InputError = 1;
		prnMsg(_('Cannot summarize by transaction number with a date type of Order Date'),'error');
		return;
	}

	if ($_POST['ReportType'] == 'Detail' && $_POST['DateType'] == 'Order'  && $_POST['SortBy'] == 'tempstockmoves.transno,salesorderdetails.stkcode') {
		$InputError = 1;
		prnMsg(_('Cannot sort by transaction number with a date type of Order Date'),'error');
		return;
	}
	

// TempStockmoves function creates a temporary table of stockmoves that is used when the DateType
// is Invoice Date
	if ($_POST['DateType'] == 'Invoice') {
	    TempStockmoves(&$db);
	}


	# Add more to WHERE statement, if user entered something for the part number,debtorno, name
	// Variables that end with Op - meaning operator - are either = or LIKE
	$wherepart = ' ';
	if (strlen($PartNumber) > 0 && $PartNumberOp == 'LIKE') {
	    $PartNumber = $PartNumber . '%';
	} else {
	    $PartNumberOp = '=';
	}
	if (strlen($PartNumber) > 0) {
	    $wherepart = " AND salesorderdetails.stkcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	}

	$wheredebtorno = ' ';
	if ($DebtorNoOp == 'LIKE') {
	    $DebtorNo = $DebtorNo . '%';
	} else {
	    $DebtorNoOp = '=';
	}
	if (strlen($DebtorNo) > 0) {
	    $wheredebtorno = " AND salesorders.debtorno " . $DebtorNoOp . " '" . $DebtorNo . "'  ";
	}

	$wheredebtorname = ' ';
	if (strlen($DebtorName) > 0 && $DebtorNameOp == 'LIKE') {
	    $DebtorName = $DebtorName . '%';
	} else {
	    $DebtorNameOp = '=';
	}
	if (strlen($DebtorName) > 0) {
	    $wheredebtorname = " AND debtorsmaster.name " . $DebtorNameOp . " '" . $DebtorName . "'  ";
	}
	if (strlen($_POST['OrderNo']) > 0) {
	    $whereorderno = ' AND salesorderdetails.orderno = ' . " '" . $_POST['OrderNo'] . "'  ";
	}


    $wherelinestatus = ' ';
    # Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
    #in WHERE clause because the WHERE clause didn't recognize
    # that had used the IF statement to create a field caused linestatus
    if ($_POST['LineStatus'] != 'All') {
        $wherelinestatus = " AND IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced ||
		  salesorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
    }

    // The following is from PDFCustomerList.php and shows how to set up WHERE clause
    // for multiple selections from Areas - decided to just allow selection of one Area at
    // a time, so used simpler code
// 		$i=0;
// 		foreach ($_POST['Areas'] as $Area){
// 			if ($i>0){
// 				$SQL .= ' OR ';
// 			}
// 			$i++;
// 			$SQL .= "custbranch.area='" . $Area ."'";
// 		}
	 $wherearea = ' ';
    if ($_POST['Area'] != 'All') {
        $wherearea = " AND custbranch.area = '" . $_POST['Area'] . "'";
    }
	 
	 $wheresalesman = ' ';
    if ($_POST['Salesman'] != 'All') {
        $wheresalesman = " AND custbranch.salesman = '" . $_POST['Salesman'] . "'";
    }
    
 	 $wherecategory = ' ';
    if ($_POST['Category'] != 'All') {
        $wherecategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
    }

// Only used for Invoice Date type where tempstockmoves is the main table
 	 $wheretype = " AND (tempstockmoves.type='10' OR tempstockmoves.type='11')";
    if ($_POST['InvoiceType'] != 'All') {
        $wheretype = " AND tempstockmoves.type = '" . $_POST['InvoiceType'] . "'";
    }
    if ($inputError !=1) {
		$fromdate = FormatDateForSQL($_POST['FromDate']);
		$todate = FormatDateForSQL($_POST['ToDate']);
		if ($_POST['ReportType'] == 'Detail') {
		    if ($_POST['DateType'] == 'Order') {
				$sql = "SELECT salesorderdetails.orderno,
							   salesorderdetails.stkcode,
							   salesorderdetails.itemdue,
							   salesorders.debtorno,
							   salesorders.orddate,
							   salesorders.branchcode,
							   salesorderdetails.quantity,
							   salesorderdetails.qtyinvoiced,
							   (salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
							   (salesorderdetails.quantity * stockmaster.actualcost) as extcost,
							   IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced ||
								  salesorderdetails.completed = 1,'Completed','Open') as linestatus,
							   debtorsmaster.name,
							   custbranch.brname,
							   custbranch.area,
							   custbranch.salesman,
							   stockmaster.decimalplaces,
							   stockmaster.description
							   FROM salesorderdetails 
						LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
						LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
						LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
						WHERE salesorders.orddate >='$fromdate'
						 AND salesorders.orddate <='$todate'
						 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
						$wherepart
						$whereorderno
						$wheredebtorno
						$wheredebtorname
						$wherelinestatus
						$wherearea
						$wheresalesman
						$wherecategory
						ORDER BY " . $_POST['SortBy'];
			  } else {
			    // Selects by tempstockmoves.trandate not order date
				$sql = "SELECT salesorderdetails.orderno,
							   salesorderdetails.stkcode,
							   salesorderdetails.itemdue,
							   salesorders.debtorno,
							   salesorders.orddate,
							   salesorders.branchcode,
							   salesorderdetails.quantity,
							   salesorderdetails.qtyinvoiced,
							   (tempstockmoves.qty * salesorderdetails.unitprice) * -1 as extprice,
							   (tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
							   IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced ||
								  salesorderdetails.completed = 1,'Completed','Open') as linestatus,
							   debtorsmaster.name,
							   custbranch.brname,
							   custbranch.area,
							   custbranch.salesman,
							   stockmaster.decimalplaces,
							   stockmaster.description,
							   (tempstockmoves.qty * -1) as qty,
							   tempstockmoves.transno,
							   tempstockmoves.trandate,
							   tempstockmoves.type
							   FROM tempstockmoves 
						LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
						LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
						LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
						LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
						WHERE tempstockmoves.trandate >='$fromdate'
						 AND tempstockmoves.trandate <='$todate'
						 AND tempstockmoves.stockid=salesorderdetails.stkcode
						 AND tempstockmoves.hidemovt=0
						 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
						$wherepart
						$wheretype
						$whereorderno
						$wheredebtorno
						$wheredebtorname
						$wherelinestatus
						$wherearea
						$wheresalesman
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
		      $_POST['SummaryType'] = 'stkcode';
		      $orderby = 'extprice DESC';
		  }
		  if ($_POST['DateType'] == 'Order') {
		      if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'stkcode') {
					$sql = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost,
								   stockmaster.description,
								   stockmaster.decimalplaces
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',salesorderdetails.stkcode,
								   stockmaster.description,
								   stockmaster.decimalplaces
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$sql = "SELECT salesorderdetails.orderno,
					               salesorders.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',salesorders.debtorno,
								   debtorsmaster.name
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'debtorno' || $_POST['SummaryType'] == 'name') {
				    if ($_POST['SummaryType'] == 'name') {
				        $orderby = 'name';
				    }
					$sql = "SELECT debtorsmaster.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
						
							GROUP BY debtorsmaster.debtorno" . ' ' .
							',debtorsmaster.name
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'month') {
					$sql = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',monthname
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$sql = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',categorydescription
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'salesman') {
					$sql = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',salesmanname
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'area') {
					$sql = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails 
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE salesorders.orddate >='$fromdate'
							 AND salesorders.orddate <='$todate'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',areas.areadescription
								   ORDER BY ' . $orderby;
				} 
		   } else {
		        // Selects by tempstockmoves.trandate not order date
		      if ($_POST['SummaryType'] == 'extprice' || $_POST['SummaryType'] == 'stkcode') {
					$sql = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   stockmaster.description,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',stockmaster.description
								   ORDER BY ' . $orderby;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$sql = "SELECT salesorderdetails.orderno,
					               salesorders.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',salesorders.debtorno,
								   debtorsmaster.name
								   ORDER BY ' . $orderby;				
				} elseif ($_POST['SummaryType'] == 'debtorno' || $_POST['SummaryType'] == 'name') {
				    if ($_POST['SummaryType'] == 'name') {
				        $orderby = 'name';
				    }
					$sql = "SELECT debtorsmaster.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY debtorsmaster.debtorno" . ' ' .
							',debtorsmaster.name
								   ORDER BY ' . $orderby;			
				} elseif ($_POST['SummaryType'] == 'month') {
					$sql = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',monthname
						    ORDER BY ' . $orderby;					
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$sql = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',categorydescription
						    ORDER BY ' . $orderby;							
				} elseif ($_POST['SummaryType'] == 'salesman') {
					$sql = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',salesmanname
						    ORDER BY ' . $orderby;		
				} elseif ($_POST['SummaryType'] == 'area') {
					$sql = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves 
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						    LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							WHERE tempstockmoves.trandate >='$fromdate'
							 AND tempstockmoves.trandate <='$todate'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' 
							$wherepart
							$wheretype
							$whereorderno
							$wheredebtorno
							$wheredebtorname
							$wherelinestatus
							$wherearea
							$wheresalesman
							$wherecategory
							GROUP BY " . $_POST['SummaryType'] .
							',areas.areadescription
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
	$summary_array["stkcode"] =  _('Part Number');
	$summary_array["extprice"] =  _('Extended Price');
	$summary_array["debtorno"] =  _('Customer Number');
	$summary_array["name"] =  _('Customer Name');
	$summary_array["month"] =  _('Month');
	$summary_array["categoryid"] =  _('Stock Category');
	$summary_array["salesman"] =  _('Salesman');
	$summary_array["area"] = _('Sales Area');
	$summary_array["transno"] = _('Transaction Number');
    // Create array for sort for detail report to display in header
    $detail_array['salesorderdetails.orderno'] = _('Order Number');
	$detail_array['salesorderdetails.stkcode'] = _('Part Number');
	$detail_array['debtorsmaster.debtorno,salesorderdetails.orderno'] = _('Customer Number');
	$detail_array['debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno'] = _('Customer Name');
	$detail_array['tempstockmoves.transno,salesorderdetails.stkcode'] = _('Transaction Number');
	
		// Display Header info
		if ($_POST['ReportType'] == 'Summary') {
		    $sortby_display = $summary_array[$savesummarytype];
		} else {
		    $sortby_display = $detail_array[$_POST['SortBy']];
		}
		echo '  ' . _('Sales Inquiry') . ' - ' . $_POST['ReportType'] . " By $sortby_display <br/>";
		if ($_POST['OrderType'] == '0') {
		    echo '  ' . _('Order Type - Sales Orders') . '<br/>';
		} else {
		    echo '  ' . _('Order Type - Quotations') . '<br/>';
		}
		echo '  ' . _('Date Type') . ' - ' . $_POST['DateType'] . '<br/>';
		echo '  ' . _('Date Range') . ' - ' . $_POST['FromDate'] . _(' To ') .  $_POST['ToDate'] . '<br/>';
		if (strlen(trim($PartNumber)) > 0) {
			echo '  ' . _('Part Number') . ' - ' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '<br/>';
		}
		if (strlen(trim($_POST['DebtorNo'])) > 0) {
			echo '  ' . _('Customer Number') . ' - ' . $_POST['DebtorNoOp'] . ' ' . $_POST['DebtorNo'] . '<br/>';
		}
		if (strlen(trim($_POST['DebtorName'])) > 0) {
			echo '  ' . _('Customer Name') . ' - ' . $_POST['DebtorNameOp'] . ' ' . $_POST['DebtorName'] . '<br/>';
		}
		echo '  ' . _('Line Item Status') . '  - ' . $_POST['LineStatus'] . '<br/>';
		echo '  ' . _('Stock Category') . '  - ' . $_POST['Category'] . '<br/>';
		echo '  ' . _('Salesman') . '  - ' . $_POST['Salesman'] . '<br/>';
		echo '  ' . _('Sales Area') . '  - ' . $_POST['Area'] . '<br/>';
		If ($_POST['DateType'] != 'Order') {
		    $itype = 'All';
		    if ($_POST['InvoiceType'] == '10') {
		        $itype = 'Sales Invoice';
		    } elseif ($_POST['InvoiceType'] == '11') {
		        $itype = 'Credit Notes';
		    }
		    echo '  ' . _('Invoice Type') . '  - ' . $itype . '<br/>';
        }
		echo '<br/><br/>';
		if ($_POST['ReportType'] == 'Detail') {
		    if ($_POST['DateType'] == 'Order') {
				printf("%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ",
					 _('Order No'),
					 _('Part Number'),
					 _('Order Date'),
					 _('Debtor No'),
					 _('Debtor Name'),
					 _('Branch Name'),
					 _('Order Qty'),
					 _('Extended Cost'),
					 _('Extended Price'),
					 _('Invoiced Qty'),
					 _('Line Status'),
					 _('Item Due'),
					 _('Salesman'),
					 _('Area'),
					 _('Part Description'));
			} else {
			    // Headings for Invoiced Date
				printf("%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ",
					 _('Order No'),
					 _('Trans. No'),
					 _('Part Number'),
					 _('Order Date'),
					 _('Debtor No'),
					 _('Debtor Name'),
					 _('Branch Name'),
					 _('Invoiced Qty'),
					 _('Extended Cost'),
					 _('Extended Price'),
					 _('Line Status'),
					 _('Invoiced'),
					 _('Salesman'),
					 _('Area'),
					 _('Part Description'));
			}
				print '<br/><br/>';
				$linectr = 0;
			while ($myrow = DB_fetch_array($result)) {
			    $linectr++;
			    if ($_POST['DateType'] == 'Order') {
					printf("%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ",
					$myrow['orderno'],
					$myrow['stkcode'],
					ConvertSQLDate($myrow['orddate']),
					$myrow['debtorno'],
					$myrow['name'],
					$myrow['brname'],
					number_format($myrow['quantity'],$myrow['decimalplaces']),
					number_format($myrow['extcost'],2),
					number_format($myrow['extprice'],2),
					number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']),
					$myrow['linestatus'],
					ConvertSQLDate($myrow['itemdue']),
					$myrow['salesman'],
					$myrow['area'],
					$myrow['description']);
					print '<br/>';
					$totalqty += $myrow['quantity'];
				} else {
				    // Detail for Invoiced Date
					printf("%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ",
					$myrow['orderno'],
					$myrow['transno'],
					$myrow['stkcode'],
					ConvertSQLDate($myrow['orddate']),
					$myrow['debtorno'],
					$myrow['name'],
					$myrow['brname'],
					number_format($myrow['qty'],$myrow['decimalplaces']),
					number_format($myrow['extcost'],2),
					number_format($myrow['extprice'],2),
					$myrow['linestatus'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['salesman'],
					$myrow['area'],
					$myrow['description']);
					print '<br/>';
					$totalqty += $myrow['qty'];
				}
				$lastdecimalplaces = $myrow['decimalplaces'];
				$totalextcost += $myrow['extcost'];
				$totalextprice += $myrow['extprice'];
				$totalinvqty += $myrow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
			if ($_POST['DateType'] == 'Order') {
					printf("%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-40s ",
					_('Totals'),
					_('Lines - ') . $linectr,
					' ',
					' ',
					' ',
					' ',
					number_format($totalqty,2),
					number_format($totalextcost,2),
					number_format($totalextprice,2),
					number_format($totalinvqty,2),
					' ',
					' ',
					' ');
			} else {
			  // Print totals for Invoiced Date Type - Don't print invoice quantity
					printf("%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %10s | %-40s ",
					_('Totals'),
					_('Lines - ') . $linectr,
					' ',
					' ',
					' ',
					' ',
					' ',
					number_format($totalqty,2),
					number_format($totalextcost,2),
					number_format($totalextprice,2),
					' ',
					' ',
					' ');			
			}
			echo '</pre>';
		} else {
		  // Print summary stuff
			$summarytype = $_POST['SummaryType'];
			$columnheader7 = ' ';
			// Set up description based on the Summary Type
			if ($summarytype == 'name') {
				$summarytype = 'name';
				$description = 'debtorno';
				$summaryheader = _('Customer Name');
				$descriptionheader =  _('Customer Number');
			}
			if ($summarytype == 'stkcode' || $summarytype == 'extprice') {
				$description = 'description';
				$summaryheader =  _('Part Number');
				$descriptionheader =  _('Part Description');
			}
			if ($summarytype == 'transno') {
				$description = 'name';
				$summaryheader =  _('Transaction Number');
				$descriptionheader =  _('Customer Name');
				$columnheader7 =  _('Order Number');
			}
			if ($summarytype == 'debtorno') {
				$description = 'name';
				$summaryheader =  _('Customer Number');
				$descriptionheader =  _('Customer Name');
			}
			if ($summarytype == 'orderno') {
				$description = 'debtorno';
				$summaryheader =  _('Order Number');
				$descriptionheader =  _('Customer Number');
				$columnheader7 =  _('Customer Name');
			}				
			if ($summarytype == 'categoryid') {
				$description = 'categorydescription';
				$summaryheader =  _('Stock Category');
				$descriptionheader =  _('Category Description');
			}
			if ($summarytype == 'salesman') {
				$description = 'salesmanname';
				$summaryheader =  _('Salesman Code');
				$descriptionheader =  _('Salesman Name');
			}
			if ($summarytype == 'area') {
				$description = 'areadescription';
				$summaryheader =  _('Sales Area');
				$descriptionheader =  _('Area Description');
			}
			if ($summarytype == 'month') {
				$description = 'monthname';
				$summaryheader =  _('Month');
				$descriptionheader =  _('Month');
			}			
			printf("    %-30s | %-40s | %12s | %14s | %14s | %14s | %-15s",
				 _($summaryheader),
				 _($descriptionheader),
				 _('Quantity'),
				 _('Extended Cost'),
				 _('Extended Price'),
				 _('Invoiced Qty'),
				 _($columnheader7));
				print '<br/><br/>';

				$column7 = ' ';
				$linectr = 0;
			while ($myrow = DB_fetch_array($result)) {
			    $linectr++;
				if ($summarytype == 'orderno') {
				    $column7 = $myrow['name'];
				}
				if ($summarytype == 'transno') {
				    $column7 =  $myrow['orderno'];
				}				
				if ($_POST['DateType'] == 'Order') {
				    // quantity is from salesorderdetails
				    $displayqty = $myrow['quantity'];
				} else {
				    // qty is from stockmoves
				    $displayqty = $myrow['qty'];
				}
				printf("    %-30s | %-40s | %12s | %14s | %14s | %14s |  %-40s",
				$myrow[$summarytype],
				$myrow[$description],
				number_format($displayqty,2),
				number_format($myrow['extcost'],2),
				number_format($myrow['extprice'],2),
				number_format($myrow['qtyinvoiced'],2),
				$column7);

				print '<br/>';
				$totalqty += $displayqty;
				$totalextcost += $myrow['extcost'];
				$totalextprice += $myrow['extprice'];
				$totalinvqty += $myrow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
				printf("    %-30s | %-40s | %12s | %14s | %14s | %14s",
				_('Totals'),
				_('Lines - ') . $linectr,
				number_format($totalqty,2),
				number_format($totalextcost,2),
				number_format($totalextprice,2),
				number_format($totalinvqty,2),
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


    echo '<tr><td>' . _('Order Type') . ':</td>';
    echo "<td><select name='OrderType'>";
	echo "<option selected value='0'>" . _('Sales Order');
	echo "<option value='1'>" . _('Quotation');
	echo '</select></td><td>&nbsp</td></tr>';
	
	echo '<tr><td>' . _('Date Type') . ':</td>';
    echo "<td><select name='DateType'>";
	echo "<option selected value='Order'>" . _('Order Date');
	echo "<option value='Invoice'>" . _('Invoice Date');
	echo '</select></td><td>&nbsp</td></tr>';

	echo '<tr><td>' . _('Invoice Type') . ':</td>';
    echo "<td><select name='InvoiceType'>";
	echo "<option selected value='All'>" . _('All');
	echo "<option value='10'>" . _('Sales Invoice');
	echo "<option value='11'>" . _('Credit Note');
	echo '</select></td><td>&nbsp</td><td>' . _('Only Applies To Invoice Date Type') . '</td></tr>';

    echo '<tr>
        <td>' . _('Date Range') . ":</td>
	    <td><input type='Text' class='date' alt='".$_SESSION['DefaultDateFormat']."' name='FromDate' size=10 maxlength=10 value=" . $_POST['FromDate'] . '></td>
        <td>' . _('To') . ":</td>
	    <td><input type='Text' class='date' alt='".$_SESSION['DefaultDateFormat']."' name='ToDate' size=10 maxlength=10 value=" . $_POST['ToDate'] . "></td>
	</tr>";

    echo '<tr><td>' . _('Part Number') . ':</td>';
    echo "<td><select name='PartNumberOp'>";
	echo "<option selected value='Equals'>" . _('Equals');
	echo "<option value='LIKE'>" . _('Begins With');
	echo '</select></td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='PartNumber' size=20 maxlength=20 value=" 
	    . $_POST['PartNumber'] . "></td></tr>";
	
    echo '<tr><td>' . _('Customer Number') . ':</td>';
    echo "<td><select name='DebtorNoOp'>";
	echo "<option selected value='Equals'>" . _('Equals');
	echo "<option value='LIKE'>" . _('Begins With');
	echo '</select></td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='DebtorNo' size=10 maxlength=10 value=" 
	    . $_POST['DebtorNo'] . "></td></tr>";

    echo '<tr><td>' . _('Customer Name') . ':</td>';
    echo "<td><select name='DebtorNameOp'>";
	echo "<option selected value='LIKE'>" . _('Begins With');
	echo "<option value='Equals'>" . _('Equals');
	echo '</select></td><td>&nbsp</td>';    
    echo "<td><input type='Text' name='DebtorName' size=30 maxlength=30 value=" 
	    . $_POST['DebtorName'] . "></td></tr>";

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

	echo '<tr><td>' . _('For Sales Man') . ":</td><td><select name='Salesman'>";
	$sql='SELECT salesmancode, salesmanname FROM salesman';
	$SalesmanResult= DB_query($sql,$db);
	echo '<option selected value="All">' . _('All Salesmen');
	While ($myrow = DB_fetch_array($SalesmanResult)){
		echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'];
	}
	echo '</select></td></tr>';

// Use name='Areas[]' multiple - if want to create an array for Areas and allow multiple selections
	echo '<tr><td>' . _('For Sales Areas') . ":</td><td><select name='Area'>";
	$sql='SELECT areacode, areadescription FROM areas';
	$AreasResult= DB_query($sql,$db);
	echo '<option selected value="All">' . _('All Areas');
	While ($myrow = DB_fetch_array($AreasResult)){
		echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'];
	}
	echo '</select></td></tr>';

	echo '<tr><td>&nbsp</td></tr>';
    echo '<tr><td>' . _('Sort By') . ':</td>';
    echo "<td><select name='SortBy'>";
	echo "<option selected value='salesorderdetails.orderno'>" . _('Order Number');
	echo "<option value='salesorderdetails.stkcode'>" . _('Part Number');
	echo "<option value='debtorsmaster.debtorno,salesorderdetails.orderno'>" . _('Customer Number');
	echo "<option value='debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno'>" . _('Customer Name');
	echo "<option value='tempstockmoves.transno,salesorderdetails.stkcode'>" . _('Transaction Number');
	echo '</select></td><td>&nbsp</td><td>Transaction Number sort only valid for Invoice Date Type</td></tr>';

   echo '<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td></tr>
	<tr><td>&nbsp</td></tr>';

	echo '<tr><td>&nbsp</td></tr>';
    echo '<tr><td>' . _('Summary Type') . ':</td>';
    echo "<td><select name='SummaryType'>";
	echo "<option selected value='orderno'>" . _('Order Number');
	echo "<option value='transno'>" . _('Transaction Number');
	echo "<option value='stkcode'>" . _('Part Number');
	echo "<option value='extprice'>" . _('Extended Price');
	echo "<option value='debtorno'>" . _('Customer Number');
	echo "<option value='name'>" . _('Customer Name');
	echo "<option value='month'>" . _('Month');
	echo "<option value='categoryid'>" . _('Stock Category');
	echo "<option value='salesman'>" . _('Salesman');
	echo "<option value='area'>" . _('Sales Area');
	echo '</select></td><td>&nbsp</td><td>Transaction Number summary only valid for Invoice Date Type</td></tr>';

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

function TempStockmoves(&$db)
{
// When report based on Invoice Date, use stockmoves as the main file, but credit
// notes, which are type 11 in stockmoves, don't have the order number in the
// reference field; instead they have "Ex Inv - " and then the transno from the
// type 10 stockmoves the credit note was applied to. Use this function to load all 
// type 10 and 11 stockmoves into a temporary table and then update the 
// reference field for type 11 records with the orderno from the type 10 records.

	$fromdate = FormatDateForSQL($_POST['FromDate']);
	$todate = FormatDateForSQL($_POST['ToDate']);

	$sql = 'CREATE TEMPORARY TABLE tempstockmoves LIKE stockmoves';
	$ErrMsg = _('The SQL to to create passbom failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
	
	$sql = "INSERT tempstockmoves 
	          SELECT * FROM stockmoves
	          WHERE (stockmoves.type='10' OR stockmoves.type='11') 
	          AND stockmoves.trandate >='" . $fromdate . 
			  "' AND stockmoves.trandate <='" . $todate . "'";
	$ErrMsg = _('The SQL to to create twmpstockmoves failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	$sql = 'UPDATE tempstockmoves, stockmoves
	          SET tempstockmoves.reference = stockmoves.reference
	          WHERE tempstockmoves.type="11" 
	            AND SUBSTR(tempstockmoves.reference,10,10) = stockmoves.transno
                AND tempstockmoves.stockid = stockmoves.stockid
                AND stockmoves.type ="10"';
	$ErrMsg = _('The SQL to to update tempstockmoves failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);


} // End of function TempStockmoves

include('includes/footer.inc');
?>
