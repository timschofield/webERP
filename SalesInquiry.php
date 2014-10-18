<?php

/* $Id: SalesInquiry.php 6458 2013-11-30 11:54:03Z exsonqu $*/
// SalesInquiry.php
// Inquiry on Sales Orders - If Date Type is Order Date, salesorderdetails is the main table
// If Date Type is Invoice, stockmoves is the main table

include('includes/session.inc');
$Title = _('Sales Inquiry');
include('includes/header.inc');

# Sets default date range for current month
if (!isset($_POST['FromDate'])){

	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['PartNumber'])){
	$PartNumber = trim(mb_strtoupper($_POST['PartNumber']));
} elseif (isset($_GET['PartNumber'])){
	$PartNumber = trim(mb_strtoupper($_GET['PartNumber']));
}

# Part Number operator - either LIKE or =
if (isset($_POST['PartNumberOp'])){
	$PartNumberOp = $_POST['PartNumberOp'];
} else {
	$PartNumberOp = '=';
}

if (isset($_POST['DebtorNo'])){
	$DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
}
if (isset($_POST['DebtorNoOp'])){
	$DebtorNoOp = $_POST['DebtorNoOp'];
} else {
	$DebtorNoOp = '=';
}
if (isset($_POST['DebtorName'])){
	$DebtorName = trim(mb_strtoupper($_POST['DebtorName']));
} elseif (isset($_GET['DebtorName'])){
	$DebtorName = trim(mb_strtoupper($_GET['DebtorName']));
}
if (isset($_POST['DebtorNameOp'])){
	$DebtorNameOp = $_POST['DebtorNameOp'];
} else {
	$DebtorNameOp = '=';
}

// Save $_POST['SummaryType'] in $SaveSummaryType because change $_POST['SummaryType'] when
// create $sql
if (isset($_POST['SummaryType'])){
	$SaveSummaryType = $_POST['SummaryType'];
} else {
	$SaveSummaryType = 'name';
}

