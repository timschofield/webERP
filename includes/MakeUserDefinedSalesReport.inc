<?php

Function GetDataSQL($SQLData){

/*This function determines the SQL to use to get the value for the columns defined */
switch ($SQLData) {
Case "Quantity":
	Return "SalesAnalysis.Qty";
	break;
Case "Gross Value":
	Return "SalesAnalysis.Amt";
	break;
Case "Net Value":
	Return "SalesAnalysis.Amt - SalesAnalysis.Disc";
	break;
Case "Gross Profit":
	Return "SalesAnalysis.Amt - SalesAnalysis.Disc - SalesAnalysis.Cost";
	break;
Case "Cost":
	Return "SalesAnalysis.Cost";
	break;
Case "Discount":
	Return "SalesAnalysis.Disc";
	break;
} /*end of switch stmt block*/
}


Function GetFieldSQL($Data, $ColNo){

/*This function determines the two columns to get for the group by levels defined in the report heading
and allocates a Colxx to each  */

Switch ($Data) {
Case "Sales Area":
	$SQL= "SalesAnalysis.Area AS Col". $ColNo . ", Areas.AreaDescription AS Col" . ($ColNo+1);
	Return $SQL;
	break;
Case "Product Code":
	$SQL=	"SalesAnalysis.StockID AS Col". $ColNo . ", StockMaster.Description AS Col" . ($ColNo+1);
	Return $SQL;
	break;
Case "Customer Code":
	$SQL=	"SalesAnalysis.Cust AS Col". $ColNo . ", DebtorsMaster.Name AS Col" . ($ColNo+1);
	Return $SQL;
	break;
Case "Sales Type":
	$SQL=	"SalesAnalysis.TypeAbbrev AS Col". $ColNo . ", SalesTypes.Sales_Type AS Col" . ($ColNo+1);
	Return $SQL;
	break;
Case "Product Type":
	$SQL=	"SalesAnalysis.StkCategory AS Col" . $ColNo . ", StockCategory.CategoryDescription AS Col" . ($ColNo+1);
	Return $SQL;
	break;
Case "Customer Branch":
	$SQL=	"SalesAnalysis.CustBranch AS Col" . $ColNo . ", CustBranch.BrName AS Col" . ($ColNo+1);
	Return $SQL;
	break;
Case "Sales Person":
	$SQL=	"SalesAnalysis.Salesperson AS Col" . $ColNo . ", Salesman.SalesmanName AS Col" . ($ColNo+1);
	Return $SQL;
	break;
} /* end of switch statement */

}

Function GetGroupBySQL($GByData){

/*This function returns the SQL for the group by clause for the group by levels defined in the report header */

Switch ($GByData) {
Case "Sales Area":
	Return "SalesAnalysis.Area, Areas.AreaDescription";
	break;
Case "Product Code":
	Return "SalesAnalysis.StockID, StockMaster.Description";
	break;
Case "Customer Code":
	Return "SalesAnalysis.Cust, DebtorsMaster.Name";
	break;
Case "Sales Type":
	Return "SalesAnalysis.TypeAbbrev, SalesTypes.Sales_Type";
	break;
Case "Product Type":
	Return "SalesAnalysis.StkCategory, StockCategory.CategoryDescription";
	break;
Case "Customer Branch":
	Return "SalesAnalysis.CustBranch, CustBranch.BrName";
	break;
Case "Sales Person":
	Return "SalesAnalysis.Salesperson, Salesman.SalesmanName";
	break;
} /* end of switch statement */
}

include("config.php");
include("includes/ConnectDB.inc");

if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

session_start();

/*First construct the necessary SQL statement to send to the server
using the case construct to emulate cross tabs */

$GetReportSpecSQL="SELECT ReportHeading, GroupByData1, NewPageAfter1, Lower1, Upper1, GroupByData2, NewPageAfter2, Lower2, Upper2, GroupByData3, NewPageAfter3, Lower3, Upper3, GroupByData4, NewPageAfter4, Lower4, Upper4 FROM ReportHeaders WHERE ReportID=" . $_GET['ReportID'];
$SpecResult= DB_query($GetReportSpecSQL,$db);
$ReportSpec = DB_fetch_array($SpecResult);

