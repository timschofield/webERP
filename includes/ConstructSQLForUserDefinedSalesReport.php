<?php

/** This function determines the SQL to use to get the value for the columns defined */
function GetDataSQL($SQLData) {

	switch ($SQLData) {
		case 'Quantity':
			return 'salesanalysis.qty';
		case 'Gross Value':
			return 'salesanalysis.amt';
		case 'Net Value':
			return 'salesanalysis.amt - salesanalysis.disc';
		case 'Gross Profit':
			return 'salesanalysis.amt - salesanalysis.disc - salesanalysis.cost';
		case 'Cost':
			return 'salesanalysis.cost';
		case 'Discount':
			return 'salesanalysis.disc';
	}
}

/** This function determines the two columns to get for the group by levels defined in the report heading
and allocates a Colxx to each  */
function GetFieldSQL($Data, $ColNo) {

	switch ($Data) {
		case 'Sales Area':
			$SQL = 'salesanalysis.area AS col'. $ColNo . ', areas.areadescription AS col' . ($ColNo+1);
			return $SQL;
		case 'Product Code':
			$SQL = 'salesanalysis.stockid AS col'. $ColNo . ', stockmaster.description AS col' . ($ColNo+1);
			return $SQL;
		case 'Customer Code':
			$SQL = 'salesanalysis.cust AS col'. $ColNo . ', debtorsmaster.name AS col' . ($ColNo+1);
			return $SQL;
		case 'Sales Type':
			$SQL = 'salesanalysis.typeabbrev AS col'. $ColNo . ', salestypes.sales_type AS col' . ($ColNo+1);
			return $SQL;
		case 'Product Type':
			$SQL = 'salesanalysis.stkcategory AS col' . $ColNo . ', stockcategory.categorydescription AS col' . ($ColNo+1);
			return $SQL;
		case 'Customer Branch':
			$SQL = 'salesanalysis.custbranch AS col' . $ColNo . ', custbranch.brname AS col' . ($ColNo+1);
			return $SQL;
		case 'Sales Person':
			$SQL = 'salesanalysis.salesperson AS col' . $ColNo . ', salesman.salesmanname AS col' . ($ColNo+1);
			return $SQL;
	}
}

/** This function determines the field names to search on in the having clause */
function GetHavingSQL($Data) {

	switch ($Data) {
		case 'Sales Area':
			return 'salesanalysis.area';
		case 'Product Code':
			return 'salesanalysis.stockid';
		case 'Customer Code':
			return 'salesanalysis.cust';
		case 'Sales Type':
			return 'salesanalysis.typeabbrev';
		case 'Product Type':
			return 'salesanalysis.stkcategory';
		case 'Customer Branch':
			return 'salesanalysis.custbranch';
		case 'Sales Person':
			return 'salesanalysis.salesperson';
	}
}

/** This function returns the SQL for the group by clause for the group by levels defined in the report header */
function GetGroupBySQL($GByData) {

	switch ($GByData) {
		case 'Sales Area':
			return 'salesanalysis.area, areas.areadescription';
		case 'Product Code':
			return 'salesanalysis.stockid, stockmaster.description';
		case 'Customer Code':
			return 'salesanalysis.cust, debtorsmaster.name';
		case 'Sales Type':
			return 'salesanalysis.typeabbrev, salestypes.sales_type';
		case 'Product Type':
			return 'salesanalysis.stkcategory, stockcategory.categorydescription';
		case 'Customer Branch':
			return 'salesanalysis.custbranch, custbranch.brname';
		case 'Sales Person':
			return 'salesanalysis.salesperson, salesman.salesmanname';
	}
}

/* First construct the necessary SQL statement to send to the server using the case construct to emulate cross tabs */

if (isset($ReportID)) {
	/* then use it - this is required from MailSalesReport scripts where the ReportID to run is hard coded */
	$_GET['ReportID'] == $ReportID;
}

