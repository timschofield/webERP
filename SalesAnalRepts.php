<?php

/* $Revision: 1.2 $ */
$title = "Sales Analysis Reports Maintenance";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");



Function GrpByDataOptions ($GroupByDataX){

/*Sales analysis headers group by data options */
 if ($GroupByDataX == "Sales Area"){
     echo "<OPTION SELECTED 'Sales Area'>Sales Area";
 } else {
    echo "<OPTION 'Sales Area'>Sales Area";
 }
 if ($GroupByDataX == "Product Code"){
     echo "<OPTION SELECTED 'Product Code'>Product Code";
 } else {
    echo "<OPTION 'Product Code'>Product Code";
 }
 if ($GroupByDataX == "Customer Code"){
     echo "<OPTION SELECTED 'Customer Code'>Customer Code";
 } else {
    echo "<OPTION 'Customer Code'>Customer Code";
 }
 if ($GroupByDataX == "Sales Type"){
     echo "<OPTION SELECTED 'Sales Type'>Sales Type";
 } else {
    echo "<OPTION 'Sales Type'>Sales Type";
 }
 if ($GroupByDataX == "Product Type"){
     echo "<OPTION SELECTED 'Product Type'>Product Type";
 } else {
    echo "<OPTION 'Product Type'>Product Type";
 }
 if ($GroupByDataX == "Customer Branch"){
     echo "<OPTION SELECTED 'Customer Branch'>Customer Branch";
 } else {
    echo "<OPTION 'Customer Branch'>Customer Branch";
 }
 if ($GroupByDataX == "Sales Person"){
     echo "<OPTION SELECTED 'Sales Person'>Sales Person";
 } else {
    echo "<OPTION 'Sales Person'>Sales Person";
 }
 if ($GroupByDataX=="Not Used" OR $GroupByDataX == "" OR ! isset($GroupByDataX) OR is_null($GroupByDataX)){
     echo "<OPTION SELECTED ''>Not Used";
 } else {
    echo "<OPTION ''>Not Used";
 }
}

/* end of function  */
?>

<P>

<?php

if (isset($_GET['SelectedReport'])){
	$SelectedReport = $_GET['SelectedReport'];
} elseif (isset($_POST['SelectedReport'])){
	$SelectedReport = $_POST['SelectedReport'];
}