$GetColsSQL = "SELECT ColNo, Heading1, Heading2, Calculation, PeriodFrom, PeriodTo, DataType, ColNumerator,ColDenominator, CalcOperator, Constant, BudgetOrActual, ValFormat FROM ReportColumns WHERE ReportID=" . $_GET['ReportID'];
$ColsResult = DB_query($GetColsSQL,$db);

If (DB_num_rows($ColsResult)== 0) {
    $title = "User Defined Sales Analysis Problem .... ";
   include("includes/header.inc");
    echo "<P>The report does not have any output columns. You need to set up the data columns that you wish to show in the report.";
    echo "<BR><A HREF='$rootpath/SalesAnalReptCols.php?" . SID . "ReportID=" . $_GET['ReportID'] . "'>Enter Columns for this report</A>";
    echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
    include("includes/footer.inc");
    Exit;
} elseif (DB_num_rows($ColsResult) >10){
    $title = "User Defined Sales Analysis Problem .... ";
   include("includes/header.inc");
    echo "<P>The report cannot have more than 10 columns in it. Please delete one or more columns before attempting to run it.";
    echo "<BR><A HREF='$rootpath/SalesAnalReptCols.php?" . SID . "ReportID=" . $_GET['ReportID'] . "'>Maintain Columns for this report</A>";
    echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
    include("includes/footer.inc");
    exit;
}


$SQLFromCls = " FROM ((((((SalesAnalysis LEFT JOIN SalesTypes ON SalesAnalysis.TypeAbbrev = SalesTypes.TypeAbbrev) LEFT JOIN StockMaster ON SalesAnalysis.StockID = StockMaster.StockID) LEFT JOIN Areas ON SalesAnalysis.Area = Areas.AreaCode) LEFT JOIN DebtorsMaster ON SalesAnalysis.Cust = DebtorsMaster.DebtorNo) LEFT JOIN CustBranch ON (SalesAnalysis.CustBranch = CustBranch.BranchCode)) LEFT JOIN StockCategory ON SalesAnalysis.StkCategory = StockCategory.CategoryID) LEFT JOIN Salesman ON SalesAnalysis.Salesperson = Salesman.SalesmanCode ";
$SQLSelectCls = "SELECT ";
$SQLGroupCls = "GROUP BY ";
$SQLHavingCls = "HAVING ";
$SQLWhereCls = "WHERE ";

$SQLSelectCls = $SQLSelectCls . GetFieldSQL($ReportSpec["GroupByData1"],1);
$SQLHavingCls = $SQLHavingCls . "Col1 >= '" . $ReportSpec["Lower1"] . "' AND Col1 <= '" . $ReportSpec["Upper1"] . "'";
/*$SQLHavingCls = $SQLHavingCls . GetFieldInfo($ReportSpec["GroupByData1"]) . " >= '" . $ReportSpec["Lower1"] . "' AND " . GetFieldInfo($ReportSpec["GroupByData1"]) . " <= '" . $ReportSpec["Upper1"] . "'"; */
$SQLGroupCls = $SQLGroupCls . GetGroupBySQL($ReportSpec["GroupByData1"]);

If ($ReportSpec["GroupByData2"] != "Not Used") {
     $SQLSelectCls = $SQLSelectCls . ", " . GetFieldSQL($ReportSpec["GroupByData2"],3);
     $SQLHavingCls = $SQLHavingCls . " AND Col3 >= '" . $ReportSpec["Lower2"] . "' AND Col3 <= '" . $ReportSpec["Upper2"] . "'";
     $SQLGroupCls = $SQLGroupCls . ", " . GetGroupBySQL($ReportSpec["GroupByData2"]);
} else {
	$SQLGroupCls = $SQLGroupCls . ", 0 AS Col3, 0 As Col4";
 	$ReportSpec["GroupByData3"] = "Not Used"; /*This is forced if no entry in Group By 2 */
}

