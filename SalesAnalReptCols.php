<?php

/* $Revision: 1.4 $ */
$title = "Sales Analysis Report Columns Maintenance";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");


Function DataOptions ($DataX){

/*Sales analysis headers group by data options */
 if ($DataX == "Quantity"){
     echo "<OPTION SELECTED Value='Quantity'>Quantity";
 } else {
    echo "<OPTION Value='Quantity'>Quantity";
 }
 if ($DataX == "Gross Value"){
     echo "<OPTION SELECTED Value='Gross Value'>Gross Value";
 } else {
    echo "<OPTION Value='Gross Value'>Gross Value";
 }
 if ($DataX == "Net Value"){
     echo "<OPTION SELECTED Value='Net Value'>Net Value";
 } else {
    echo "<OPTION Value='Net Value'>Net Value";
 }
 if ($DataX == "Gross Profit"){
     echo "<OPTION SELECTED Value='Gross Profit'>Gross Profit";
 } else {
    echo "<OPTION Value='Gross Profit'>Gross Profit";
 }
 if ($DataX == "Cost"){
     echo "<OPTION SELECTED Value='Cost'>Cost";
 } else {
    echo "<OPTION Value='Cost'>Cost";
 }
 if ($DataX == "Discount"){
     echo "<OPTION SELECTED Value='Discount'>Discount";
 } else {
    echo "<OPTION Value='Discount'>Discount";
 }

}
/* end of functions
Right ... now to the meat	*/

if (isset($_GET['SelectedCol'])){
	$SelectedCol = $_GET['SelectedCol'];
} elseif (isset($_POST['SelectedCol'])){
	$SelectedCol = $_POST['SelectedCol'];
}


if (isset($_GET['ReportID'])){
	$ReportID = $_GET['ReportID'];
} elseif (isset($_POST['ReportID'])){
	$ReportID = $_POST['ReportID'];
}