if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['ReportHeading']) <2) {
		$InputError = 1;
		echo "<BR>The report heading must be more than two characters long. No report heading was entered!";
	}
	if ($_POST['GroupByData1']=="" OR !isset($_POST['GroupByData1']) OR $_POST['GroupByData1']=="Not Used"){
	      $InputError = 1;
	      echo "<BR>A group by item must be specified for the report to have any output.";
	}
	if ($_POST['GroupByData3']=="Not Used" AND $_POST['GroupByData4']!="Not Used"){ 
		// If GroupByData3 is blank but GroupByData4 is used then move GroupByData3 to GroupByData2
		$_POST['GroupByData3'] = $_POST['GroupByData4']; 
		$_POST['Lower3'] = $_POST['Lower4']; 
		$_POST['Upper3'] = $_POST['Upper4']; 
	}
	if ($_POST['GroupByData2']=="Not Used" AND $_POST['GroupByData3']!="Not Used"){
	     /*If GroupByData2 is blank but GroupByData3 is used then move GroupByData3 to GroupByData2 */
	     $_POST['GroupByData2'] = $_POST['GroupByData3'];
	     $_POST['Lower2'] = $_POST['Lower3'];
	     $_POST['Upper2'] = $_POST['Upper3'];
	}
	if (($_POST['Lower1']=="" OR $_POST['Upper1']=="")){
	     $InputError = 1;
	     echo "<BR>Group by Level 1 is set but the upper and lower limits are not set - these must be specified for the report to have any output.";
	}
	if (($_POST['GroupByData2']!="Not Used") AND ($_POST['Lower2']=="" || $_POST['Upper2']=="")){
	     $InputError = 1;
	     echo "<BR>Group by Level 2 is set but the upper and lower limits are not set - these must be specified for the report to have any output.";
	}
	if (($_POST['GroupByData3']!="Not Used") AND ($_POST['Lower3']=="" || $_POST['Upper3']=="")){
	     $InputError = 1;
	     echo "<BR>Group by Level 3 is set but the upper and lower limits are not set - these must be specified for the report to have any output.";
	}
	if (($_POST['GroupByData4']!="Not Used") AND ($_POST['Lower4']=="" || $_POST['Upper4']=="")){ 
		$InputError = 1; 
		echo "<BR>Group by Level 4 is set but the upper and lower limits are not set - these must be specified for the report to have any output."; 
	}
	if ($_POST['GroupByData1']!="Not Used" AND $_POST['Lower1'] > $_POST['Upper1']){
	     $InputError = 1;
	     echo "<BR>Group by Level 1 is set but the lower limit is greater than the upper limit - the report will have no output.";
	}
	if ($_POST['GroupByData2']!="Not Used" AND $_POST['Lower2'] > $_POST['Upper2']){
	     $InputError = 1;
	     echo "<BR>Group by Level 2 is set but the lower limit is greater than the upper limit - the report will have no output.";
	}
	if ($_POST['GroupByData3']!="Not Used" AND $_POST['Lower3'] > $_POST['Upper3']){
	     $InputError = 1;
	     echo "<BR>Group by Level 3 is set but the lower limit is greater than the upper limit - the report will have no output.";
	}
	if ($_POST['GroupByData4']!="Not Used" AND $_POST['Lower4'] > $_POST['Upper4']){ 
		$InputError = 1; 
		echo "<BR>Group by Level 4 is set but the lower limit is greater than the upper limit - the report will have no output."; 
	} 



	if ($SelectedReport AND $InputError !=1) {

		/*SelectedReport could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = "UPDATE ReportHeaders SET ReportHeading='" . $_POST['ReportHeading'] . "', GroupByData1='" . $_POST['GroupByData1'] . "', GroupByData2='" . $_POST['GroupByData2'] . "', GroupByData3='" . $_POST['GroupByData3'] . "', GroupByData4='" . $_POST['GroupByData4'] . "', NewPageAfter1=" . $_POST['NewPageAfter1'] . ", NewPageAfter2=" . $_POST['NewPageAfter2'] . ", NewPageAfter3=" . $_POST['NewPageAfter3'] . ", Lower1='" . $_POST['Lower1'] . "', Upper1='" . $_POST['Upper1'] . "', Lower2='" . $_POST['Lower2'] . "', Upper2='" . $_POST['Upper2'] . "', Lower3='" . $_POST['Lower3'] . "', Upper3='" . $_POST['Upper3'] . "', Lower4='" . $_POST['Lower4'] . "', Upper4='" . $_POST['Upper4'] . "' WHERE ReportID = " . $SelectedReport;
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
		   echo "<BR>The report could not be updated because: " . DB_error_msg($db);
		   if ($debug==1){
		      echo "<BR>The SQL used to update the report headers was:<BR>$sql";
		   }
		   exit;
		} else {
		   echo "<BR>The " . $_POST['ReportHeading'] . " report has been updated";
		   unset($SelectedReport);
		   unset($_POST['ReportHeading']);
		   unset($_POST['GroupByData1']);
		   unset($_POST['GroupByData2']);
		   unset($_POST['GroupByData3']);
		   unset($_POST['GroupByData4']);
		   unset($_POST['NewPageAfter1']);
		   unset($_POST['NewPageAfter2']);
		   unset($_POST['NewPageAfter3']);
		   unset($_POST['Lower1']);
		   unset($_POST['Upper1']);
		   unset($_POST['Lower2']);
		   unset($_POST['Upper2']);
		   unset($_POST['Lower3']);
		   unset($_POST['Upper3']);
		   unset($_POST['Lower4']); 
		   unset($_POST['Upper4']); 
		}

	} elseif ($InputError !=1) {

	/*SelectedReport is null cos no item selected on first time round so must be adding a new report */

		$sql = "INSERT INTO ReportHeaders (ReportHeading, GroupByData1, GroupByData2, GroupByData3, GroupByData4, NewPageAfter1, NewPageAfter2, NewPageAfter3, Lower1, Upper1, Lower2, Upper2, Lower3, Upper3, Lower4, Upper4 ) VALUES ('" . $_POST['ReportHeading'] . "', '" . $_POST['GroupByData1'] . "', '" . $_POST['GroupByData2'] . "', '" . $_POST['GroupByData3'] . "', '" . $_POST['GroupByData4'] . "', " . $_POST['NewPageAfter1'] . ", " . $_POST['NewPageAfter2'] . ", " . $_POST['NewPageAfter3'] . ", '" . $_POST['Lower1'] . "', '" . $_POST['Upper1'] . "', '" . $_POST['Lower2'] . "', '" . $_POST['Upper2'] . "', '" . $_POST['Lower3'] . "', '" . $_POST['Upper3'] . "', '" . $_POST['Lower4'] . "', '" . $_POST['Upper4'] . "' )";
		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
		   echo "<BR>The report could not be added because: " . DB_error_msg($db);
		   if ($debug==1){
		      echo "<BR>The SQL used to add the report header was:<BR>$sql";
		   }
		   exit;
		} else {
			echo "<BR>The " . $_POST['ReportHeading'] . " report has been added to the database";
			unset($SelectedReport);
			unset($_POST['ReportHeading']);
			unset($_POST['GroupByData1']);
			unset($_POST['GroupByData2']);
			unset($_POST['GroupByData3']);
			unset($_POST['GroupByData4']);
			unset($_POST['NewPageAfter1']);
			unset($_POST['NewPageAfter2']);
			unset($_POST['NewPageAfter3']);
			unset($_POST['Lower1']);
			unset($_POST['Upper1']);
			unset($_POST['Lower2']);
			unset($_POST['Upper2']);
			unset($_POST['Lower3']);
			unset($_POST['Upper3']);
			unset($_POST['Lower4']); 
            unset($_POST['Upper4']); 
		}
	}


} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM ReportHeaders WHERE ReportID=$SelectedReport";
	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
	   echo "<BR>The deletion of the report heading failed because: " . DB_error_msg($db);
	   if ($debug==1){
	      echo "<BR>The SQL used to delete the report headers was:<BR>$sql";
	   }
	   exit;
	}

	$sql="DELETE FROM ReportColumns WHERE ReportID=$SelectedReport";
	if (DB_error_no($db)!=0){
	   echo "<BR>The deletion of the report's columns failed because: " . DB_error_msg($db);
	   if ($debug==1){
	      echo "<BR>The SQL used to delete the report's columns was:<BR>$sql";
	   }
	   exit;
	}

	echo "<P>Report Deleted ! <p>";
	unset($SelectedReport);

} 
 