If ($ReportSpec["GroupByData3"] != "Not Used") {
	 $SQLSelectCls = $SQLSelectCls . ", " . GetFieldSQL($ReportSpec["GroupByData3"],5);
	 $SQLHavingCls = $SQLHavingCls . " AND Col5 >= '" . $ReportSpec["Lower3"] . "' AND Col5 <= '" . $ReportSpec["Upper3"] . "'";
	 $SQLGroupCls = $SQLGroupCls . ", " . GetGroupBySQL($ReportSpec["GroupByData3"]);
} else {
    	 $ReportSpec["GroupByData4"] = "Not Used"; /*This is forced if no entry in Group By 3 */
	 $SQLSelectCls = $SQLSelectCls . ", 0 AS Col5, 0 AS Col6";
}

If ($ReportSpec["GroupByData4"] != "Not Used") {
	 $SQLSelectCls = $SQLSelectCls . ", " . GetFieldSQL($ReportSpec["GroupByData4"],7);
	 $SQLHavingCls = $SQLHavingCls . " AND Col7 >= '" . $ReportSpec["Lower4"] . "' AND Col7 <= '" . $ReportSpec["Upper4"] . "'";
	 $SQLGroupCls = $SQLGroupCls . ", " . GetGroupBySQL($ReportSpec["GroupByData4"]);
} else {
	 $SQLSelectCls = $SQLSelectCls . ", 0 AS Col7, 0 AS Col8";
}

/*Right, now run thro the cols and build the select clause from the defined cols */

while ($Cols = DB_fetch_array($ColsResult)){
    If ($Cols["Calculation"]==0){
	 $SQLSelectCls = $SQLSelectCls . ", Sum(CASE WHEN SalesAnalysis.PeriodNo >= " . $Cols["PeriodFrom"] . " AND SalesAnalysis.PeriodNo <= " . $Cols["PeriodTo"];
	 $SQLSelectCls = $SQLSelectCls . " AND SalesAnalysis.BudgetOrActual = " . $Cols["BudgetOrActual"] . " THEN " . GetDataSQL($Cols["DataType"]) . " ELSE 0 END) AS Col" . ($Cols["ColNo"] + 8);
    }
}

/* Now go through the cols again and do the SQL for the calculations - need the
Select clause to have all the non-calc fields in it before start using the calcs */

/*Set the ColsResult back at the start */
DB_data_seek($ColsResult,0);

while ($Cols = DB_fetch_array($ColsResult)){
    If ($Cols["Calculation"]==1){

	/*find the end of the col select clause AS Col# start is 8 because no need to search the SELECT
	First find out the position in the select statement where "AS ColX" is
	The first 6 Columns are defined by the group by fields so for eg the first col
	defined will be col 7 and so on - thats why need to add 6 to the col defined as */

	$length_ColNum = strpos($SQLSelectCls, "AS Col" . ($Cols["ColNumerator"] + 8) , 7);


	 If ($length_ColNum == 0) {

	     $title = "User Defined Sales Analysis Problem .... ";
	    include("includes/header.inc");
	     echo "<P>Calculated fields must use columns defined in the report specification. The numerator column number entered for this calculation is not defined in the report.";
	     echo "<BR><A HREF='$rootpath/SalesAnalReptCols.php?" . SID . "ReportID=" . $_GET['ReportID'] . "'>Maintain Columns for this report</A>";
	     include("includes/footer.inc");

	     Exit;
	 }
	 $strt_ColNum = 9; /* Start searching after SELECT */

	/*find the comma just before the Select Cls statement for the numerator column */

	do {
	     $strt_ColNum = strpos( $SQLSelectCls, ",", $strt_ColNum + 1) + 1;

	} while (strpos($SQLSelectCls, ",", $strt_ColNum) < $length_ColNum && strpos($SQLSelectCls, "," , $strt_ColNum)!=0);


/*The length of the element in the select clause defining the column will be from the comma to the
"AS ColX" bit found above */

	 $length_ColNum = $length_ColNum - $strt_ColNum - 1;

	if (!($Cols["CalcOperator"]=="C" OR $Cols["CalcOperator"]=="*")){

	/*The denominator column is also required if the constant is not used so do the same again for the denominator */

	$length_ColDen = strpos($SQLSelectCls, "AS Col" . (($Cols["ColDenominator"]) + 8), 7);
	 If ($length_ColDen == 0){
	     echo "<p>Calculated fields must use columns defined in the report specification. The denominator column number entered for this calculation is not defined in the report.";
	     Exit;
	}

	 $strt_ColDen = 7; /* start searching after SELECT */

	/*find the comma just before the Select Cls statement for the denominator column */

	do {
	     $strt_ColDen = strpos( $SQLSelectCls, ",", $strt_ColDen +1)+1;

	} while (strpos($SQLSelectCls, ",", $strt_ColDen) < $length_ColDen && strpos($SQLSelectCls, "," , $strt_ColDen)!=0);

	 $length_ColDen = $length_ColDen - $strt_ColDen - 1;

	 $SQLSelectCls = $SQLSelectCls . ", " . substr($SQLSelectCls, $strt_ColNum, $length_ColNum) . $Cols["CalcOperator"] . substr($SQLSelectCls, $strt_ColDen, $length_ColDen) . " AS Col" . ($Cols["ColNo"] + 8);

	} elseif ($Cols["CalcOperator"]=="C") {  /* its a calculation divided by Constant */

		$SQLSelectCls = $SQLSelectCls . ", " . substr($SQLSelectCls, $strt_ColNum, $length_ColNum) . "/" . $Cols["Constant"] . " AS Col" . ($Cols["ColNo"] + 8);

	} elseif ($Cols["CalcOperator"]=="*") {  /* its a calculation multiplied by constant */
		$SQLSelectCls = $SQLSelectCls . ", " . substr($SQLSelectCls, $strt_ColNum, $length_ColNum) . "*" . $Cols["Constant"] . " AS Col" . ($Cols["ColNo"] + 8);

	}

    } /*end if its a calculation */

} /* end of loop through defined columns */

