<?php
/* $Revision: 1.11 $ */
Function GetDataSQL($SQLData){

/*This function determines the SQL to use to get the value for the columns defined */
switch ($SQLData) {
Case 'Quantity':
	Return 'salesanalysis.qty';
	break;
Case 'Gross Value':
	Return 'salesanalysis.amt';
	break;
Case 'Net Value':
	Return 'salesanalysis.amt - salesanalysis.disc';
	break;
Case 'Gross Profit':
	Return 'salesanalysis.amt - salesanalysis.disc - salesanalysis.cost';
	break;
Case 'Cost':
	Return 'salesanalysis.cost';
	break;
Case 'Discount':
	Return 'salesanalysis.disc';
	break;
} /*end of switch stmt block*/
}


Function GetFieldSQL($Data, $ColNo){

/*This function determines the two columns to get for the group by levels defined in the report heading
and allocates a Colxx to each  */

Switch ($Data) {
Case 'Sales Area':
	$SQL= 'salesanalysis.area AS col'. $ColNo . ', areas.areadescription AS col' . ($ColNo+1);
	Return $SQL;
	break;
Case 'Product Code':
	$SQL=	'salesanalysis.stockid AS col'. $ColNo . ', stockmaster.description AS col' . ($ColNo+1);
	Return $SQL;
	break;
Case 'Customer Code':
	$SQL=	'salesanalysis.cust AS col'. $ColNo . ', debtorsmaster.name AS col' . ($ColNo+1);
	Return $SQL;
	break;
Case 'Sales Type':
	$SQL=	'salesanalysis.typeabbrev AS col'. $ColNo . ', salestypes.sales_type AS col' . ($ColNo+1);
	Return $SQL;
	break;
Case 'Product Type':
	$SQL=	'salesanalysis.stkcategory AS col' . $ColNo . ', stockcategory.categorydescription AS col' . ($ColNo+1);
	Return $SQL;
	break;
Case 'Customer Branch':
	$SQL=	'salesanalysis.custbranch AS col' . $ColNo . ', custbranch.brname AS col' . ($ColNo+1);
	Return $SQL;
	break;
Case 'Sales Person':
	$SQL=	'salesanalysis.salesperson AS col' . $ColNo . ', salesman.salesmanname AS col' . ($ColNo+1);
	Return $SQL;
	break;
} /* end of switch statement */

}

Function GetHavingSQL($Data){

/*This function determines the field names to search on in the having clause  */

Switch ($Data) {
Case 'Sales Area':
	Return 'salesanalysis.area';
	break;
Case 'Product Code':
	Return 'salesanalysis.stockid';
	break;
Case 'Customer Code':
	Return 'salesanalysis.cust';
	break;
Case 'Sales Type':
	Return 'salesanalysis.typeabbrev';
	break;
Case 'Product Type':
	Return 'salesanalysis.stkcategory';
	break;
Case 'Customer Branch':
	Return 'salesanalysis.custbranch';
	break;
Case 'Sales Person':
	Return 'salesanalysis.salesperson';
	break;
} /* end of switch statement */

}

Function GetGroupBySQL($GByData){

/*This function returns the SQL for the group by clause for the group by levels defined in the report header */

Switch ($GByData) {
Case 'Sales Area':
	Return 'salesanalysis.area, areas.areadescription';
	break;
Case 'Product Code':
	Return 'salesanalysis.stockid, stockmaster.description';
	break;
Case 'Customer Code':
	Return 'salesanalysis.cust, debtorsmaster.name';
	break;
Case 'Sales Type':
	Return 'salesanalysis.typeabbrev, salestypes.sales_type';
	break;
Case 'Product Type':
	Return 'salesanalysis.stkcategory, stockcategory.categorydescription';
	break;
Case 'Customer Branch':
	Return 'salesanalysis.custbranch, custbranch.brname';
	break;
Case 'Sales Person':
	Return 'salesanalysis.salesperson, salesman.salesmanname';
	break;
} /* end of switch statement */
}