if ($_POST['submit']=='Enter Information') {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input
	first off validate inputs sensible */

	if (strlen($_POST['ReportHeading']) >70) {
		$InputError = 1;
		echo "<BR>The report heading must be 70 characters or less long";
	}
	if (!is_numeric($_POST['PeriodFrom']) AND $_POST['Calculation']==0){
		$InputError = 1;
		echo "<BR>The period from must be numeric";
	}
	if (!is_numeric($_POST['PeriodTo']) AND $_POST['Calculation']==0){
		$InputError = 1;
		echo "<BR>The period to must be numeric";
	}
	if (!is_numeric($_POST['Constant']) AND $_POST['Calculation']==1){
		$InputError = 1;
		echo "<BR>The constant entered must be numeric";
	}
	if (isset($_POST['ColID']) AND !is_numeric($_POST['ColID'])){
		$InputError = 1;
		echo "<BR>The column number must be numeric";
	} elseif(isset($_POST['ColID']) AND $_POST['ColID'] >10){
		$InputError = 1;
		echo "<BR>The column number must be less than 11";
	}
	if ($_POST['Calculation']==0 AND $_POST['PeriodFrom'] > $_POST['PeriodTo']){
		$InputError = 1;
		echo "<BR>The Period From must be before the Period To, otherwise the column will always have no value.";
	}

	
	if ($SelectedCol AND $InputError !=1) {


		$sql = "UPDATE ReportColumns SET Heading1='" . $_POST['Heading1'] . "', Heading2='" . $_POST['Heading2'] . "', Calculation=" . $_POST['Calculation'] . ", PeriodFrom=" . $_POST['PeriodFrom'] . ", PeriodTo=" . $_POST['PeriodTo'] . ", DataType='" . $_POST['DataType'] . "', ColNumerator=" . $_POST['ColNumerator'] . ", ColDenominator=" . $_POST['ColDenominator'] . ", CalcOperator='" . $_POST['CalcOperator'] . "', BudgetOrActual=" . $_POST['BudgetOrActual'] . ", ValFormat='" . $_POST['ValFormat'] . "', Constant = " . $_POST['Constant'] . " WHERE ReportID = $ReportID AND ColNo=$SelectedCol";
		$result = DB_query($sql,$db);


		if (DB_error_no($db)!=0){
		   echo "<BR>The report column could not be updated because: " . DB_error_msg($db);
		   if ($debug==1){
		      echo "<BR>The SQL used to update the report column was:<BR>$sql";
		   }
		   exit;
		} else {
			echo "<BR>Column $SelectedCol has been updated";
			unset($SelectedCol);
			unset($_POST['ColID']);
			unset($_POST['Heading1']);
			unset($_POST['Heading2']);
			unset($_POST['Calculation']);
			unset($_POST['PeriodFrom']);
			unset($_POST['PeriodTo']);
			unset($_POST['DataType']);
			unset($_POST['ColNumerator']);
			unset($_POST['ColDenominator']);
			unset($_POST['CalcOperator']);
			unset($_POST['Constant']);
			unset($_POST['BudgetOrActual']);

		}



	} elseif ($InputError !=1 && (($_POST['Calculation']==1 && (($_POST['ColNumerator']>0 && $_POST['Constant']!=0) OR ($_POST['ColNumerator']>0 && $_POST['ColDenominator']>0)) OR $_POST['Calculation']==0))) {

	/*SelectedReport is null cos no item selected on first time round so must be adding a new column to the report */

		$sql = "INSERT INTO ReportColumns (ReportID, ColNo, Heading1, Heading2, Calculation, PeriodFrom, PeriodTo, DataType, ColNumerator, ColDenominator, CalcOperator, Constant, BudgetOrActual, ValFormat ) VALUES ($ReportID, " . $_POST['ColID'] . ", '" . $_POST['Heading1'] . "', '" . $_POST['Heading2'] . "', " . $_POST['Calculation'] . ", " . $_POST['PeriodFrom'] . ", " . $_POST['PeriodTo'] . ", '" . $_POST['DataType'] . "', " . $_POST['ColNumerator'] . ", " . $_POST['ColDenominator'] . ", '" . $_POST['CalcOperator'] . "', " . $_POST['Constant'] . ", " . $_POST['BudgetOrActual'] . ", '" . $_POST['ValFormat'] . "')";

		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
		   echo "<BR>The column could not be added to the report because: " . DB_error_msg($db);
		   if ($debug==1){
		   	echo "<BR>The SQL used to add the column to the report was:<BR>$sql";
		   }
		   exit;
		} else {
			echo "<BR>Column " . $_POST['ColID'] . " has been added to the database";
			unset($SelectedCol);
			unset($_POST['ColID']);
			unset($_POST['Heading1']);
			unset($_POST['Heading2']);
			unset($_POST['Calculation']);
			unset($_POST['PeriodFrom']);
			unset($_POST['PeriodTo']);
			unset($_POST['DataType']);
			unset($_POST['ColNumerator']);
			unset($_POST['ColDenominator']);
			unset($_POST['CalcOperator']);
			unset($_POST['Constant']);
			unset($_POST['BudgetOrActual']);
			unset($_POST['ValFormat']);
		}
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM ReportColumns WHERE ReportID=$ReportID AND ColNo=$SelectedCol";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
	   echo "<BR>The deletion of the column failed because: " . DB_error_msg($db);
	   if ($debug==1){
	      echo "<BR>The SQL used to delete this report column was:<BR>$sql";
	   }
	   exit;
	}

	echo "<P>Column $SelectedCol has been deleted.<p>";

}

/* List of Columns will be displayed with links to delete or edit each.
These will call the same page again and allow update/input or deletion of the records*/

$sql = "SELECT ReportHeaders.ReportHeading, ColNo, Heading1, Heading2, Calculation, PeriodFrom, PeriodTo, DataType, ColNumerator, ColDenominator, CalcOperator, BudgetOrActual, Constant FROM ReportHeaders, ReportColumns WHERE ReportHeaders.ReportID = ReportColumns.ReportID AND ReportColumns.ReportID=$ReportID ORDER BY ColNo";
$result = DB_query($sql,$db);
if (DB_error_no($db)!=0){
   echo "<BR>The column definitions could not be retrieved from the database because: " . DB_error_msg($db);
   if ($debug==1){
      echo "<BR>The SQL used to retrieve the columns for the report was:<BR>$sql";
   }
   exit;
}