if (!isset($SelectedReport)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReport will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Reports will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT ReportID, ReportHeading FROM ReportHeaders ORDER BY ReportID";
	$result = DB_query($sql,$db);

	echo "<CENTER><table border=1>\n";
	echo "<tr><td BGCOLOR =#800000><FONT COLOR=#ffffff><B>Report No.</B></td><td BGCOLOR =#800000><FONT COLOR=#ffffff><B>Report Title</B></td>\n";

$k=0; //row colour counter

while ($myrow = DB_fetch_array($result)) {
	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k++;
	}


printf("<td>%s</td><td>%s</td><td><a href=\"%s?SelectedReport=%s\">Design</A></td><td><a href=\"%s/SalesAnalReptCols.php?ReportID=%s\">Define Columns</A></td><td><a href=\"%s/SalesAnalysis_UserDefined.php?ReportID=%s&ProducePDF=True\">Make PDF Report</A></td><td><a href=\"%s/SalesAnalysis_UserDefined.php?ReportID=%s&ProduceCVSFile=True\">Make CSV File</A></td><td><a href=\"%s?SelectedReport=%s&delete=1\">Delete</td></tr>", $myrow[0],$myrow[1], $_SERVER['PHP_SELF'],$myrow[0], $rootpath, $myrow[0], $rootpath, $myrow[0], $rootpath, $myrow[0], $_SERVER['PHP_SELF'], $myrow[0]);

	}
	//END WHILE LIST LOOP
}

//end of ifs and buts!

?>
</CENTER></table>
<p>
<?php
if (isset($SelectedReport)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'];?>">Show All Defined Reports</a></Center>
<?php }  ?>

<P>

<?php