$GetReportSpecSQL="SELECT reportheading,
				groupbydata1,
				newpageafter1,
				lower1,
				upper1,
				groupbydata2,
				newpageafter2,
				lower2,
				upper2,
				groupbydata3,
				newpageafter3,
				lower3,
				upper3,
				groupbydata4,
				newpageafter4,
				lower4,
				upper4
			FROM reportheaders
			WHERE reportid='" . $_GET['ReportID'] . "'";

$SpecResult = DB_query($GetReportSpecSQL);
$ReportSpec = DB_fetch_array($SpecResult);

$GetColsSQL = "SELECT colno,
			heading1,
			heading2,
			calculation,
			periodfrom,
			periodto,
			datatype,
			colnumerator,
			coldenominator,
			calcoperator,
			constant,
			budgetoractual,
			valformat
		FROM reportcolumns
		WHERE reportid='" . $_GET['ReportID'] . "'";

$ColsResult = DB_query($GetColsSQL);

if (DB_num_rows($ColsResult)== 0) {
	$Title = __('User Defined Sales Analysis Problem') . ' ....';
	include('includes/header.php');
	prnMsg(  __('The report does not have any output columns') . '. ' . __('You need to set up the data columns that you wish to show in the report'),'error',__('No Columns'));
	echo '<br /><a href="' . $RootPath . '/SalesAnalReptCols.php?ReportID=' . $_GET['ReportID'] . '">' . __('Enter Columns for this report') . '</a>';
	echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit();
} elseif (DB_num_rows($ColsResult) >10){
	$Title = __('User Defined Sales Analysis Problem') . ' ....';
	include('includes/header.php');
	prnMsg(__('The report cannot have more than 10 columns in it') . '. ' . __('Please delete one or more columns before attempting to run it'),'error',__('Too Many Columns'));
	echo '<br /><a href="' . $RootPath . '/SalesAnalReptCols.php?ReportID=' . $_GET['ReportID'] . '">' . __('Maintain Columns for this report') . '</a>';
	echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
	exit();
}

$SQLFromCls = " FROM ((((((salesanalysis LEFT JOIN salestypes ON salesanalysis.typeabbrev = salestypes.typeabbrev) LEFT JOIN stockmaster ON salesanalysis.stockid = stockmaster.stockid) LEFT JOIN areas ON salesanalysis.area = areas.areacode) LEFT JOIN debtorsmaster ON salesanalysis.cust = debtorsmaster.debtorno) LEFT JOIN custbranch ON (salesanalysis.custbranch = custbranch.branchcode AND salesanalysis.cust=custbranch.debtorno)) LEFT JOIN stockcategory ON salesanalysis.stkcategory = stockcategory.categoryid) LEFT JOIN salesman ON salesanalysis.salesperson = salesman.salesmancode ";
$SQLSelectCls = 'SELECT ';
$SQLGroupCls = 'GROUP BY ';

$SQLWhereCls = 'WHERE ';