if (isset($_POST['submit'])) {
    submit($db,$PartNumber,$PartNumberOp,$DebtorNo,$DebtorNoOp,$DebtorName,$DebtorNameOp,$SaveSummaryType);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db,$PartNumber,$PartNumberOp,$DebtorNo,$DebtorNoOp,$DebtorName,$DebtorNameOp,$SaveSummaryType) {

	//initialise no input errors
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

	if ($_POST['ReportType'] == 'Summary' AND $_POST['DateType'] == 'Order'  AND $_POST['SummaryType'] == 'transno') {
		$InputError = 1;
		prnMsg(_('Cannot summarize by transaction number with a date type of Order Date'),'error');
		return;
	}

	if ($_POST['ReportType'] == 'Detail' AND $_POST['DateType'] == 'Order'  AND $_POST['SortBy'] == 'tempstockmoves.transno,salesorderdetails.stkcode') {
		$InputError = 1;
		prnMsg(_('Cannot sort by transaction number with a date type of Order Date'),'error');
		return;
	}

// TempStockmoves function creates a temporary table of stockmoves that is used when the DateType
// is Invoice Date
	if ($_POST['DateType'] == 'Invoice') {
		TempStockmoves($db);
	}

	# Add more to WHERE statement, if user entered something for the part number,debtorno, name
	// Variables that end with Op - meaning operator - are either = or LIKE
	$WherePart = ' ';
	if (mb_strlen($PartNumber) > 0 AND $PartNumberOp == 'LIKE') {
	    $PartNumber = $PartNumber . '%';
	} else {
	    $PartNumberOp = '=';
	}
	if (mb_strlen($PartNumber) > 0) {
	    $WherePart = " AND salesorderdetails.stkcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	}

	$WhereDebtorNo = ' ';
	if ($DebtorNoOp == 'LIKE') {
	    $DebtorNo = $DebtorNo . '%';
	} else {
	    $DebtorNoOp = '=';
	}
	if (mb_strlen($DebtorNo) > 0) {
	    $WhereDebtorNo = " AND salesorders.debtorno " . $DebtorNoOp . " '" . $DebtorNo . "'  ";
	} else {
		$WhereDebtorNo = ' ';
	}

	$WhereDebtorName = ' ';
	if (mb_strlen($DebtorName) > 0 AND $DebtorNameOp == 'LIKE') {
	    $DebtorName = $DebtorName . '%';
	} else {
	    $DebtorNameOp = '=';
	}
	if (mb_strlen($DebtorName) > 0) {
	    $WhereDebtorName = " AND debtorsmaster.name " . $DebtorNameOp . " '" . $DebtorName . "'  ";
	}
	if (mb_strlen($_POST['OrderNo']) > 0) {
	    $WhereOrderNo = " AND salesorderdetails.orderno = " . " '" . $_POST['OrderNo'] . "'  ";
	} else {
		$WhereOrderNo =  " ";
	}

    $WhereLineStatus = ' ';
    # Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
    #in WHERE clause because the WHERE clause did not recognize
    # that had used the IF statement to create a field caused linestatus
    if ($_POST['LineStatus'] != 'All') {
        $WhereLineStatus = " AND IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced ||
		  salesorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
    }

    // The following is from PDFCustomerList.php and shows how to set up WHERE clause
    // for multiple selections from Areas - decided to just allow selection of one Area at
    // a time, so used simpler code
	 $WhereArea = ' ';
    if ($_POST['Area'] != 'All') {
        $WhereArea = " AND custbranch.area = '" . $_POST['Area'] . "'";
    }

	$WhereSalesman = ' ';
	if ($_SESSION['SalesmanLogin'] != '') {

		$WhereSalesman .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";

	}elseif ($_POST['Salesman'] != 'All') {

        $WhereSalesman = " AND custbranch.salesman = '" . $_POST['Salesman'] . "'";
    }

 	 $WhereCategory = ' ';
    if ($_POST['Category'] != 'All') {
        $WhereCategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
    }

// Only used for Invoice Date type where tempstockmoves is the main table
 	 $WhereType = " AND (tempstockmoves.type='10' OR tempstockmoves.type='11')";
    if ($_POST['InvoiceType'] != 'All') {
        $WhereType = " AND tempstockmoves.type = '" . $_POST['InvoiceType'] . "'";
    }
    if ($InputError !=1) {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
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
							   (salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
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
						LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
						WHERE salesorders.orddate >='" . $FromDate . "'
						 AND salesorders.orddate <='" . $ToDate . "'
						 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
						$WherePart .
						$WhereOrderNo .
						$WhereDebtorNo .
						$WhereDebtorName .
						$WhereLineStatus .
						$WhereArea .
						$WhereSalesman .
						$WhereCategory .
						"ORDER BY " . $_POST['SortBy'];
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
							   (tempstockmoves.qty * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) * -1 / currencies.rate as extprice,
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
						LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
						WHERE tempstockmoves.trandate >='" . $FromDate . "'
						 AND tempstockmoves.trandate <='" . $ToDate . "'
						 AND tempstockmoves.stockid=salesorderdetails.stkcode
						 AND tempstockmoves.hidemovt=0
						 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " .
						$WherePart .
						$WhereType .
						$WhereOrderNo .
						$WhereDebtorNo .
						$WhereDebtorName .
						$WhereLineStatus .
						$WhereArea .
						$WhereSalesman .
						$WhereCategory .
						"ORDER BY " . $_POST['SortBy'];
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
		      if ($_POST['SummaryType'] == 'extprice' OR $_POST['SummaryType'] == 'stkcode') {
					$sql = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",salesorderdetails.stkcode,
								   stockmaster.description,
								   stockmaster.decimalplaces
								   ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$sql = "SELECT salesorderdetails.orderno,
					               salesorders.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate  . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",salesorders.debtorno,
								   debtorsmaster.name
								   ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'debtorno' OR $_POST['SummaryType'] == 'name') {
				    if ($_POST['SummaryType'] == 'name') {
				        $orderby = 'name';
				    }
					$sql = "SELECT debtorsmaster.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY debtorsmaster.debtorno
							,debtorsmaster.name
							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'month') {
					$sql = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",monthname
							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$sql = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",categorydescription

							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'salesman') {
					$sql = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",salesmanname
							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'area') {
					$sql = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent)) / currencies.rate as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " .
							$WherePart .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",areas.areadescription
							ORDER BY " . $orderby;
				}
		   } else {
		        // Selects by tempstockmoves.trandate not order date
		      if ($_POST['SummaryType'] == 'extprice' OR $_POST['SummaryType'] == 'stkcode') {
					$sql = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",stockmaster.description
							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'orderno') {
					$sql = "SELECT salesorderdetails.orderno,
					               salesorders.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",salesorders.debtorno,
							  debtorsmaster.name
							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'debtorno' OR $_POST['SummaryType'] == 'name') {
				    if ($_POST['SummaryType'] == 'name') {
				        $orderby = 'name';
				    }
					$sql = "SELECT debtorsmaster.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY debtorsmaster.debtorno" . ' ' .
							",debtorsmaster.name
							ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'month') {
					$sql = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",monthname
						    ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'categoryid') {
					$sql = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",categorydescription
						    ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'salesman') {
					$sql = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",salesmanname
						    ORDER BY " . $orderby;
				} elseif ($_POST['SummaryType'] == 'area') {
					$sql = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price) * -1 / currencies.rate as extprice,
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
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
						     AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" .
							$WherePart .
							$WhereType .
							$WhereOrderNo .
							$WhereDebtorNo .
							$WhereDebtorName .
							$WhereLineStatus .
							$WhereArea .
							$WhereSalesman .
							$WhereCategory .
							"GROUP BY " . $_POST['SummaryType'] .
							",areas.areadescription
						    ORDER BY " . $orderby;
				}
		   }
		} // End of if ($_POST['ReportType']
		//echo "<br/>$sql<br/>";
		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);
		$ctr = 0;
		echo '<pre>';
		$TotalQty = 0;
		$TotalExtCost = 0;
		$TotalExtPrice = 0;
		$TotalInvQty = 0;

	// Create array for summary type to display in header. Access it with $SaveSummaryType
	$Summary_Array['orderno'] =  _('Order Number');
	$Summary_Array['stkcode'] =  _('Stock Code');
	$Summary_Array['extprice'] =  _('Extended Price');
	$Summary_Array['debtorno'] =  _('Customer Code');
	$Summary_Array['name'] =  _('Customer Name');
	$Summary_Array['month'] =  _('Month');
	$Summary_Array['categoryid'] =  _('Stock Category');
	$Summary_Array['salesman'] =  _('Salesman');
	$Summary_Array['area'] = _('Sales Area');
	$Summary_Array['transno'] = _('Transaction Number');
    // Create array for sort for detail report to display in header
    $Detail_Array['salesorderdetails.orderno'] = _('Order Number');
	$Detail_Array['salesorderdetails.stkcode'] = _('Stock Code');
	$Detail_Array['debtorsmaster.debtorno,salesorderdetails.orderno'] = _('Customer Code');
	$Detail_Array['debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno'] = _('Customer Name');
	$Detail_Array['tempstockmoves.transno,salesorderdetails.stkcode'] = _('Transaction Number');

		// Display Header info
		if ($_POST['ReportType'] == 'Summary') {
		    $SortBy_Display = $Summary_Array[$SaveSummaryType];
		} else {
		    $SortBy_Display = $Detail_Array[$_POST['SortBy']];
		}
		echo '  ' . _('Sales Inquiry') . ' - ' . $_POST['ReportType'] . ' ' . _('By') . ' ' . $SortBy_Display . '<br/>';
		if ($_POST['OrderType'] == '0') {
		    echo '  ' . _('Order Type - Sales Orders') . '<br/>';
		} else {
		    echo '  ' . _('Order Type - Quotations') . '<br/>';
		}
		echo '  ' . _('Date Type') . ' - ' . $_POST['DateType'] . '<br/>';
		echo '  ' . _('Date Range') . ' - ' . $_POST['FromDate'] . ' ' . _('To') . ' ' .  $_POST['ToDate'] . '<br/>';
		if (mb_strlen(trim($PartNumber)) > 0) {
			echo '  ' . _('Stock Code') . ' - ' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '<br/>';
		}
		if (mb_strlen(trim($_POST['DebtorNo'])) > 0) {
			echo '  ' . _('Customer Code') . ' - ' . $_POST['DebtorNoOp'] . ' ' . $_POST['DebtorNo'] . '<br/>';
		}
		if (mb_strlen(trim($_POST['DebtorName'])) > 0) {
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
				printf('%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					 _('Order No'),
					 _('Stock Code'),
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
					 _('Item Description'));
			} else {
			    // Headings for Invoiced Date
				printf('%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					 _('Order No'),
					 _('Trans. No'),
					 _('Stock Code'),
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
					 _('Item Description'));
			}
				print '<br/><br/>';
				$linectr = 0;
			while ($myrow = DB_fetch_array($result)) {
			    $linectr++;
			    if ($_POST['DateType'] == 'Order') {
					printf('%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					$myrow['orderno'],
					$myrow['stkcode'],
					ConvertSQLDate($myrow['orddate']),
					$myrow['debtorno'],
					$myrow['name'],
					$myrow['brname'],
					locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
					locale_number_format($myrow['extcost'],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($myrow['extprice'],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']),
					$myrow['linestatus'],
					ConvertSQLDate($myrow['itemdue']),
					$myrow['salesman'],
					$myrow['area'],
					$myrow['description']);
					print '<br/>';
					$TotalQty += $myrow['quantity'];
				} else {
				    // Detail for Invoiced Date
					printf('%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					$myrow['orderno'],
					$myrow['transno'],
					$myrow['stkcode'],
					ConvertSQLDate($myrow['orddate']),
					$myrow['debtorno'],
					$myrow['name'],
					$myrow['brname'],
					locale_number_format($myrow['qty'],$myrow['decimalplaces']),
					locale_number_format($myrow['extcost'],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($myrow['extprice'],$_SESSION['CompanyRecord']['decimalplaces']),
					$myrow['linestatus'],
					ConvertSQLDate($myrow['trandate']),
					$myrow['salesman'],
					$myrow['area'],
					$myrow['description']);
					print '<br/>';
					$TotalQty += $myrow['qty'];
				}
				$lastdecimalplaces = $myrow['decimalplaces'];
				$TotalExtCost += $myrow['extcost'];
				$TotalExtPrice += $myrow['extprice'];
				$TotalInvQty += $myrow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
			if ($_POST['DateType'] == 'Order') {
					printf('%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-40s ',
					_('Totals'),
					_('Lines - ') . $linectr,
					' ',
					' ',
					' ',
					' ',
					locale_number_format($TotalQty,2),
					locale_number_format($TotalExtCost,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($TotalExtPrice,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($TotalInvQty,2),
					' ',
					' ',
					' ');
			} else {
			  // Print totals for Invoiced Date Type - Don't print invoice quantity
					printf('%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %10s | %-40s ',
					_('Totals'),
					_('Lines - ') . $linectr,
					' ',
					' ',
					' ',
					' ',
					' ',
					locale_number_format($TotalQty,2),
					locale_number_format($TotalExtCost,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($TotalExtPrice,$_SESSION['CompanyRecord']['decimalplaces']),
					' ',
					' ',
					' ');
			}
			echo '</pre>';
		} else {
		  // Print summary stuff
			$SummaryType = $_POST['SummaryType'];
			$columnheader7 = ' ';
			// Set up description based on the Summary Type
			if ($SummaryType == 'name') {
				$SummaryType = 'name';
				$Description = 'debtorno';
				$SummaryHeader = _('Customer Name');
				$Descriptionheader =  _('Customer Code');
			}
			if ($SummaryType == 'stkcode' OR $SummaryType == 'extprice') {
				$Description = 'Description';
				$SummaryHeader =  _('Stock Code');
				$Descriptionheader =  _('Item Description');
			}
			if ($SummaryType == 'transno') {
				$Description = 'name';
				$SummaryHeader =  _('Transaction Number');
				$Descriptionheader =  _('Customer Name');
				$columnheader7 =  _('Order Number');
			}
			if ($SummaryType == 'debtorno') {
				$Description = 'name';
				$SummaryHeader =  _('Customer Code');
				$Descriptionheader =  _('Customer Name');
			}
			if ($SummaryType == 'orderno') {
				$Description = 'debtorno';
				$SummaryHeader =  _('Order Number');
				$Descriptionheader =  _('Customer Code');
				$columnheader7 =  _('Customer Name');
			}
			if ($SummaryType == 'categoryid') {
				$SummaryHeader =  _('Stock Category');
				$Descriptionheader =  _('Category Description');
			}
			if ($SummaryType == 'salesman') {
				$Description = 'salesmanname';
				$SummaryHeader =  _('Salesman Code');
				$Descriptionheader =  _('Salesman Name');
			}
			if ($SummaryType == 'area') {
				$Description = 'areadescription';
				$SummaryHeader =  _('Sales Area');
				$Descriptionheader =  _('Area Description');
			}
			if ($SummaryType == 'month') {
				$Description = 'monthname';
				$SummaryHeader =  _('Month');
				$Descriptionheader =  _('Month');
			}
			printf('    %-30s | %-40s | %12s | %14s | %14s | %14s | %-15s',
				 _($SummaryHeader),
				 _($Descriptionheader),
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
				if ($SummaryType == 'orderno') {
				    $column7 = $myrow['name'];
				}
				if ($SummaryType == 'transno') {
				    $column7 =  $myrow['orderno'];
				}
				if ($_POST['DateType'] == 'Order') {
				    // quantity is from salesorderdetails
				    $DisplayQty = $myrow['quantity'];
				} else {
				    // qty is from stockmoves
				    $DisplayQty = $myrow['qty'];
				}
				printf('    %-30s | %-40s | %12s | %14s | %14s | %14s |  %-40s',
				$myrow[$SummaryType],
				$myrow[$Description],
				locale_number_format($DisplayQty,2),
				locale_number_format($myrow['extcost'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['extprice'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['qtyinvoiced'],2),
				$column7);

				print '<br/>';
				$TotalQty += $DisplayQty;
				$TotalExtCost += $myrow['extcost'];
				$TotalExtPrice += $myrow['extprice'];
				$TotalInvQty += $myrow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
				printf('    %-30s | %-40s | %12s | %14s | %14s | %14s',
				_('Totals'),
				_('Lines - ') . $linectr,
				locale_number_format($TotalQty,2),
				locale_number_format($TotalExtCost,$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($TotalExtPrice,$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($TotalInvQty,2),
				' ');
			echo '</pre>';
		} // End of if ($_POST['ReportType']

    } // End of if inputerror != 1
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>';

	echo '<tr>
			<td>' . _('Report Type') . ':</td>
			<td><select name="ReportType">
				<option selected="selected" value="Detail">' . _('Detail') . '</option>
				<option value="Summary">' . _('Summary') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Order Type') . ':</td>
			<td><select name="OrderType">
				<option selected="selected" value="0">' . _('Sales Order') . '</option>
				<option value="1">' . _('Quotation') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Type') . ':</td>
			<td><select name="DateType">
				<option selected="selected" value="Order">' . _('Order Date') . '</option>
				<option value="Invoice">' . _('Invoice Date') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Invoice Type') . ':</td>
			<td><select name="InvoiceType">
				<option selected="selected" value="All">' . _('All') . '</option>
				<option value="10">' . _('Sales Invoice') . '</option>
				<option value="11">' . _('Credit Note') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td>' . _('Only Applies To Invoice Date Type') . '</td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Range') . ':</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="FromDate" size="10" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';
	if (!isset($_POST['PartNumber'])){
		$_POST['PartNumber']='';
	}
	echo '<tr>
			<td>' . _('Stock Code') . ':</td>
			<td><select name="PartNumberOp">
				<option selected="selected" value="Equals">' . _('Equals') . '</option>
				<option value="LIKE">' . _('Begins With') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td><input type="text" name="PartNumber" size="20" maxlength="20" value="'. $_POST['PartNumber'] . '" /></td>
		</tr>';
	if (!isset($_POST['DebtorNo'])){
		$_POST['DebtorNo']='';
	}
	echo '<tr>
			<td>' . _('Customer Number') . ':</td>
			<td><select name="DebtorNoOp">
				<option selected="selected" value="Equals">' . _('Equals') . '</option>
				<option value="LIKE">' . _('Begins With') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td><input type="text" name="DebtorNo" size="10" maxlength="10" value="' . $_POST['DebtorNo'] . '" /></td>
		</tr>';
	if (!isset($_POST['DebtorName'])){
		$_POST['DebtorName']='';
	}
	echo '<tr>
			<td>' . _('Customer Name') . ':</td>
			<td><select name="DebtorNameOp">
				<option selected="selected" value="LIKE">' . _('Begins With') . '</option>
				<option value="Equals">' . _('Equals') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td><input type="text" name="DebtorName" size="30" maxlength="30" value="' . $_POST['DebtorName'] .'" /></td>
		</tr>';
	if (!isset($_POST['OrderNo'])){
		$_POST['OrderNo']='';
	}
    echo '<tr>
			<td>' . _('Order Number') . ':</td>
			<td>' . _('Equals') . '</td>
			<td>&nbsp;</td>
			<td><input type="text" name="OrderNo" size="10" maxlength="10" value="' . $_POST['OrderNo'] . '" /></td>
		</tr>';

    echo '<tr>
			<td>' . _('Line Item Status') . ':</td>
			<td><select name="LineStatus">
				<option selected="selected" value="All">' . _('All') . '</option>
				<option value="Completed">' . _('Completed') . '</option>
				<option value="Open">' . _('Not Completed') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Stock Categories') . ':</td>
			<td><select name="Category">';

	$CategoryResult= DB_query("SELECT categoryid, categorydescription FROM stockcategory",$db);
	echo '<option selected="selected" value="All">' . _('All Categories')  . '</option>';
	While ($myrow = DB_fetch_array($CategoryResult)){
		echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription']  . '</option>';
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('For Sales Person') . ':</td>';
	if ($_SESSION['SalesmanLogin'] != '') {
		echo '<td>';
		echo $_SESSION['UsersRealName'];
		echo '</td>';
	}else{
		echo '<td><select name="Salesman">';
		$sql="SELECT salesmancode, salesmanname FROM salesman";
		$SalesmanResult= DB_query($sql,$db);
		echo '<option selected="selected" value="All">' . _('All Salesmen')  . '</option>';
		While ($myrow = DB_fetch_array($SalesmanResult)){
			echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname']  . '</option>';
		}
		echo '</select></td>';
	}
	echo '</tr>';

// Use name='Areas[]' multiple - if want to create an array for Areas and allow multiple selections
	echo '<tr><td>' . _('For Sales Areas') . ':</td>
				<td><select name="Area">';
	$AreasResult= DB_query("SELECT areacode, areadescription FROM areas",$db);
	echo '<option selected="selected" value="All">' . _('All Areas')  . '</option>';
	While ($myrow = DB_fetch_array($AreasResult)){
		echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription']  . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr>
			<td>' . _('Sort By') . ':</td>
			<td><select name="SortBy">
				<option selected="selected" value="salesorderdetails.orderno">' . _('Order Number') . '</option>
				<option value="salesorderdetails.stkcode">' . _('Stock Code') . '</option>
				<option value="debtorsmaster.debtorno,salesorderdetails.orderno">' . _('Customer Number') . '</option>
				<option value="debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno">' . _('Customer Name') . '</option>
				<option value="tempstockmoves.transno,salesorderdetails.stkcode">' . _('Transaction Number') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td>' . _('Transaction Number sort only valid for Invoice Date Type') . '</td>
		</tr>';

   echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>';

	echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td>' . _('Summary Type') . ':</td>
			<td><select name="SummaryType">
				<option selected="selected" value="orderno">' . _('Order Number') . '</option>
				<option value="transno">' . _('Transaction Number') . '</option>
				<option value="stkcode">' . _('Stock Code') . '</option>
				<option value="extprice">' . _('Extended Price') . '</option>
				<option value="debtorno">' . _('Customer Code') . '</option>
				<option value="name">' . _('Customer Name') . '</option>
				<option value="month">' . _('Month') . '</option>
				<option value="categoryid">' . _('Stock Category') . '</option>
				<option value="salesman">' . _('Salesman') . '</option>
				<option value="area">' . _('Sales Area') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td>' . _('Transaction Number summary only valid for Invoice Date Type') . '</td>
		</tr>';

  echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Run Inquiry') . '" /></td>
		</tr>
		</table>
	<br />';
   echo '</div>
         </form>';

} // End of function display()

function TempStockmoves(&$db) {
// When report based on Invoice Date, use stockmoves as the main file, but credit
// notes, which are type 11 in stockmoves, do not have the order number in the
// reference field; instead they have "Ex Inv - " and then the transno from the
// type 10 stockmoves the credit note was applied to. Use this function to load all
// type 10 and 11 stockmoves into a temporary table and then update the
// reference field for type 11 records with the orderno from the type 10 records.

	$FromDate = FormatDateForSQL($_POST['FromDate']);
	$ToDate = FormatDateForSQL($_POST['ToDate']);

	$sql = "CREATE TEMPORARY TABLE tempstockmoves LIKE stockmoves";
	$ErrMsg = _('The SQL to the create temp stock moves table failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	$sql = "INSERT tempstockmoves
	          SELECT * FROM stockmoves
	          WHERE (stockmoves.type='10' OR stockmoves.type='11')
	          AND stockmoves.trandate >='" . $FromDate .
			  "' AND stockmoves.trandate <='" . $ToDate . "'";
	$ErrMsg = _('The SQL to insert temporary stockmoves records failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	$sql = "UPDATE tempstockmoves, stockmoves
	          SET tempstockmoves.reference = stockmoves.reference
	          WHERE tempstockmoves.type='11'
	            AND SUBSTR(tempstockmoves.reference,10,10) = stockmoves.transno
                AND tempstockmoves.stockid = stockmoves.stockid
                AND stockmoves.type ='10'";
	$ErrMsg = _('The SQL to update tempstockmoves failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);


} // End of function TempStockmoves

include('includes/footer.inc');
?>