if (!$_GET['delete']) {
	echo "<HR></CENTER>";
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if ($SelectedReport) {
		//editing an existing Report

		$sql = "SELECT ReportID, ReportHeading, GroupByData1, NewPageAfter1, Upper1, Lower1, GroupByData2, NewPageAfter2, Upper2, Lower2, GroupByData3, Upper3, Lower3, NewPageAfter3, GroupByData4, Upper4, Lower4 FROM ReportHeaders WHERE ReportID=$SelectedReport";

		$result = DB_query($sql, $db);
		if (DB_error_no($db)!=0){
		   echo "<BR>The reports for display could not be retrieved because: " . DB_error_msg($db);
		   if ($debug==1){
		      echo "<BR>The SQL used to retrieve the report headers was:<BR>$sql";
		   }
		   exit;
		}

		$myrow = DB_fetch_array($result);

		$ReportID = $myrow["ReportID"];
		$_POST['ReportHeading']  = $myrow["ReportHeading"];
		$_POST['GroupByData1'] = $myrow["GroupByData1"];
		$_POST['NewPageAfter1'] = $myrow["NewPageAfter1"];
		$_POST['Upper1'] = $myrow["Upper1"];
		$_POST['Lower1'] = $myrow["Lower1"];
		$_POST['GroupByData2'] = $myrow["GroupByData2"];
		$_POST['NewPageAfter2'] = $myrow["NewPageAfter2"];
		$_POST['Upper2'] = $myrow["Upper2"];
		$_POST['Lower2'] = $myrow["Lower2"];
		$_POST['GroupByData3'] = $myrow["GroupByData3"];
		$_POST['Upper3'] = $myrow["Upper3"];
		$_POST['Lower3'] = $myrow["Lower3"];
		$_POST['GroupByData4'] = $myrow["GroupByData4"]; 
        $_POST['Upper4'] = $myrow["Upper4"]; 
        $_POST['Lower4'] = $myrow["Lower4"]; 

		echo "<INPUT TYPE=HIDDEN NAME='SelectedReport' VALUE=$SelectedReport>";
		echo "<INPUT TYPE=HIDDEN NAME='ReportID' VALUE=$ReportID>";
		echo "<B>Edit The Selected Report</B>";
	} else {
		echo "<B>Define A New Report</B>";
	}
	echo "<CENTER><TABLE WIDTH=100% COLSPAN=4><TR><TD ALIGN=RIGHT>Report Heading:</TD><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE='TEXT' size=80 maxlength=80 name=ReportHeading value='" . $_POST['ReportHeading'] . "'></TD></TR>";

	echo "<TR><TD>Group By 1: <SELECT name=GroupByData1>";

	GrpByDataOptions($_POST['GroupByData1']);

	echo "</SELECT></TD><TD>Page Break After: <SELECT name=NewPageAfter1>";

	if ($_POST['NewPageAfter1']==0){
	  echo "<OPTION SELECTED value=0>No";
	  echo "<OPTION value=1>Yes";
	} Else {
	  echo "<OPTION value=0>No";
	  echo "<OPTION SELECTED value=1>Yes";
	}

	echo "</SELECT></TD>";
	echo "<TD>From: <INPUT TYPE='TEXT' NAME='Lower1' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Lower1'] . "'></TD>";
	echo "<TD>To: <INPUT TYPE='TEXT' NAME='Upper1' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Upper1'] . "'></TD></TR>";

	echo "<TR><TD>Group By 2: <SELECT name=GroupByData2>";

	GrpByDataOptions($_POST['GroupByData2']);

	echo "</SELECT></TD><TD>Page Break After: <SELECT name=NewPageAfter2>";

	if ($_POST['NewPageAfter2']==0){
	  echo "<OPTION SELECTED value=0>No";
	  echo "<OPTION value=1>Yes";
	} Else {
	  echo "<OPTION value=0>No";
	  echo "<OPTION SELECTED value=1>Yes";
	}

	echo "</SELECT></TD>";
	echo "<TD>From: <INPUT TYPE='TEXT' NAME='Lower2' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Lower2'] . "'></TD>";
	echo "<TD>To: <INPUT TYPE='TEXT' NAME='Upper2' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Upper2'] . "'></TD></TR>";

	echo "<TR><TD>Group By 3: <SELECT name=GroupByData3>";

	GrpByDataOptions($_POST['GroupByData3']);
	
	echo "</SELECT></TD><TD>Page Break After: <SELECT name=NewPageAfter3>"; 
    
	if ($_POST['NewPageAfter3']==0){ 
	 echo "<OPTION SELECTED value=0>No"; 
	 echo "<OPTION value=1>Yes"; 
	} Else { 
	 echo "<OPTION value=0>No"; 
	 echo "<OPTION SELECTED value=1>Yes"; 
	} 

	echo "</SELECT></TD>"; 

	echo "<TD>From: <INPUT TYPE='TEXT' NAME='Lower3' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Lower3'] . "'></TD>"; 
	echo "<TD>To: <INPUT TYPE='TEXT' NAME='Upper3' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Upper3'] . "'></TD></TR>"; 

	echo "<TR><TD>Group By 4: <SELECT name=GroupByData4>"; 

	GrpByDataOptions($_POST['GroupByData4']); 

	echo "</SELECT></TD><TD></TD>";

	echo "<TD>From: <INPUT TYPE='TEXT' NAME='Lower4' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Lower4'] . "'></TD>";
	echo "<TD>To: <INPUT TYPE='TEXT' NAME='Upper4' SIZE=10 MAXLENGTH=10 VALUE='" . $_POST['Upper4'] . "'></TD></TR>";

	echo "</TABLE>";

	echo "<CENTER><input type='Submit' name='submit' value='Enter Information'></CENTER></FORM>";

} //end if record deleted no point displaying form to add record

include("includes/footer.inc");
?>