if (DB_num_rows($result)!=0){

	$myrow = DB_fetch_array($result);
	echo "<CENTER><B>" . $myrow["ReportHeading"] . "</B><BR><table border=1>\n";
	echo "<tr><td class='tableheader'>Col #</td><td class='tableheader'>Heading 1</td><td class='tableheader'>Heading 2</td>";
	echo "<td class='tableheader'>Calc'n</td><td class='tableheader'>Prd From</td><td class='tableheader'>Prd To</td>";
	echo "<td class='tableheader'>Data</td><td class='tableheader'>Col #<BR><FONT SIZE=1>Numerator</FONT></td><td class='tableheader'>Col #<BR><FONT SIZE=1>Denominator</FONT></td>";
	echo "<td class='tableheader'>Operator</td><td class='tableheader'>Budget<BR>Or Actual</td></TR>";
	$k=0; //row colour counter

	do {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}
	if ($myrow[11]==1){
		$BudOrAct = "Actual";
	} else {
		$BudOrAct = "Budget";
	}
	if ($myrow[4]==0){
		$Calc = "No";
	} else {
		$Calc = "Yes";
		$BudOrAct = "N/A";
	}

	printf("<td><A HREF=\"%sReportID=%s&SelectedCol=%s\">%s</A></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%sReportID=%s&SelectedCol=%s&delete=1\">Delete</td></tr>", $_SERVER['PHP_SELF'] . "?" . SID, $ReportID, $myrow[1], $myrow[1], $myrow[2], $myrow[3], $Calc, $myrow[5], $myrow[6], $myrow[7], $myrow[8], $myrow[9], $myrow[10], $BudOrAct, $_SERVER['PHP_SELF'] . "?" . SID, $ReportID, $myrow[1]);

	} while ($myrow = DB_fetch_array($result));
	//END WHILE LIST LOOP
 }

echo "</table><BR><A HREF='$rootpath/SalesAnalRepts.php?" . SID . "'>Maintain Report Headers</A></CENTER><p>";
if (DB_num_rows($result)>10){
    echo "<P>WARNING: User defined reports can have up to 10 columns defined. The report will not be able to be run until some columns are deleted.";
}