/*First construct the necessary SQL statement to send to the server
using the case construct to emulate cross tabs */

if (isset($ReportID)){
/* then use it - this is required from MailSalesReport scripts where the ReportID to run is hard coded */
	$_GET['ReportID']==$ReportID;
}

$GetReportSpecSQL='SELECT reportheading,
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
			WHERE reportid=' . $_GET['ReportID'];

$SpecResult= DB_query($GetReportSpecSQL,$db);
$ReportSpec = DB_fetch_array($SpecResult);

$GetColsSQL = 'SELECT colno,
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
		WHERE reportid=' . $_GET['ReportID'];

$ColsResult = DB_query($GetColsSQL,$db);

If (DB_num_rows($ColsResult)== 0) {
    $title = _('User Defined Sales Analysis Problem') . ' ....';
   include('includes/header.inc');
    prnMsg (  _('The report does not have any output columns') . '. ' . _('You need to set up the data columns that you wish to show in the report'),'error',_('No Columns'));
    echo "<BR><A HREF='$rootpath/SalesAnalReptCols.php?" . SID . "&ReportID=" . $_GET['ReportID'] . "'>" . _('Enter Columns for this report') . '</A>';
    echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
    include('includes/footer.inc');
    Exit;
} elseif (DB_num_rows($ColsResult) >10){
    $title = _('User Defined Sales Analysis Problem') . ' ....';
   include('includes/header.inc');
    prnMsg (_('The report cannot have more than 10 columns in it') . '. ' . _('Please delete one or more columns before attempting to run it'),'error',_('Too Many Columns'));
    echo "<BR><A HREF='$rootpath/SalesAnalReptCols.php?" . SID . "&ReportID=" . $_GET['ReportID'] . "'>" . _('Maintain Columns for this report') . '</A>';
    echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
    include('includes/footer.inc');
    exit;
}


$SQLFromCls = ' FROM ((((((salesanalysis LEFT JOIN salestypes ON salesanalysis.typeabbrev = salestypes.typeabbrev) LEFT JOIN stockmaster ON salesanalysis.stockid = stockmaster.stockid) LEFT JOIN areas ON salesanalysis.area = areas.areacode) LEFT JOIN debtorsmaster ON salesanalysis.cust = debtorsmaster.debtorno) LEFT JOIN custbranch ON (salesanalysis.custbranch = custbranch.branchcode AND salesanalysis.cust=custbranch.debtorno)) LEFT JOIN stockcategory ON salesanalysis.stkcategory = stockcategory.categoryid) LEFT JOIN salesman ON salesanalysis.salesperson = salesman.salesmancode ';
$SQLSelectCls = 'SELECT ';
$SQLGroupCls = 'GROUP BY ';

$SQLWhereCls = 'WHERE ';