$SQLTheLot =	$SQLSelectCls . " " . $SQLFromCls . $SQLGroupCls . " " . $SQLHavingCls;

/*For the purposes of debugging */
/*echo "<P>". $SQLTheLot;
exit;
*/

/*Now let her go .... */
$result=DB_query($SQLTheLot, $db);

if (DB_error_no($db)!=0){
     echo "<BR>There was a problem running the SQL to retreive the sales analysis information - " . DB_error_msg($db);
     if ($debug==1){
	 echo "<BR>The SQL that was used to retrieve the user defined sales analysis info was :<BR>$SQLTheLot";
     }
     exit;
}

if (DB_num_rows($result)==0){
    $title = "User Defined Sales Analysis Problem .... ";
   include("includes/header.inc");
    echo "<P>The user defined sales analysis SQL did not return any rows - have another look at the criteria specified.";
    echo "<BR><A HREF='$rootpath/SalesAnalRepts.php?" . SID . "SelectedReport=" . $_GET['ReportID'] . "'>Look at the design of this report</A>";
    echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
    include("includes/footer.inc");

   exit;
}

if (isset($_GET['ProducePDF'])){

	include ("includes/class.pdf.php");

	/* A4_Landscape */

	$Page_Width=842;
	$Page_Height=595;
	$Top_Margin=20;
	$Bottom_Margin=20;
	$Left_Margin=25;
	$Right_Margin=22;

	$PageSize = array(0,0,$Page_Width,$Page_Height);
	$pdf = & new Cpdf($PageSize);

	$PageNumber = 0;

	$pdf->selectFont('./fonts/Helvetica.afm');

/* Standard PDF file creation header stuff */

	$pdf->addinfo('Author',"WEB-ERP " . $Version);
	$pdf->addinfo('Creator',"WEB-ERP http://weberp.sourceforge.net - R&OS PHP-PDF http://www.ros.co.nz");
	$pdf->addinfo('Title',$ReportSpec["ReportHeading"]);
	$pdf->addinfo('Subject',"Sales Analysis Report " . $ReportSpec["ReportHeading"]);

	$line_height=12;

	include ("includes/PDFSalesAnalPageHeader.inc");

	$GrpData1="";
	$GrpData2="";
	$GrpData3="";

	$Counter=0;

	/*Make an array to hold accumulators for */
	$AccumLvl1 = array();
	for ($i=0;$i<=10;$i++){
	    $AccumLvl1[$i]=0;
	}
	$AccumLvl2= array();
	for ($i=0;$i<=10;$i++){
	    $AccumLvl2[$i]=0;
	}
	$AccumLvl3=array();
	for ($i=0;$i<=10;$i++){
	    $AccumLvl3[$i]=0;
	}
	$AccumLvl4=array();
	for ($i=0;$i<=10;$i++){
	    $AccumLvl4[$i]=0;
	}

	While ($myrow = DB_fetch_array($result)){

	/*First off check that at least one of the columns of data has some none zero amounts */
	      DB_data_seek($ColsResult,0); /*go back to the beginning */
	      $ThisLineHasOutput=False;   /*assume no output to start with */
	      while ($Cols = DB_fetch_array($ColsResult)){
		     $ColumnNo ="Col" . ((int) $Cols["ColNo"] + 8);
		     if (abs($myrow[$ColumnNo])>0.5){
			 $ThisLineHasOutput = True;
		     }
	      }
	      if ($ThisLineHasOutput==True){


		      if ($myrow["Col5"]!=$GrpData3 && $myrow["Col5"]!="0" && $myrow["Col7"]!="0"){
			  /*Totals only relevant to GrpByLevel 3 if GrpByLevel 4 also used */
			     if ($Counter > 0){ /*Dont want to print totals if this is the first record */
				   $TotalText = substr("TOTAL " . $LastLine["Col5"] . " - " . $LastLine["Col6"],0,33);
				   $LeftOvers = $pdf->addTextWrap(40,$Ypos,180,$FontSize,$TotalText);

				   DB_data_seek($ColsResult,0);
				   while ($Cols = DB_fetch_array($ColsResult)){
			      		$Xpos = 160 + $Cols["ColNo"]*60;
					if ($Cols["Calculation"]==0){
					      $LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl3[$Cols["ColNo"]]),"right");
					} else { /* its a calculation need to re-perform on the totals*/

						switch ($Cols["CalcOperator"]) {
						Case "/":
							if ($AccumLvl3[$Cols["ColDenominator"]]==0){
								$TotalCalculation = 0;
							} else {
								$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] / $AccumLvl3[$Cols["ColDenominator"]];
							}
							break;
						Case "+":
							$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] + $AccumLvl3[$Cols["ColDenominator"]];
							break;
						Case "-":
							$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] + $AccumLvl3[$Cols["ColDenominator"]];
							break;
						Case "*":
							$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] * $Cols["Constant"];
							break;
						Case "C":
							if ($Cols["Constant"]==0){
								$TotalCalculation = 0;
							} else {
								$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] / $Cols["Constant"];
							}
							break;
						} /*end of switch stmt block*/
						if ($Cols["ValFormat"]=='P'){
							 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			     			} else {
							$TotalCalculation = number_format($TotalCalculation);
						}
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
					}
				   }
				   $Ypos -=(2*$line_height);
				   /*reset the accumulators to 0 */
				   for ($i=0;$i<=10;$i++){
					$AccumLvl3[$i]=0;
				   }
			     }
		      }


		      if ($myrow["Col3"]!=$GrpData2 && $myrow["Col3"]!="0" && $myrow["Col5"]!="0"){
			  /*Totals only relevant to GrpByLevel 2 if GrpByLevel 3 also used */
			     if ($Counter > 0){ /*Dont want to print totals if this is the first record */
				   $TotalText = substr("TOTAL " . $LastLine["Col3"] . " - " . $LastLine["Col4"],0,43);
				   $LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);
				   DB_data_seek($ColsResult,0);
				   while ($Cols = DB_fetch_array($ColsResult)){
      					$Xpos = 160 + $Cols["ColNo"]*60;
					if ($Cols["Calculation"]==0){
					      $LeftOvers = $pdf->addTextWrap($Xpos, $Ypos,60,$FontSize, number_format($AccumLvl2[$Cols["ColNo"]]),"right");
					} else { /* its a calculation need to re-perform on the totals*/

						switch ($Cols["CalcOperator"]) {
						Case "/":
							if ($AccumLvl2[$Cols["ColDenominator"]]==0){
								$TotalCalculation = 0;
							} else {
								$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] / $AccumLvl2[$Cols["ColDenominator"]];
							}
							break;
						Case "+":
							$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] + $AccumLvl2[$Cols["ColDenominator"]];
							break;
						Case "-":
							$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] + $AccumLvl2[$Cols["ColDenominator"]];
							break;
						Case "*":
							$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] * $Cols["Constant"];
							break;
						Case "C":
							if ($Cols["Constant"]==0){
								$TotalCalculation = 0;
							} else {
								$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] / $Cols["Constant"];
							}
							break;
						} /*end of switch stmt block*/
						if ($Cols["ValFormat"]=='P'){
							 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			     			} else {
							$TotalCalculation = number_format($TotalCalculation);
						}
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
					}

				   }
				   $Ypos -=(2*$line_height);
				   /*reset the accumulators to 0 */
				   for ($i=0;$i<=10;$i++){
					$AccumLvl2[$i]=0;
				   }
			     }
		      }

		      if ($myrow["Col1"]!=$GrpData1  && $LastLine["Col3"]!="0"){
			 /*Totals only relevant to GrpByLevel 1 if GrpByLevel 2 also used */
			     if ($Counter > 0){ /*Dont want to print totals if this is the first record */
				   $TotalText = substr("TOTAL " . $LastLine["Col1"] . " - " . $LastLine["Col2"],0,46);
				   $LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$TotalText);
				   DB_data_seek($ColsResult,0);
				   while ($Cols = DB_fetch_array($ColsResult)){
     					$Xpos = 160 + $Cols["ColNo"]*60;
					if ($Cols["Calculation"]==0){
					     $LeftOvers = $pdf->addTextWrap($Xpos, $Ypos,60,$FontSize, number_format($AccumLvl1[$Cols["ColNo"]]),"right");
					} else { /* its a calculation need to re-perform on the totals*/

						switch ($Cols["CalcOperator"]) {
						Case "/":
							if ($AccumLvl1[$Cols["ColDenominator"]]==0){
								$TotalCalculation = 0;
							} else {
								$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] / $AccumLvl1[$Cols["ColDenominator"]];
							}
							break;
						Case "+":
							$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] + $AccumLvl1[$Cols["ColDenominator"]];
							break;
						Case "-":
							$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] + $AccumLvl1[$Cols["ColDenominator"]];
							break;
						Case "*":
							$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] * $Cols["Constant"];
							break;
						Case "C":
							if ($Cols["Constant"]==0){
								$TotalCalculation = 0;
							} else {
								$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] / $Cols["Constant"];
							}
							break;
						} /*end of switch stmt block*/
						if ($Cols["ValFormat"]=='P'){
							 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			     			} else {
							$TotalCalculation = number_format($TotalCalculation);
						}
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
					}

				   }
				   $Ypos -=(2*$line_height);

				    /*reset the accumulators to 0 */
				   for ($i=0;$i<=10;$i++){
					$AccumLvl1[$i]=0;
				   }
			     }
		      }

		      $NewHeading =0;

		      if ($myrow["Col1"]!=$GrpData1){ /*Need a new heading for Level 1 */
			      $NewHeading = 1;
			      if ($ReportSpec["NewPageAfter1"]==1){
				   include ("includes/PDFSalesAnalPageHeader.inc");
			      }
			      $GroupHeadingText = substr($myrow["Col1"] . " - " . $myrow["Col2"],0,50);
			      $LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$GroupHeadingText);

			      if ($myrow["Col3"]!="0"){
					$Ypos-=$line_height;
			      }
		      }

		      if (($myrow["Col3"]!=$GrpData2  OR $NewHeading ==1) AND $myrow["Col3"]!="0"){
		      		/*Need a new heading for Level 2 */
			     $NewHeading = 1;
			     if ($ReportSpec["NewPageAfter2"]==1){
				  include ("includes/PDFSalesAnalPageHeader.inc");
			     }
			     $GroupHeadingText = substr($myrow["Col3"] . " - " . $myrow["Col4"],0,46);
			     $LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$GroupHeadingText);

			     if ($myrow["Col5"]!="0"){
				      $Ypos-=$line_height;
			     }
		      }
		      if (($myrow["Col5"]!=$GrpData3  OR $NewHeading ==1) AND $myrow["Col5"]!="0"){
		      		/*Need a new heading for Level 3 */

			     if ($ReportSpec["NewPageAfter3"]==1){
				  include ("includes/PDFSalesAnalPageHeader.inc");
			     }
			     $GroupHeadingText = substr($myrow["Col5"] . " - " . $myrow["Col6"],0,46);
			     $LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$GroupHeadingText);

			     if ($myrow["Col7"]!="0"){
				      $Ypos-=$line_height;
			     }
		      }

		      if ($myrow["Col7"]!="0"){
			/*show titles */
			     $GroupHeadingText = substr($myrow["Col7"] . " - " . $myrow["Col8"], 0, 40);
			     $LeftOvers = $pdf->addTextWrap(55,$Ypos,135,$FontSize,$GroupHeadingText);

		      }

		     /*NOW SHOW THE LINE OF DATA */
		      DB_data_seek($ColsResult,0);
		      while ($Cols = DB_fetch_array($ColsResult)){
			     $Xpos = 160 + ($Cols["ColNo"]*60);
			     $ColumnNo = "Col" . (string) (($Cols["ColNo"]) +8);
			     if ($Cols["ValFormat"]=='P'){
				 $DisplayValue = number_format($myrow[$ColumnNo] *100) . "%";
			     } else {
				$DisplayValue = number_format($myrow[$ColumnNo]);
			     }
			     $LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize,$DisplayValue, "right");

			     $AccumLvl1[$Cols["ColNo"]] += $myrow[$ColumnNo];
			     $AccumLvl2[$Cols["ColNo"]] += $myrow[$ColumnNo];
			     $AccumLvl3[$Cols["ColNo"]] += $myrow[$ColumnNo];
			     $AccumLvl4[$Cols["ColNo"]] += $myrow[$ColumnNo];
		      }

		      $Ypos -=$line_height;

		      if ($Ypos - (2*$line_height) < $Bottom_Margin){
			    include ("includes/PDFSalesAnalPageHeader.inc");
		      }//end if need a new page headed up
		      $GrpData1 = $myrow["Col1"];
		      $GrpData2 = $myrow["Col3"];
		      $GrpData3 = $myrow["Col5"];
		      $Counter++;
		      $LastLine = $myrow; /*remember the last line that had some output in an array called last line*/
		} /*The line has some positive amount on it */

	} /*end of the data loop to print lines */

	if ($LastLine["Col5"]!="0" && $LastLine["Col7"]!="0"){
	  /* if GrpBY3 and GrpBy4 are both set need to show totals for GrpBy3 */
	     if ($Counter>0){ /*Dont want to print totals if this is the first record */
		 $TotalText = substr("TOTAL " . $LastLine["Col5"] . " - " . $LastLine["Col6"],0,33);
		 $LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);

		 DB_data_seek($ColsResult,0);
		 while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos = 160 + $Cols["ColNo"]*60;
			if ($Cols["Calculation"]==0){
			    $LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl3[$Cols["ColNo"]]),"right");

			} else { /* its a calculation need to re-perform on the totals*/

				switch ($Cols["CalcOperator"]) {
					Case "/":
						if ($AccumLvl3[$Cols["ColDenominator"]]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] / $AccumLvl3[$Cols["ColDenominator"]];
						}
						break;
					Case "+":
						$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] + $AccumLvl3[$Cols["ColDenominator"]];
						break;
					Case "-":
						$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] + $AccumLvl3[$Cols["ColDenominator"]];
						break;
					Case "*":
						$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] * $Cols["Constant"];
						break;
					Case "C":
						if ($Cols["Constant"]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl3[$Cols["ColNumerator"]] / $Cols["Constant"];
						}
						break;
				} /*end of switch stmt block*/
				if ($Cols["ValFormat"]=='P'){
					 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			   	} else {
					$TotalCalculation = number_format($TotalCalculation);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
			}

		 }
		 $Ypos -=$line_height;
	     }
	}

	if ($LastLine["Col3"]!="0" && $LastLine["Col5"]!="0"){
	  /* if GrpBY2 and GrpBy3 are both set need to show totals for GrpBy2 */
	     if ($Counter>0){ /*Dont want to print totals if this is the first record */
		 $TotalText = substr("TOTAL " . $LastLine["Col3"] . " - " . $LastLine["Col4"],0,33);
		 $LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);
		 DB_data_seek($ColsResult,0);
		 while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos = 160 + $Cols["ColNo"]*60;
			if ($Cols["Calculation"]==0){
			    $LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl2[$Cols["ColNo"]]),"right");

			} else { /* its a calculation need to re-perform on the totals*/

				switch ($Cols["CalcOperator"]) {
					Case "/":
						if ($AccumLvl2[$Cols["ColDenominator"]]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] / $AccumLvl2[$Cols["ColDenominator"]];
						}
						break;
					Case "+":
						$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] + $AccumLvl2[$Cols["ColDenominator"]];
						break;
					Case "-":
						$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] + $AccumLvl2[$Cols["ColDenominator"]];
						break;
					Case "*":
						$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] * $Cols["Constant"];
						break;
					Case "C":
						if ($Cols["Constant"]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl2[$Cols["ColNumerator"]] / $Cols["Constant"];
						}
						break;
				} /*end of switch stmt block*/
				if ($Cols["ValFormat"]=='P'){
					 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			     	} else {
					$TotalCalculation = number_format($TotalCalculation);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
			}

		 }
		 $Ypos -=$line_height;
	     }
	}
	if ($LastLine["Col3"]!="0"){
	  /* GrpBY1 must always be set but if GrpBy2 is also set need to show totals for GrpBy2 */
	     if ($Counter>1){ /*Dont want to print totals if this is the first record */
		 $TotalText = substr("TOTAL " . $LastLine["Col1"] . " - " . $LastLine["Col2"],0,30);
		 $LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$TotalText);
		 DB_data_seek($ColsResult,0);
		 while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos =160 + $Cols["ColNo"]*60;
			if ($Cols["Calculation"]==0){
			      $LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl1[$Cols["ColNo"]]),"right");
			} else { /* its a calculation need to re-perform on the totals*/

				switch ($Cols["CalcOperator"]) {
				Case "/":
					if ($AccumLvl1[$Cols["ColDenominator"]]==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] / $AccumLvl1[$Cols["ColDenominator"]];
					}
					break;
				Case "+":
					$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] + $AccumLvl1[$Cols["ColDenominator"]];
					break;
				Case "-":
					$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] + $AccumLvl1[$Cols["ColDenominator"]];
					break;
				Case "*":
					$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] * $Cols["Constant"];
					break;
				Case "C":
					if ($Cols["Constant"]==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl1[$Cols["ColNumerator"]] / $Cols["Constant"];
					}
					break;
				} /*end of switch stmt block*/
				if ($Cols["ValFormat"]=='P'){
					 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			   	} else {
					$TotalCalculation = number_format($TotalCalculation);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
			}

		 }
		 $Ypos -=(2*$line_height);
	     }
	}
	if ($Counter>0){
	   $LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,"GRAND TOTAL");

	    DB_data_seek($ColsResult,0);
	    while ($Cols = DB_fetch_array($ColsResult)){
		  $Xpos =160 + $Cols["ColNo"]*60;
		  if ($Cols["Calculation"]==0){
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl4[$Cols["ColNo"]]),"right");
		} else { /* its a calculation need to re-perform on the totals*/

			switch ($Cols["CalcOperator"]) {
			Case "/":
				if ($AccumLvl4[$Cols["ColDenominator"]]==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl4[$Cols["ColNumerator"]] / $AccumLvl4[$Cols["ColDenominator"]];
				}
				break;
			Case "+":
				$TotalCalculation = $AccumLvl4[$Cols["ColNumerator"]] + $AccumLvl4[$Cols["ColDenominator"]];
				break;
			Case "-":
				$TotalCalculation = $AccumLvl4[$Cols["ColNumerator"]] + $AccumLvl4[$Cols["ColDenominator"]];
				break;
			Case "*":
				$TotalCalculation = $AccumLvl4[$Cols["ColNumerator"]] * $Cols["Constant"];
				break;
			Case "C":
				if ($Cols["Constant"]==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl4[$Cols["ColNumerator"]] / $Cols["Constant"];
				}
				break;
			} /*end of switch stmt block*/
			if ($Cols["ValFormat"]=='P'){
				 $TotalCalculation = number_format($TotalCalculation * 100) . "%";
			} else {
				$TotalCalculation = number_format($TotalCalculation);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,"right");
		}

	    }
	    $Ypos -=$line_height;
	}
/* still if ProducePDF is open  need to close after the include */

?>
