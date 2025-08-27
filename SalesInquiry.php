<?php

// Inquiry on Sales Orders - If Date Type is Order Date, salesorderdetails is the main table
// If Date Type is Invoice, stockmoves is the main table

require(__DIR__ . '/includes/session.php');

$Title = __('Sales Inquiry');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

# Sets default date range for current month
if(!isset($_POST['FromDate'])) {

	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if(!isset($_POST['ToDate'])) {
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if(isset($_POST['PartNumber'])) {
	$PartNumber = trim(mb_strtoupper($_POST['PartNumber']));
} elseif(isset($_GET['PartNumber'])) {
	$PartNumber = trim(mb_strtoupper($_GET['PartNumber']));
}

# Part Number operator - either LIKE or =
if(isset($_POST['PartNumberOp'])) {
	$PartNumberOp = $_POST['PartNumberOp'];
} else {
	$PartNumberOp = '=';
}

if(isset($_POST['DebtorNo'])) {
	$DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
} elseif(isset($_GET['DebtorNo'])) {
	$DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
}
if(isset($_POST['DebtorNoOp'])) {
	$DebtorNoOp = $_POST['DebtorNoOp'];
} else {
	$DebtorNoOp = '=';
}
if(isset($_POST['DebtorName'])) {
	$DebtorName = trim(mb_strtoupper($_POST['DebtorName']));
} elseif(isset($_GET['DebtorName'])) {
	$DebtorName = trim(mb_strtoupper($_GET['DebtorName']));
}
if(isset($_POST['DebtorNameOp'])) {
	$DebtorNameOp = $_POST['DebtorNameOp'];
} else {
	$DebtorNameOp = '=';
}

// Save $_POST['SummaryType'] in $SaveSummaryType because change $_POST['SummaryType'] when
// create $SQL
if(isset($_POST['SummaryType'])) {
	$SaveSummaryType = $_POST['SummaryType'];
} else {
	$SaveSummaryType = 'name';
}

if(isset($_POST['submit'])) {
    submit($PartNumber,$PartNumberOp,$DebtorNo,$DebtorNoOp,$DebtorName,$DebtorNameOp,$SaveSummaryType);
} else {
    display();
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($PartNumber,$PartNumberOp,$DebtorNo,$DebtorNoOp,$DebtorName,$DebtorNameOp,$SaveSummaryType) {

	//initialise no input errors
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if(!Is_Date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(__('Invalid From Date'),'error');
	}
	if(!Is_Date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(__('Invalid To Date'),'error');
	}

	if($_POST['ReportType'] == 'Summary' AND $_POST['DateType'] == 'Order'  AND $_POST['SummaryType'] == 'transno') {
		$InputError = 1;
		prnMsg(__('Cannot summarize by transaction number with a date type of Order Date'),'error');
		return;
	}

	if($_POST['ReportType'] == 'Detail' AND $_POST['DateType'] == 'Order'  AND $_POST['SortBy'] == 'tempstockmoves.transno,salesorderdetails.stkcode') {
		$InputError = 1;
		prnMsg(__('Cannot sort by transaction number with a date type of Order Date'),'error');
		return;
	}
	if (!in_array($_POST['SortBy'],array('salesorderdetails.orderno',
						'salesorderdetails.stkcode',
						'debtorsmaster.debtorno,salesorderdetails.orderno',
						'debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno',
						'tempstockmoves.transno,salesorderdetails.stkcode',
						'salesorderdetails.itemdue,salesorderdetails.orderno'))) {
		$InputError = 1;
		prnMsg(__('The sorting order is not defined'),'error');
		return;
	}


// TempStockmoves function creates a temporary table of stockmoves that is used when the DateType
// is Invoice Date
	if($_POST['DateType'] == 'Invoice') {
		TempStockmoves();
	}

	# Add more to WHERE statement, if user entered something for the part number,debtorno, name
	// Variables that end with Op - meaning operator - are either = or LIKE
	$WherePart = ' ';
	if(mb_strlen($PartNumber) > 0 AND $PartNumberOp == 'LIKE') {
	    $PartNumber = $PartNumber . '%';
	} else {
	    $PartNumberOp = '=';
	}
	if(mb_strlen($PartNumber) > 0) {
	    $WherePart = " AND salesorderdetails.stkcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	}

	$WhereDebtorNo = ' ';
	if($DebtorNoOp == 'LIKE') {
	    $DebtorNo = $DebtorNo . '%';
	} else {
	    $DebtorNoOp = '=';
	}
	if(mb_strlen($DebtorNo) > 0) {
	    $WhereDebtorNo = " AND salesorders.debtorno " . $DebtorNoOp . " '" . $DebtorNo . "'  ";
	} else {
		$WhereDebtorNo = ' ';
	}

	$WhereDebtorName = ' ';
	if(mb_strlen($DebtorName) > 0 AND $DebtorNameOp == 'LIKE') {
	    $DebtorName = $DebtorName . '%';
	} else {
	    $DebtorNameOp = '=';
	}
	if(mb_strlen($DebtorName) > 0) {
	    $WhereDebtorName = " AND debtorsmaster.name " . $DebtorNameOp . " '" . $DebtorName . "'  ";
	}
	if(mb_strlen($_POST['OrderNo']) > 0) {
	    $WhereOrderNo = " AND salesorderdetails.orderno = " . " '" . $_POST['OrderNo'] . "'  ";
	} else {
		$WhereOrderNo =  " ";
	}

    $WhereLineStatus = ' ';
    # Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
    #in WHERE clause because the WHERE clause did not recognize
    # that had used the IF statement to create a field caused linestatus
    if($_POST['LineStatus'] != 'All') {
        $WhereLineStatus = " AND IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced ||
		  salesorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
    }

    // The following is from PDFCustomerList.php and shows how to set up WHERE clause
    // for multiple selections from Areas - decided to just allow selection of one Area at
    // a time, so used simpler code
	 $WhereArea = ' ';
    if($_POST['Area'] != 'All') {
        $WhereArea = " AND custbranch.area = '" . $_POST['Area'] . "'";
    }

	$WhereSalesman = ' ';
	if($_SESSION['SalesmanLogin'] != '') {

		$WhereSalesman .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";

	}elseif($_POST['Salesman'] != 'All') {

        $WhereSalesman = " AND custbranch.salesman = '" . $_POST['Salesman'] . "'";
    }

 	 $WhereCategory = ' ';
    if($_POST['Category'] != 'All') {
        $WhereCategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
    }

// Only used for Invoice Date type where tempstockmoves is the main table
 	 $WhereType = " AND (tempstockmoves.type='10' OR tempstockmoves.type='11')";
    if($_POST['InvoiceType'] != 'All') {
        $WhereType = " AND tempstockmoves.type = '" . $_POST['InvoiceType'] . "'";
    }
    if($InputError !=1) {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		if($_POST['ReportType'] == 'Detail') {
		    if($_POST['DateType'] == 'Order') {
				$SQL = "SELECT salesorderdetails.orderno,
							   salesorderdetails.stkcode,
							   salesorderdetails.itemdue,
							   salesorders.debtorno,
							   salesorders.orddate,
							   salesorders.branchcode,
							   salesorderdetails.quantity,
							   salesorderdetails.qtyinvoiced,
							   (salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
				$SQL = "SELECT salesorderdetails.orderno,
							   salesorderdetails.stkcode,
							   salesorderdetails.itemdue,
							   salesorders.debtorno,
							   salesorders.orddate,
							   salesorders.branchcode,
							   salesorderdetails.quantity,
							   salesorderdetails.qtyinvoiced,
							   (tempstockmoves.qty * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) * -1 / currencies.rate) as extprice,
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
		  $OrderBy = $_POST['SummaryType'];
		  // The following is because the 'extprice' summary is a special case - with the other
		  // summaries, you group and order on the same field; with 'extprice', you are actually
		  // grouping on the stkcode and ordering by extprice descending
		  if($_POST['SummaryType'] == 'extprice') {
		      $_POST['SummaryType'] = 'stkcode';
		      $OrderBy = 'extprice DESC';
		  }
		  if($_POST['DateType'] == 'Order') {
		      if($_POST['SummaryType'] == 'extprice' OR $_POST['SummaryType'] == 'stkcode') {
					$SQL = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
								   ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT salesorderdetails.orderno,
					               salesorders.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
								   ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'debtorno' OR $_POST['SummaryType'] == 'name') {
				    if($_POST['SummaryType'] == 'name') {
				        $OrderBy = 'name';
				    }
					$SQL = "SELECT debtorsmaster.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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

							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'salesman') {
					$SQL = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'area') {
					$SQL = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				}
		   } else {
		        // Selects by tempstockmoves.trandate not order date
		      if($_POST['SummaryType'] == 'extprice' OR $_POST['SummaryType'] == 'stkcode') {
					$SQL = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT salesorderdetails.orderno,
					               salesorders.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'debtorno' OR $_POST['SummaryType'] == 'name') {
				    if($_POST['SummaryType'] == 'name') {
				        $OrderBy = 'name';
				    }
					$SQL = "SELECT debtorsmaster.debtorno,
					               debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
							ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
						    ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
						    ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'salesman') {
					$SQL = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
						    ORDER BY " . $OrderBy;
				} elseif($_POST['SummaryType'] == 'area') {
					$SQL = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
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
						    ORDER BY " . $OrderBy;
				}
		   }
		} // End of if($_POST['ReportType']
		//echo "<br/>$SQL<br/>";
		$ErrMsg = __('The SQL to find the parts selected failed with the message');
		$Result = DB_query($SQL, $ErrMsg);
		$ctr = 0;
		echo '<pre>';
		$TotalQty = 0;
		$TotalExtCost = 0;
		$TotalExtPrice = 0;
		$TotalInvQty = 0;

	// Create array for summary type to display in header. Access it with $SaveSummaryType
	$Summary_Array['orderno'] =  __('Order Number');
	$Summary_Array['stkcode'] =  __('Stock Code');
	$Summary_Array['extprice'] =  __('Extended Price');
	$Summary_Array['debtorno'] =  __('Customer Code');
	$Summary_Array['name'] =  __('Customer Name');
	$Summary_Array['month'] =  __('Month');
	$Summary_Array['categoryid'] =  __('Stock Category');
	$Summary_Array['salesman'] =  __('Salesman');
	$Summary_Array['area'] = __('Sales Area');
	$Summary_Array['transno'] = __('Transaction Number');
    // Create array for sort for detail report to display in header
    $Detail_Array['salesorderdetails.orderno'] = __('Order Number');
	$Detail_Array['salesorderdetails.stkcode'] = __('Stock Code');
	$Detail_Array['debtorsmaster.debtorno,salesorderdetails.orderno'] = __('Customer Code');
	$Detail_Array['debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno'] = __('Customer Name');
	$Detail_Array['tempstockmoves.transno,salesorderdetails.stkcode'] = __('Transaction Number');

		// Display Header info
		if($_POST['ReportType'] == 'Summary') {
		    $SortBy_Display = $Summary_Array[$SaveSummaryType];
		} else {
		    $SortBy_Display = $Detail_Array[$_POST['SortBy']];
		}
		echo '  ' . __('Sales Inquiry') . ' - ' . $_POST['ReportType'] . ' ' . __('By') . ' ' . $SortBy_Display . '<br/>';
		if($_POST['OrderType'] == '0') {
		    echo '  ' . __('Order Type - Sales Orders') . '<br/>';
		} else {
		    echo '  ' . __('Order Type - Quotations') . '<br/>';
		}
		echo '  ' . __('Date Type') . ' - ' . $_POST['DateType'] . '<br/>';
		echo '  ' . __('Date Range') . ' - ' . $_POST['FromDate'] . ' ' . __('To') . ' ' .  $_POST['ToDate'] . '<br/>';
		if(mb_strlen(trim($PartNumber)) > 0) {
			echo '  ' . __('Stock Code') . ' - ' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '<br/>';
		}
		if(mb_strlen(trim($_POST['DebtorNo'])) > 0) {
			echo '  ' . __('Customer Code') . ' - ' . $_POST['DebtorNoOp'] . ' ' . $_POST['DebtorNo'] . '<br/>';
		}
		if(mb_strlen(trim($_POST['DebtorName'])) > 0) {
			echo '  ' . __('Customer Name') . ' - ' . $_POST['DebtorNameOp'] . ' ' . $_POST['DebtorName'] . '<br/>';
		}
		echo '  ' . __('Line Item Status') . '  - ' . $_POST['LineStatus'] . '<br/>';
		echo '  ' . __('Stock Category') . '  - ' . $_POST['Category'] . '<br/>';
		echo '  ' . __('Salesman') . '  - ' . $_POST['Salesman'] . '<br/>';
		echo '  ' . __('Sales Area') . '  - ' . $_POST['Area'] . '<br/>';
		if ($_POST['DateType'] != 'Order') {
		    $IType = 'All';
		    if($_POST['InvoiceType'] == '10') {
		        $IType = 'Sales Invoice';
		    } elseif($_POST['InvoiceType'] == '11') {
		        $IType = 'Credit Notes';
		    }
		    echo '  ' . __('Invoice Type') . '  - ' . $IType . '<br/>';
        }
		echo '<br/><br/>';
		if($_POST['ReportType'] == 'Detail') {
		    if($_POST['DateType'] == 'Order') {
				printf('%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					 __('Order No'),
					 __('Stock Code'),
					 __('Order Date'),
					 __('Debtor No'),
					 __('Debtor Name'),
					 __('Branch Name'),
					 __('Order Qty'),
					 __('Extended Cost'),
					 __('Extended Price'),
					 __('Invoiced Qty'),
					 __('Line Status'),
					 __('Item Due'),
					 __('Salesman'),
					 __('Area'),
					 __('Item Description'));
			} else {
			    // Headings for Invoiced Date
				printf('%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					 __('Order No'),
					 __('Trans. No'),
					 __('Stock Code'),
					 __('Order Date'),
					 __('Debtor No'),
					 __('Debtor Name'),
					 __('Branch Name'),
					 __('Invoiced Qty'),
					 __('Extended Cost'),
					 __('Extended Price'),
					 __('Line Status'),
					 __('Invoiced'),
					 __('Salesman'),
					 __('Area'),
					 __('Item Description'));
			}
				print '<br/><br/>';
				$Linectr = 0;
			while ($MyRow = DB_fetch_array($Result)) {
			    $Linectr++;
			    if($_POST['DateType'] == 'Order') {
					printf('%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					$MyRow['orderno'],
					$MyRow['stkcode'],
					ConvertSQLDate($MyRow['orddate']),
					$MyRow['debtorno'],
					$MyRow['name'],
					$MyRow['brname'],
					locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['extcost'],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($MyRow['extprice'],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($MyRow['qtyinvoiced'],$MyRow['decimalplaces']),
					$MyRow['linestatus'],
					ConvertSQLDate($MyRow['itemdue']),
					$MyRow['salesman'],
					$MyRow['area'],
					$MyRow['description']);
					print '<br/>';
					$TotalQty += $MyRow['quantity'];
				} else {
				    // Detail for Invoiced Date
					printf('%10s | %14s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %12s | %-10s | %-10s | %-10s | %-40s ',
					$MyRow['orderno'],
					$MyRow['transno'],
					$MyRow['stkcode'],
					ConvertSQLDate($MyRow['orddate']),
					$MyRow['debtorno'],
					$MyRow['name'],
					$MyRow['brname'],
					locale_number_format($MyRow['qty'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['extcost'],$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($MyRow['extprice'],$_SESSION['CompanyRecord']['decimalplaces']),
					$MyRow['linestatus'],
					ConvertSQLDate($MyRow['trandate']),
					$MyRow['salesman'],
					$MyRow['area'],
					$MyRow['description']);
					print '<br/>';
					$TotalQty += $MyRow['qty'];
				}
				$LastDecimalPlaces = $MyRow['decimalplaces'];
				$TotalExtCost += $MyRow['extcost'];
				$TotalExtPrice += $MyRow['extprice'];
				$TotalInvQty += $MyRow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
			if($_POST['DateType'] == 'Order') {
					printf('%10s | %-20s | %10s | %-10s | %-30s | %-30s | %12s | %14s | %14s | %14s | %12s | %-10s | %-40s ',
					__('Totals'),
					__('Lines - ') . $Linectr,
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
					__('Totals'),
					__('Lines - ') . $Linectr,
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
			$ColumnHeader7 = ' ';
			// Set up description based on the Summary Type
			if($SummaryType == 'name') {
				$SummaryType = 'name';
				$Description = 'debtorno';
				$SummaryHeader = __('Customer Name');
				$DescriptionHeader =  __('Customer Code');
			}
			if($SummaryType == 'stkcode' OR $SummaryType == 'extprice') {
				$Description = 'Description';
				$SummaryHeader =  __('Stock Code');
				$DescriptionHeader =  __('Item Description');
			}
			if($SummaryType == 'transno') {
				$Description = 'name';
				$SummaryHeader =  __('Transaction Number');
				$DescriptionHeader =  __('Customer Name');
				$ColumnHeader7 =  __('Order Number');
			}
			if($SummaryType == 'debtorno') {
				$Description = 'name';
				$SummaryHeader =  __('Customer Code');
				$DescriptionHeader =  __('Customer Name');
			}
			if($SummaryType == 'orderno') {
				$Description = 'debtorno';
				$SummaryHeader =  __('Order Number');
				$DescriptionHeader =  __('Customer Code');
				$ColumnHeader7 =  __('Customer Name');
			}
			if($SummaryType == 'categoryid') {
				$Description = 'categorydescription';
				$SummaryHeader =  __('Stock Category');
				$DescriptionHeader =  __('Category Description');
			}
			if($SummaryType == 'salesman') {
				$Description = 'salesmanname';
				$SummaryHeader =  __('Salesman Code');
				$DescriptionHeader =  __('Salesman Name');
			}
			if($SummaryType == 'area') {
				$Description = 'areadescription';
				$SummaryHeader =  __('Sales Area');
				$DescriptionHeader =  __('Area Description');
			}
			if($SummaryType == 'month') {
				$Description = 'monthname';
				$SummaryHeader =  __('Month');
				$DescriptionHeader =  __('Month');
			}
			printf('    %-30s | %-40s | %12s | %14s | %14s | %14s | %-15s',
				 __($SummaryHeader),
				 __($DescriptionHeader),
				 __('Quantity'),
				 __('Extended Cost'),
				 __('Extended Price'),
				 __('Invoiced Qty'),
				 __($ColumnHeader7));
				print '<br/><br/>';

				$Column7 = ' ';
				$Linectr = 0;
			while ($MyRow = DB_fetch_array($Result)) {
			    $Linectr++;
				if($SummaryType == 'orderno') {
				    $Column7 = $MyRow['name'];
				}
				if($SummaryType == 'transno') {
				    $Column7 =  $MyRow['orderno'];
				}
				if($_POST['DateType'] == 'Order') {
				    // quantity is from salesorderdetails
				    $DisplayQty = $MyRow['quantity'];
				} else {
				    // qty is from stockmoves
				    $DisplayQty = $MyRow['qty'];
				}
				printf('    %-30s | %-40s | %12s | %14s | %14s | %14s |  %-40s',
				$MyRow[$SummaryType],
				$MyRow[$Description],
				locale_number_format($DisplayQty,2),
				locale_number_format($MyRow['extcost'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($MyRow['extprice'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($MyRow['qtyinvoiced'],2),
				$Column7);

				print '<br/>';
				$TotalQty += $DisplayQty;
				$TotalExtCost += $MyRow['extcost'];
				$TotalExtPrice += $MyRow['extprice'];
				$TotalInvQty += $MyRow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
				printf('    %-30s | %-40s | %12s | %14s | %14s | %14s',
				__('Totals'),
				__('Lines - ') . $Linectr,
				locale_number_format($TotalQty,2),
				locale_number_format($TotalExtCost,$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($TotalExtPrice,$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($TotalInvQty,2)
				);
			echo '</pre>';
		} // End of if($_POST['ReportType']

    } // End of if inputerror != 1
} // End of function submit()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Inquiry Criteria'), '</legend>';

	echo '<field>
			<label for="ReportType">' . __('Report Type') . ':</label>
			<select name="ReportType">
				<option selected="selected" value="Detail">' . __('Detail') . '</option>
				<option value="Summary">' . __('Summary') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="OrderType">' . __('Order Type') . ':</label>
			<select name="OrderType">
				<option selected="selected" value="0">' . __('Sales Order') . '</option>
				<option value="1">' . __('Quotation') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="DateType">' . __('Date Type') . ':</label>
			<select name="DateType">
				<option selected="selected" value="Order">' . __('Order Date') . '</option>
				<option value="Invoice">' . __('Invoice Date') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="InvoiceType">' . __('Invoice Type') . ':</label>
			<select name="InvoiceType">
				<option selected="selected" value="All">' . __('All') . '</option>
				<option value="10">' . __('Sales Invoice') . '</option>
				<option value="11">' . __('Credit Note') . '</option>
			</select>
			<fieldhelp>' . __('Only Applies To Invoice Date Type') . '</fieldhelp>
		</field>';

	echo '<field>
			<label>' . __('Date Range') . ':</label>
			<input type="date" name="FromDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
			' . __('To') . ':
			<input type="date" name="ToDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
		</field>';
	if(!isset($_POST['PartNumber'])) {
		$_POST['PartNumber']='';
	}
	echo '<field>
			<label for="PartNumberOp">' . __('Stock Code') . ':</label>
			<select name="PartNumberOp">
				<option selected="selected" value="Equals">' . __('Equals') . '</option>
				<option value="LIKE">' . __('Begins With') . '</option>
			</select>
			<input type="text" name="PartNumber" size="20" maxlength="20" value="'. $_POST['PartNumber'] . '" />
		</field>';
	if(!isset($_POST['DebtorNo'])) {
		$_POST['DebtorNo']='';
	}
	echo '<field>
			<label for="Equals">' . __('Customer Number') . ':</label>
			<select name="DebtorNoOp">
				<option selected="selected" value="Equals">' . __('Equals') . '</option>
				<option value="LIKE">' . __('Begins With') . '</option>
			</select>
			<input type="text" name="DebtorNo" size="10" maxlength="10" value="' . $_POST['DebtorNo'] . '" />
		</field>';
	if(!isset($_POST['DebtorName'])) {
		$_POST['DebtorName']='';
	}
	echo '<field>
			<label for="DebtorNameOp">' . __('Customer Name') . ':</label>
			<select name="DebtorNameOp">
				<option selected="selected" value="LIKE">' . __('Begins With') . '</option>
				<option value="Equals">' . __('Equals') . '</option>
			</select>
			<input type="text" name="DebtorName" size="30" maxlength="30" value="' . $_POST['DebtorName'] .'" />
		</field>';
	if(!isset($_POST['OrderNo'])) {
		$_POST['OrderNo']='';
	}
	echo '<field>
			<label for="OrderNo">' . __('Order Number') . ':</label>
			<fieldtext>' . __('Equals') . '</fieldtext>
			<input type="text" name="OrderNo" size="10" maxlength="10" value="' . $_POST['OrderNo'] . '" />
		</field>';

	echo '<field>
			<label for="LineStatus">' . __('Line Item Status') . ':</label>
			<select name="LineStatus">
				<option selected="selected" value="All">' . __('All') . '</option>
				<option value="Completed">' . __('Completed') . '</option>
				<option value="Open">' . __('Not Completed') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Category">' . __('Stock Categories') . ':</label>
			<select name="Category">';

	$CategoryResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory");
	echo '<option selected="selected" value="All">' . __('All Categories')  . '</option>';
	while($MyRow = DB_fetch_array($CategoryResult)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription']  . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Salesman">' . __('For Sales Person') . ':</label>';
	if($_SESSION['SalesmanLogin'] != '') {
		echo '<td>';
		echo $_SESSION['UsersRealName'];
		echo '</td>';
	}else{
		echo '<select name="Salesman">';
		$SQL="SELECT salesmancode, salesmanname FROM salesman";
		$SalesmanResult = DB_query($SQL);
		echo '<option selected="selected" value="All">' . __('All Salespeople')  . '</option>';
		while($MyRow = DB_fetch_array($SalesmanResult)) {
			echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname']  . '</option>';
		}
		echo '</select>';
	}
	echo '</field>';

// Use name='Areas[]' multiple - if want to create an array for Areas and allow multiple selections
	echo '<field>
			<label for="Area">' . __('For Sales Areas') . ':</label>
			<select name="Area">';
	$AreasResult = DB_query("SELECT areacode, areadescription FROM areas");
	echo '<option selected="selected" value="All">' . __('All Areas')  . '</option>';
	while($MyRow = DB_fetch_array($AreasResult)) {
		echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription']  . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SortBy">' . __('Sort By') . ':</label>
			<select name="SortBy">
				<option selected="selected" value="salesorderdetails.orderno">' . __('Order Number') . '</option>
				<option value="salesorderdetails.stkcode">' . __('Stock Code') . '</option>
				<option value="debtorsmaster.debtorno,salesorderdetails.orderno">' . __('Customer Number') . '</option>
				<option value="debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno">' . __('Customer Name') . '</option>
				<option value="tempstockmoves.transno,salesorderdetails.stkcode">' . __('Transaction Number') . '</option>
			</select>
			<fieldhelp>' . __('Transaction Number sort only valid for Invoice Date Type') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="SummaryType">' . __('Summary Type') . ':</label>
			<select name="SummaryType">
				<option selected="selected" value="orderno">' . __('Order Number') . '</option>
				<option value="transno">' . __('Transaction Number') . '</option>
				<option value="stkcode">' . __('Stock Code') . '</option>
				<option value="extprice">' . __('Extended Price') . '</option>
				<option value="debtorno">' . __('Customer Code') . '</option>
				<option value="name">' . __('Customer Name') . '</option>
				<option value="month">' . __('Month') . '</option>
				<option value="categoryid">' . __('Stock Category') . '</option>
				<option value="salesman">' . __('Salesman') . '</option>
				<option value="area">' . __('Sales Area') . '</option>
			</select>
			<fieldhelp>' . __('Transaction Number summary only valid for Invoice Date Type') . '</fieldhelp>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . __('Run Inquiry') . '" />
		</div>';
	echo '</form>';

} // End of function display()

function TempStockmoves() {
// When report based on Invoice Date, use stockmoves as the main file, but credit
// notes, which are type 11 in stockmoves, do not have the order number in the
// reference field; instead they have "Ex Inv - " and then the transno from the
// type 10 stockmoves the credit note was applied to. Use this function to load all
// type 10 and 11 stockmoves into a temporary table and then update the
// reference field for type 11 records with the orderno from the type 10 records.

	$FromDate = FormatDateForSQL($_POST['FromDate']);
	$ToDate = FormatDateForSQL($_POST['ToDate']);

	$SQL = "CREATE TEMPORARY TABLE tempstockmoves LIKE stockmoves";
	$ErrMsg = __('The SQL to the create temp stock moves table failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "INSERT tempstockmoves
	          SELECT * FROM stockmoves
	          WHERE (stockmoves.type='10' OR stockmoves.type='11')
	          AND stockmoves.trandate >='" . $FromDate .
			  "' AND stockmoves.trandate <='" . $ToDate . "'";
	$ErrMsg = __('The SQL to insert temporary stockmoves records failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "UPDATE tempstockmoves, stockmoves
	          SET tempstockmoves.reference = stockmoves.reference
	          WHERE tempstockmoves.type='11'
	            AND SUBSTR(tempstockmoves.reference,10,10) = stockmoves.transno
                AND tempstockmoves.stockid = stockmoves.stockid
                AND stockmoves.type ='10'";
	$ErrMsg = __('The SQL to update tempstockmoves failed with the message');
	$Result = DB_query($SQL, $ErrMsg);


} // End of function TempStockmoves

include('includes/footer.php');