$SQLSelectCls = $SQLSelectCls . GetFieldSQL($ReportSpec['groupbydata1'],1);
$SQLWhereCls = $SQLWhereCls . GetHavingSQL($ReportSpec['groupbydata1']) . " >= '" . $ReportSpec['lower1'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata1']) . " <= '" . $ReportSpec['upper1'] . "'";

$SQLGroupCls = $SQLGroupCls . GetGroupBySQL($ReportSpec['groupbydata1']);

if ($ReportSpec['groupbydata2'] != 'Not Used') {
	$SQLSelectCls = $SQLSelectCls . ', ' . GetFieldSQL($ReportSpec['groupbydata2'],3);

	$SQLWhereCls = $SQLWhereCls . " AND " . GetHavingSQL($ReportSpec['groupbydata2']) . " >= '" . $ReportSpec['lower2'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata2']) . " <= '" . $ReportSpec['upper2'] . "'";

	$SQLGroupCls = $SQLGroupCls . ', ' . GetGroupBySQL($ReportSpec['groupbydata2']);
} else {
	$SQLSelectCls = $SQLSelectCls . ', 0 AS col3, 0 AS col4';
	$ReportSpec['groupbydata3'] = 'Not Used'; /*This is forced if no entry in Group By 2 */
}

if ($ReportSpec['groupbydata3'] != 'Not Used') {
	$SQLSelectCls = $SQLSelectCls . ', ' . GetFieldSQL($ReportSpec['groupbydata3'],5);

	$SQLWhereCls = $SQLWhereCls . " AND " . GetHavingSQL($ReportSpec['groupbydata3']) . " >= '" . $ReportSpec['lower3'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata3']) . " <= '" . $ReportSpec['upper3'] . "'";

	$SQLGroupCls = $SQLGroupCls . ', ' . GetGroupBySQL($ReportSpec['groupbydata3']);
} else {
	$ReportSpec['groupbydata4'] = 'Not Used'; /*This is forced if no entry in Group By 3 */
	$SQLSelectCls = $SQLSelectCls . ', 0 AS col5, 0 AS col6';
}

if ($ReportSpec['groupbydata4'] != 'Not Used') {
	$SQLSelectCls = $SQLSelectCls . ', ' . GetFieldSQL($ReportSpec['groupbydata4'],7);
	$SQLWhereCls = $SQLWhereCls . " AND " . GetHavingSQL($ReportSpec['groupbydata4']) . " >= '" . $ReportSpec['lower4'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata4']) . " <= '" . $ReportSpec['upper4'] . "'";

	$SQLGroupCls = $SQLGroupCls . ', ' . GetGroupBySQL($ReportSpec['groupbydata4']);
} else {
	$SQLSelectCls = $SQLSelectCls . ', 0 AS col7, 0 AS col8';
}

/* Right, now run thru the cols and build the select clause from the defined cols */

while ($Cols = DB_fetch_array($ColsResult)) {
	if ($Cols['calculation']==0) {
		$SQLSelectCls = $SQLSelectCls . ', SUM(CASE WHEN salesanalysis.periodno >= ' . $Cols['periodfrom'] . ' AND salesanalysis.periodno <= ' . $Cols['periodto'];
		$SQLSelectCls = $SQLSelectCls . ' AND salesanalysis.budgetoractual = ' . $Cols['budgetoractual'] . ' THEN ' . GetDataSQL($Cols['datatype']) . ' ELSE 0 END) AS col' . ($Cols['colno'] + 8);
	}
}

/* Now go through the cols again and do the SQL for the calculations - need the
Select clause to have all the non-calc fields in it before start using the calcs */

/*Set the ColsResult back at the start */
DB_data_seek($ColsResult,0);

while ($Cols = DB_fetch_array($ColsResult)){
	if ($Cols['calculation']==1){

	/*find the end of the col select clause AS Col# start is 8 because no need to search the SELECT
	First find out the position in the select statement where 'AS ColX' is
	The first 6 Columns are defined by the group by fields so for eg the first col
	defined will be col 7 and so on - thats why need to add 6 to the col defined as */

	$Length_ColNum = mb_strpos($SQLSelectCls, 'AS col' . ($Cols['colnumerator'] + 8) , 7);

	if ($Length_ColNum == 0) {

		 $Title = __('User Defined Sales Analysis Problem') . ' ....';
		include('includes/header.php');
		prnMsg(__('Calculated fields must use columns defined in the report specification') . '. ' . __('The numerator column number entered for this calculation is not defined in the report'),'error',__('Calculation With Undefined Column'));
		echo '<br /><a href="' . $RootPath . '/SalesAnalReptCols.php?ReportID=' . $_GET['ReportID'] . '">' . __('Maintain Columns for this report') . '</a>';
		include('includes/footer.php');
		exit();
	}
	$strt_ColNum = 9; /* Start searching after SELECT */

	/*find the comma just before the Select Cls statement for the numerator column */

	do {
		$strt_ColNum = mb_strpos( $SQLSelectCls, ',', $strt_ColNum + 1) + 1;

	} while (mb_strpos($SQLSelectCls, ',', $strt_ColNum) < $Length_ColNum && mb_strpos($SQLSelectCls, ',' , $strt_ColNum)!=0);

/*The length of the element in the select clause defining the column will be from the comma to the
'AS ColX' bit found above */

	$Length_ColNum = $Length_ColNum - $strt_ColNum - 1;

	if (!($Cols['calcoperator']=='C' OR $Cols['calcoperator']=='*')){

		/*The denominator column is also required if the constant is not used so do the same again for the denominator */

		$Length_ColDen = mb_strpos($SQLSelectCls, 'AS col' . (($Cols['coldenominator']) + 8), 7);
		if ($Length_ColDen == 0){
			prnMsg(__('Calculated fields must use columns defined in the report specification') . '. ' . __('The denominator column number entered for this calculation is not defined in the report'),'error',__('Calculation With Undefined Denominator'));
			exit();
		}

	 	$strt_ColDen = 7; /* start searching after SELECT */

		/* find the comma just before the Select Cls statement for the denominator column */

		do {
			 $strt_ColDen = mb_strpos( $SQLSelectCls, ',', $strt_ColDen +1)+1;

		} while (mb_strpos($SQLSelectCls, ',', $strt_ColDen) < $Length_ColDen && mb_strpos($SQLSelectCls, ',' , $strt_ColDen)!=0);

		$Length_ColDen = $Length_ColDen - $strt_ColDen - 1;

		$SQLSelectCls = $SQLSelectCls . ', ' . mb_substr($SQLSelectCls, $strt_ColNum, $Length_ColNum) . $Cols['calcoperator'] . mb_substr($SQLSelectCls, $strt_ColDen, $Length_ColDen) . ' AS col' . ($Cols['colno'] + 8);

		} elseif ($Cols['calcoperator']=='C') {  /* its a calculation divided by Constant */

			$SQLSelectCls = $SQLSelectCls . ', ' . mb_substr($SQLSelectCls, $strt_ColNum, $Length_ColNum) . '/' . $Cols['constant'] . ' AS col' . ($Cols['colno'] + 8);

		} elseif ($Cols['calcoperator']=='*') {  /* its a calculation multiplied by constant */
			$SQLSelectCls = $SQLSelectCls . ', ' . mb_substr($SQLSelectCls, $strt_ColNum, $Length_ColNum) . '*' . $Cols['constant'] . ' AS col' . ($Cols['colno'] + 8);
		}

	} /*end if its a calculation */

} /* end of loop through defined columns */

if ($_SESSION['SalesmanLogin'] != '') {
	$SQLWhereCls .= " AND salesanalysis.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}
$SQLTheLot =	$SQLSelectCls . ' ' . $SQLFromCls . ' ' . $SQLWhereCls . ' ' . $SQLGroupCls ;

/*For the purposes of debugging */
/*echo '<P>' .  $SQLTheLot;
exit();
*/

/* Now let her go .... */
$ErrMsg = __('There was a problem running the SQL to retrieve the sales analysis information');
$Result = DB_query($SQLTheLot, $ErrMsg);

if (DB_num_rows($Result)==0) {
	$Title = __('User Defined Sales Analysis Problem') . ' ....';
	include('includes/header.php');
	prnMsg(__('The user defined sales analysis SQL did not return any rows') . ' - ' . __('have another look at the criteria specified'),'error',__('Nothing To Report'));
	echo '<br /><a href="' . $RootPath . '/SalesAnalRepts.php?SelectedReport=' . $_GET['ReportID'] . '">' . __('Look at the design of this report') . '</a>';
	echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');

	exit();
}