$SQLSelectCls = $SQLSelectCls . GetFieldSQL($ReportSpec['groupbydata1'],1);
$SQLWhereCls = $SQLWhereCls . GetHavingSQL($ReportSpec['groupbydata1']) . " >= '" . $ReportSpec['lower1'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata1']) . " <= '" . $ReportSpec['upper1'] . "'";

$SQLGroupCls = $SQLGroupCls . GetGroupBySQL($ReportSpec['groupbydata1']);

If ($ReportSpec['groupbydata2'] != 'Not Used') {
     $SQLSelectCls = $SQLSelectCls . ', ' . GetFieldSQL($ReportSpec['groupbydata2'],3);
     
     $SQLWhereCls = $SQLWhereCls . " AND " . GetHavingSQL($ReportSpec['groupbydata2']) . " >= '" . $ReportSpec['lower2'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata2']) . " <= '" . $ReportSpec['upper2'] . "'";
     

     $SQLGroupCls = $SQLGroupCls . ', ' . GetGroupBySQL($ReportSpec['groupbydata2']);
} else {
	$SQLSelectCls = $SQLSelectCls . ', 0 AS col3, 0 AS col4';
 	$ReportSpec['groupbydata3'] = 'Not Used'; /*This is forced if no entry in Group By 2 */
}

If ($ReportSpec['groupbydata3'] != 'Not Used') {
	 $SQLSelectCls = $SQLSelectCls . ', ' . GetFieldSQL($ReportSpec['groupbydata3'],5);
	 
	$SQLWhereCls = $SQLWhereCls . " AND " . GetHavingSQL($ReportSpec['groupbydata3']) . " >= '" . $ReportSpec['lower3'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata3']) . " <= '" . $ReportSpec['upper3'] . "'";
		 
	 $SQLGroupCls = $SQLGroupCls . ', ' . GetGroupBySQL($ReportSpec['groupbydata3']);
} else {
    	 $ReportSpec['groupbydata4'] = 'Not Used'; /*This is forced if no entry in Group By 3 */
	 $SQLSelectCls = $SQLSelectCls . ', 0 AS col5, 0 AS col6';
}

If ($ReportSpec['groupbydata4'] != 'Not Used') {
	 $SQLSelectCls = $SQLSelectCls . ', ' . GetFieldSQL($ReportSpec['groupbydata4'],7);
	$SQLWhereCls = $SQLWhereCls . " AND " . GetHavingSQL($ReportSpec['groupbydata4']) . " >= '" . $ReportSpec['lower4'] . "' AND " . GetHavingSQL($ReportSpec['groupbydata4']) . " <= '" . $ReportSpec['upper4'] . "'";
	
	 $SQLGroupCls = $SQLGroupCls . ', ' . GetGroupBySQL($ReportSpec['groupbydata4']);
} else {
	 $SQLSelectCls = $SQLSelectCls . ', 0 AS col7, 0 AS col8';
}

/*Right, now run thro the cols and build the select clause from the defined cols */

while ($Cols = DB_fetch_array($ColsResult)){
    If ($Cols['calculation']==0){
	 $SQLSelectCls = $SQLSelectCls . ', SUM(CASE WHEN salesanalysis.periodno >= ' . $Cols['periodfrom'] . ' AND salesanalysis.periodno <= ' . $Cols['periodto'];
	 $SQLSelectCls = $SQLSelectCls . ' AND salesanalysis.budgetoractual = ' . $Cols['budgetoractual'] . ' THEN ' . GetDataSQL($Cols['datatype']) . ' ELSE 0 END) AS col' . ($Cols['colno'] + 8);
    }
}

/* Now go through the cols again and do the SQL for the calculations - need the
Select clause to have all the non-calc fields in it before start using the calcs */

/*Set the ColsResult back at the start */
DB_data_seek($ColsResult,0);

while ($Cols = DB_fetch_array($ColsResult)){
    If ($Cols['calculation']==1){

	/*find the end of the col select clause AS Col# start is 8 because no need to search the SELECT
	First find out the position in the select statement where 'AS ColX' is
	The first 6 Columns are defined by the group by fields so for eg the first col
	defined will be col 7 and so on - thats why need to add 6 to the col defined as */

	$length_ColNum = strpos($SQLSelectCls, 'AS col' . ($Cols['colnumerator'] + 8) , 7);


	 If ($length_ColNum == 0) {

	     $title = _('User Defined Sales Analysis Problem') . ' ....';
	    include('includes/header.inc');
	     prnMsg(_('Calculated fields must use columns defined in the report specification') . '. ' . _('The numerator column number entered for this calculation is not defined in the report'),'error',_('Calculation With Undefined Column'));
	     echo "<BR><A HREF='$rootpath/SalesAnalReptCols.php?" . SID . "&ReportID=" . $_GET['ReportID'] . "'>" . _('Maintain Columns for this report') . '</A>';
	     include('includes/footer.inc');
	     exit;
	 }
	 $strt_ColNum = 9; /* Start searching after SELECT */

	/*find the comma just before the Select Cls statement for the numerator column */

	do {
	     $strt_ColNum = strpos( $SQLSelectCls, ',', $strt_ColNum + 1) + 1;

	} while (strpos($SQLSelectCls, ',', $strt_ColNum) < $length_ColNum && strpos($SQLSelectCls, ',' , $strt_ColNum)!=0);


/*The length of the element in the select clause defining the column will be from the comma to the
'AS ColX' bit found above */

	 $length_ColNum = $length_ColNum - $strt_ColNum - 1;

	if (!($Cols['calcoperator']=='C' OR $Cols['calcoperator']=='*')){

	/*The denominator column is also required if the constant is not used so do the same again for the denominator */

	$length_ColDen = strpos($SQLSelectCls, 'AS col' . (($Cols['coldenominator']) + 8), 7);
	 If ($length_ColDen == 0){
	     prnMsg (_('Calculated fields must use columns defined in the report specification') . '. ' . _('The denominator column number entered for this calculation is not defined in the report'),'error',_('Calculation With Undefined Denominator'));
	     Exit;
	}

	 $strt_ColDen = 7; /* start searching after SELECT */

	/*find the comma just before the Select Cls statement for the denominator column */

	do {
	     $strt_ColDen = strpos( $SQLSelectCls, ',', $strt_ColDen +1)+1;

	} while (strpos($SQLSelectCls, ',', $strt_ColDen) < $length_ColDen && strpos($SQLSelectCls, ',' , $strt_ColDen)!=0);

	 $length_ColDen = $length_ColDen - $strt_ColDen - 1;

	 $SQLSelectCls = $SQLSelectCls . ', ' . substr($SQLSelectCls, $strt_ColNum, $length_ColNum) . $Cols['calcoperator'] . substr($SQLSelectCls, $strt_ColDen, $length_ColDen) . ' AS col' . ($Cols['colno'] + 8);

	} elseif ($Cols['calcoperator']=='C') {  /* its a calculation divided by Constant */

		$SQLSelectCls = $SQLSelectCls . ', ' . substr($SQLSelectCls, $strt_ColNum, $length_ColNum) . '/' . $Cols['constant'] . ' AS col' . ($Cols['colno'] + 8);

	} elseif ($Cols['calcoperator']=='*') {  /* its a calculation multiplied by constant */
		$SQLSelectCls = $SQLSelectCls . ', ' . substr($SQLSelectCls, $strt_ColNum, $length_ColNum) . '*' . $Cols['constant'] . ' AS col' . ($Cols['colno'] + 8);

	}

    } /*end if its a calculation */

} /* end of loop through defined columns */

$SQLTheLot =	$SQLSelectCls . ' ' . $SQLFromCls . ' ' . $SQLWhereCls . ' ' . $SQLGroupCls ;

/*For the purposes of debugging */
/*echo '<P>'. $SQLTheLot;
exit;
*/

/*Now let her go .... */
$ErrMsg = _('There was a problem running the SQL to retreive the sales analysis information');
$DbgMsg = _('The SQL that was used to retrieve the user defined sales analysis info was');
$result=DB_query($SQLTheLot, $db,$ErrMsg,$DbgMsg);

if (DB_num_rows($result)==0){
    $title = _('User Defined Sales Analysis Problem') . ' ....';
   include('includes/header.inc');
    prnMsg(_('The user defined sales analysis SQL did not return any rows') . ' - ' . _('have another look at the criteria specified'),'error',_('Nothing To Report'));
    echo "<BR><A HREF='$rootpath/SalesAnalRepts.php?" . SID . '&SelectedReport=' . $_GET['ReportID'] . "'>" . _('Look at the design of this report') . '</A>';
    echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
    include('includes/footer.inc');

   exit;
}

?>