if (!isset($_GET['delete'])) {

	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo "<INPUT TYPE=HIDDEN NAME='ReportID' VALUE=$ReportID>";
	if (isset($SelectedCol)) {
		//editing an existing Column

		$sql = "SELECT ReportID, ColNo, Heading1, Heading2, Calculation, PeriodFrom, PeriodTo, DataType, ColNumerator, ColDenominator, CalcOperator, Constant, BudgetOrActual, ValFormat FROM ReportColumns WHERE ReportColumns.ReportID=$ReportID AND ReportColumns.ColNo=$SelectedCol";


		$ErrMsg = "<BR>The column $SelectedCol could not be retrieved because:";
		$DbgMsg = "<BR>The SQL used to retrieve the this column was:";

		$result = DB_query($sql, $db,$ErrMsg, $DbgMsg);

		$myrow = DB_fetch_array($result);

		$_POST['Heading1']=$myrow["Heading1"];
		$_POST['Heading2']= $myrow["Heading2"];
		$_POST['Calculation']=$myrow["Calculation"];
		$_POST['PeriodFrom']=$myrow["PeriodFrom"];
		$_POST['PeriodTo']=$myrow["PeriodTo"];
		$_POST['DataType'] = $myrow["DataType"];
		$_POST['ColNumerator']=$myrow["ColNumerator"];
		$_POST['ColDenominator']=$myrow["ColDenominator"];
		$_POST['CalcOperator']=$myrow["CalcOperator"];
		$_POST['Constant']=$myrow['Constant'];
		$_POST['BudgetOrActual']=$myrow["BudgetOrActual"];
		$_POST['ValFormat']=$myrow["ValFormat"];

		echo "<INPUT TYPE=HIDDEN NAME='SelectedCol' VALUE=$SelectedCol>";
		echo "<CENTER><TABLE>";

	} else {
		echo "<CENTER><TABLE>";
		echo "<TR><TD>Column Number:</TD><TD><INPUT TYPE='TEXT' NAME=ColID SIZE=3 MAXLENGTH=3 Value=" . $_POST['ColID'] . "><FONT SIZE=1>(A number between 1 and 10 is expected)</TD>";
	}

	echo "<TR><TD>Heading line 1:</TD><TD><INPUT TYPE='TEXT' size=16 maxlength=15 name='Heading1' value='" . $_POST['Heading1'] . "'></TD></TR>";
	echo "<TR><TD>Heading line 2:</TD><TD><INPUT TYPE='TEXT' size=16 maxlength=15 name='Heading2' value='" . $_POST['Heading2'] . "'></TD></TR>";
	echo "<TR><TD>Calculation:</TD><TD><SELECT name='Calculation'>";
	if ($_POST['Calculation'] ==1){
		echo "<OPTION SELECTED Value=1>Yes";
		echo "<OPTION Value=0>No";
	} else {
		echo "<OPTION Value=1>Yes";
		echo "<OPTION SELECTED Value=0>No";
	}
	echo "</SELECT></TD></TR>";

	if ($_POST['Calculation']==0){ /*Its not a calculated column */
		echo "<TR><TD>From Period:</TD><TD><INPUT TYPE='TEXT' size=4 maxlength=3 name='PeriodFrom' value=" . $_POST['PeriodFrom'] . ">  <a target='_blank' href='$rootpath/PeriodsInquiry.php?" . SID . "'>View Periods</a></TD></TR>";
		echo "<TR><TD>To Period:</TD><TD><INPUT TYPE='TEXT' size=4 maxlength=3 name='PeriodTo' value=" . $_POST['PeriodTo'] . "></TD></TR>";

		echo "<TR><TD>Data to show:</TD><TD><SELECT name='DataType'>";
		DataOptions($_POST['DataType']);
		echo "</SELECT></TD></TR>";
		echo "<TR><TD>Budget or Actual:</TD><TD><SELECT name=BudgetOrActual>";
		if ($_POST['BudgetOrActual']==0){
			echo "<OPTION SELECTED Value=0>Budget";
			echo "<OPTION Value=1>Actual";
		} else {
		      echo "<OPTION Value=0>Budget";
		      echo "<OPTION SELECTED Value=1>Actual";
		}
		echo "</SELECT></TD></TR>";
		echo "<INPUT TYPE=HIDDEN NAME='ValFormat' Value='N'><INPUT TYPE=HIDDEN NAME='ColNumerator' Value=0><INPUT TYPE=HIDDEN NAME='ColDenominator' Value=0><INPUT TYPE=HIDDEN NAME='CalcOperator' Value=''><INPUT TYPE=HIDDEN NAME='Constant' Value=0>";

	} else {  /*it IS a calculated column */

		echo "<TR><TD>Numerator Column #:</TD><TD><INPUT TYPE='TEXT' size=4 maxlength=3 name='ColNumerator' value=" . $_POST['ColNumerator'] . "></TD></TR>";
		echo "<TR><TD>Denominator Column #:</TD><TD><INPUT TYPE='TEXT' size=4 maxlength=3 name='ColDenominator' value=" . $_POST['ColDenominator'] . "></TD></TR>";
		echo "<TR><TD>Calculation Operator:</TD><TD><SELECT name='CalcOperator'>";
		if ($_POST['CalcOperator'] == "/"){
		     echo "<OPTION SELECTED value='/'>Numerator Divided By Denominator";
		} else {
		    echo "<OPTION value='/'>Numerator Divided By Denominator";
		}
		if ($_POST['CalcOperator'] == "C"){
		     echo "<OPTION SELECTED value='/'>Numerator Divided By Constant";
		} else {
		    echo "<OPTION value='/C'>Numerator Divided By Constant";
		}
		if ($_POST['CalcOperator'] == "*"){
		     echo "<OPTION SELECTED value='*'>Numerator Col x Constant";
		} else {
		    echo "<OPTION value='*'>Numerator Col x Constant";
		}
		if ($_POST['CalcOperator'] == "+"){
		     echo "<OPTION SELECTED value='+'>Add to";
		} else {
		    echo "<OPTION value='+'>Add to";
		}
		if ($_POST['CalcOperator'] == "-"){
		     echo "<OPTION SELECTED value='-'>Numerator Minus Denominator";
		} else {
		    echo "<OPTION value='-'>Numerator Minus Denominator";
		}

		echo "</SELECT></TD></TR>";
		echo "<TR><TD>Constant:</TD><TD><INPUT TYPE='TEXT' size=10 maxlength=10 name='Constant' value=" . $_POST['Constant'] . "></TD></TR>";
		echo "<TR><TD>Format Type:</TD><TD><SELECT name='ValFormat'>";
		if ($_POST['ValFormat']=='N'){
			  echo "<OPTION SELECTED Value='N'>Numeric";
			  echo "<OPTION Value='P'>Percentage";
		} else {
			  echo "<OPTION Value='N'>Numeric";
		  	echo "<OPTION SELECTED Value='P'>Percentage";
		}
		echo "</SELECT></TD</TR><INPUT TYPE=HIDDEN NAME='BudgetOrActual' Value=0><INPUT TYPE=HIDDEN NAME='DataType' Value=''><INPUT TYPE=HIDDEN NAME='PeriodFrom' Value=0><INPUT TYPE=HIDDEN NAME='PeriodTo' Value=0>";
	}


	echo "</TABLE>";

	echo "<input type='Submit' name='submit' value='Enter Information'></CENTER></FORM>";

} //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>
