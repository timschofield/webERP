<?php

/* $Revision: 1.7 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Sales Analysis Report Columns');

include('includes/header.inc');


Function DataOptions ($DataX){

/*Sales analysis headers group by data options */
 if ($DataX == 'Quantity'){
     echo '<OPTION SELECTED Value="Quantity">' . _('Quantity');
 } else {
    echo '<OPTION Value="Quantity">' . _('Quantity');
 }
 if ($DataX == 'Gross Value'){
     echo '<OPTION SELECTED Value="Gross Value">' . _('Gross Value');
 } else {
    echo '<OPTION Value="Gross Value">' . _('Gross Value');
 }
 if ($DataX == 'Net Value'){
     echo '<OPTION SELECTED Value="Net Value">' . _('Net Value');
 } else {
    echo '<OPTION Value="Net Value">' . _('Net Value');
 }
 if ($DataX == 'Gross Profit'){
     echo '<OPTION SELECTED Value="Gross Profit">' . _('Gross Profit');
 } else {
    echo '<OPTION Value="Gross Profit">' . _('Gross Profit');
 }
 if ($DataX == 'Cost'){
     echo '<OPTION SELECTED Value="Cost">' . _('Cost');
 } else {
    echo '<OPTION Value="Cost">' . _('Cost');
 }
 if ($DataX == 'Discount'){
     echo '<OPTION SELECTED Value="Discount">' . _('Discount');
 } else {
    echo '<OPTION Value="Discount">' . _('Discount');
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



if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input
	first off validate inputs sensible */

	if (strlen($_POST['ReportHeading']) >70) {
		$InputError = 1;
		prnMsg(_('The report heading must be 70 characters or less long'),'error');
	}
	if (!is_numeric($_POST['PeriodFrom']) AND $_POST['Calculation']==0){
		$InputError = 1;
		prnMsg(_('The period from must be numeric'),'error');
	}
	if (!is_numeric($_POST['PeriodTo']) AND $_POST['Calculation']==0){
		$InputError = 1;
		prnMsg(_('The period to must be numeric'),'error');
	}
	if (!is_numeric($_POST['Constant']) AND $_POST['Calculation']==1){
		$InputError = 1;
		prnMsg(_('The constant entered must be numeric'),'error');
	}
	if (isset($_POST['ColID']) AND !is_numeric($_POST['ColID'])){
		$InputError = 1;
		prnMsg(_('The column number must be numeric'),'error');
	} elseif(isset($_POST['ColID']) AND $_POST['ColID'] >10){
		$InputError = 1;
		prnMsg(_('The column number must be less than 11'),'error');
	}
	if ($_POST['Calculation']==0 AND $_POST['PeriodFrom'] > $_POST['PeriodTo']){
		$InputError = 1;
		prnMsg(_('The Period From must be before the Period To, otherwise the column will always have no value'),'error');
	}


	if ($SelectedCol AND $InputError !=1) {


		$sql = "UPDATE reportcolumns SET heading1='" . $_POST['Heading1'] . "',
                                     heading2='" . $_POST['Heading2'] . "',
                                     calculation=" . $_POST['Calculation'] . ",
                                     periodfrom=" . $_POST['PeriodFrom'] . ",
                                     periodto=" . $_POST['PeriodTo'] . ",
                                     datatype='" . $_POST['DataType'] . "',
                                     colnumerator=" . $_POST['ColNumerator'] . ",
                                     coldenominator=" . $_POST['ColDenominator'] . ",
                                     calcoperator='" . $_POST['CalcOperator'] . "',
                                     budgetoractual=" . $_POST['BudgetOrActual'] . ",
                                     valformat='" . $_POST['ValFormat'] . "',
                                     constant = " . $_POST['Constant'] . "
                                     WHERE
                                     reportid = $ReportID AND
                                     colno=$SelectedCol";
		$ErrMsg = _('The report column could not be updated because');
		$DbgMsg = _('The SQL used to update the report column was');

		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('Column') . ' ' . $SelectedCol . ' ' . _('has been updated'),'info');
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


	} elseif ($InputError !=1 AND 
			(($_POST['Calculation']==1 AND 
			(($_POST['ColNumerator']>0 AND $_POST['Constant']!=0) OR ($_POST['ColNumerator']>0 AND $_POST['ColDenominator']>0)) 
			OR $_POST['Calculation']==0))) {

	/*SelectedReport is null cos no item selected on first time round so must be adding a new column to the report */

		$sql = "INSERT INTO reportcolumns (reportid,
                                       colno,
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
                                       valformat )
                                       VALUES (
                                       $ReportID,
                                       " . $_POST['ColID'] . ",
                                       '" . $_POST['Heading1'] . "',
                                       '" . $_POST['Heading2'] . "',
                                       " . $_POST['Calculation'] . ",
                                       " . $_POST['PeriodFrom'] . ",
                                       " . $_POST['PeriodTo'] . ",
                                       '" . $_POST['DataType'] . "',
                                       " . $_POST['ColNumerator'] . ",
                                       " . $_POST['ColDenominator'] . ",
                                       '" . $_POST['CalcOperator'] . "',
                                       " . $_POST['Constant'] . ",
                                       " . $_POST['BudgetOrActual'] . ",
                                       '" . $_POST['ValFormat'] . "')";

		$ErrMsg = _('The column could not be added to the report because');
		$DbgMsg = _('The SQL used to add the column to the report was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('Column') . ' ' . $_POST['ColID'] . ' ' . _('has been added to the database'),'info');

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


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$sql="DELETE FROM reportcolumns WHERE reportid=$ReportID AND colno=$SelectedCol";

	$ErrMsg = _('The deletion of the column failed because');
	$DbgMsg = _('The SQL used to delete this report column was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	prnMsg(_('Column') . ' ' . $SelectedCol . ' ' . _('has been deleted'),'info');

}

/* List of Columns will be displayed with links to delete or edit each.
These will call the same page again and allow update/input or deletion of the records*/

$sql = "SELECT reportheaders.reportheading,
               reportcolumns.colno,
               reportcolumns.heading1,
               reportcolumns.heading2,
               reportcolumns.calculation,
               reportcolumns.periodfrom,
               reportcolumns.periodto,
               reportcolumns.datatype,
               reportcolumns.colnumerator,
               reportcolumns.coldenominator,
               reportcolumns.calcoperator,
               reportcolumns.budgetoractual,
               reportcolumns.constant
         FROM
               reportheaders,
               reportcolumns
        WHERE  reportheaders.reportid = reportcolumns.reportid 
	AND    reportcolumns.reportid=$ReportID
        ORDER BY reportcolumns.colno";
	
$ErrMsg = _('The column definitions could not be retrieved from the database because');
$DbgMsg = _('The SQL used to retrieve the columns for the report was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

if (DB_num_rows($result)!=0){

	$myrow = DB_fetch_array($result);
	echo '<CENTER><B>' . $myrow['reportheading'] . "</B><BR><table border=1>\n";
	echo '<tr><td class="tableheader">' . _('Col') . ' #</td>
            <td class="tableheader">' . _('Heading 1') . '</td>
            <td class="tableheader">' . _('Heading 2') . '</td>';
	echo '<td class="tableheader">' . _('Calc') . '</td>
        <td class="tableheader">' . _('Prd From') . '</td>
        <td class="tableheader">' . _('Prd To') . '</td>';
	echo '<td class="tableheader">' . _('Data') . '</td>
        <td class="tableheader">' . _('Col') . ' #<BR><FONT SIZE=1>' . _('Numerator') . '</FONT></td>
        <td class="tableheader">' . _('Col') . ' #<BR><FONT SIZE=1>' . _('Denominator') . '</FONT></td>';
	echo '<td class="tableheader">' . _('Operator') . '</td>
        <td class="tableheader">' . _('Budget') . '<BR>' . _('Or Actual') . '</td></TR>';
	$k=0; //row colour counter

	do {
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}
	if ($myrow[11]==1){
		$BudOrAct = _('Actual');
	} else {
		$BudOrAct = _('Budget');
	}
	if ($myrow[4]==0){
		$Calc = _('No');
	} else {
		$Calc = _('Yes');
		$BudOrAct = _('N/A');
	}

	printf("<td><A HREF=\"%sReportID=%s&SelectedCol=%s\">%s</A></td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td>%s</td>
          	<td><a href=\"%sReportID=%s&SelectedCol=%s&delete=1\">" . _('Delete') . "</td></tr>",
          	$_SERVER['PHP_SELF'] . "?" . SID,
          	$ReportID,
          	$myrow[1],
          	$myrow[1],
          	$myrow[2],
          	$myrow[3],
          	$Calc,
          	$myrow[5],
          	$myrow[6],
          	$myrow[7],
          	$myrow[8],
          	$myrow[9],
          	$myrow[10],
          	$BudOrAct,
          	$_SERVER['PHP_SELF'] . "?" . SID,
          	$ReportID,
          	$myrow[1]);

	} while ($myrow = DB_fetch_array($result));
	//END WHILE LIST LOOP
 }

echo '</table><BR><A HREF="' . $rootpath . '/SalesAnalRepts.php?' . SID . '">' . _('Maintain Report Headers') . '</A></CENTER><p>';
if (DB_num_rows($result)>10){
    prnMsg(_('WARNING') . ': ' . _('User defined reports can have up to 10 columns defined') . '. ' . _('The report will not be able to be run until some columns are deleted'),'warn');
}

if (!isset($_GET['delete'])) {

	echo '<FORM METHOD="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<INPUT TYPE=HIDDEN NAME="ReportID" VALUE=' . $ReportID . '>';
	if (isset($SelectedCol)) {
		//editing an existing Column

		$sql = "SELECT reportid,
                   	colno,
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
                   	FROM
                   	reportcolumns
                   	WHERE
                   	reportcolumns.reportid=$ReportID AND
                   	reportcolumns.colno=$SelectedCol";


		$ErrMsg =  _('The column') . ' ' . $SelectedCol . ' ' . _('could not be retrieved because');
		$DbgMsg =  _('The SQL used to retrieve the this column was');

		$result = DB_query($sql, $db,$ErrMsg, $DbgMsg);

		$myrow = DB_fetch_array($result);

		$_POST['Heading1']=$myrow['heading1'];
		$_POST['Heading2']= $myrow['heading2'];
		$_POST['Calculation']=$myrow['calculation'];
		$_POST['PeriodFrom']=$myrow['periodfrom'];
		$_POST['PeriodTo']=$myrow['periodto'];
		$_POST['DataType'] = $myrow['datatype'];
		$_POST['ColNumerator']=$myrow['colnumerator'];
		$_POST['ColDenominator']=$myrow['coldenominator'];
		$_POST['CalcOperator']=$myrow['calcoperator'];
		$_POST['Constant']=$myrow['constant'];
		$_POST['BudgetOrActual']=$myrow['budgetoractual'];
		$_POST['ValFormat']=$myrow['valformat'];

		echo '<INPUT TYPE=HIDDEN NAME="SelectedCol" VALUE=' . $SelectedCol . '>';
		echo '<CENTER><TABLE>';

	} else {
		echo '<CENTER><TABLE>';
		echo '<TR><TD>' . _('Column Number') . ':</TD>
              <TD><INPUT TYPE="TEXT" NAME=ColID SIZE=3 MAXLENGTH=3 Value=' . $_POST['ColID'] . '><FONT SIZE=1>(' . _('A number between 1 and 10 is expected') . ')</TD>';
	}

	echo '<TR><TD>' . _('Heading line 1') . ':</TD>
            <TD><INPUT TYPE="TEXT" size=16 maxlength=15 name="Heading1" value="' . $_POST['Heading1'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Heading line 2') . ':</TD><TD><INPUT TYPE="TEXT" size=16 maxlength=15 name="Heading2" value="' . $_POST['Heading2'] . '"></TD></TR>';
	echo '<TR><TD>' . _('Calculation') . ':</TD><TD><SELECT name="Calculation">';
	if ($_POST['Calculation'] ==1){
		echo '<OPTION SELECTED Value=1>' . _('Yes');
		echo '<OPTION Value=0>' . _('No');
	} else {
		echo '<OPTION Value=1>' . _('Yes');
		echo '<OPTION SELECTED Value=0>' . _('No');
	}
	echo '</SELECT></TD></TR>';

	if ($_POST['Calculation']==0){ /*Its not a calculated column */
		echo '<TR><TD>' . _('From Period') . ':</TD><TD><INPUT TYPE="TEXT" size=4 maxlength=3 name="PeriodFrom" value=' . $_POST['PeriodFrom'] . '>  <a target="_blank" href="' . $rootpath . '/PeriodsInquiry.php?' . SID . '">' . _('View Periods') . '</a></TD></TR>';
		echo '<TR><TD>' . _('To Period') . ':</TD><TD><INPUT TYPE="TEXT" size=4 maxlength=3 name="PeriodTo" value=' . $_POST['PeriodTo'] . '></TD></TR>';
		echo '<TR><TD>' . _('Data to show') . ':</TD><TD><SELECT name="DataType">';
		DataOptions($_POST['DataType']);
		echo '</SELECT></TD></TR>';
		echo '<TR><TD>' . _('Budget or Actual') . ':</TD><TD><SELECT name=BudgetOrActual>';
		if ($_POST['BudgetOrActual']==0){
			echo '<OPTION SELECTED Value=0>' . _('Budget');
			echo '<OPTION Value=1>' . _('Actual');
		} else {
		      echo '<OPTION Value=0>' . _('Budget');
		      echo '<OPTION SELECTED Value=1>' . _('Actual');
		}
		echo '</SELECT></TD></TR>';
		echo '<INPUT TYPE=HIDDEN NAME="ValFormat" Value="N">
          <INPUT TYPE=HIDDEN NAME="ColNumerator" Value=0>
          <INPUT TYPE=HIDDEN NAME="ColDenominator" Value=0>
          <INPUT TYPE=HIDDEN NAME="CalcOperator" Value="">
          <INPUT TYPE=HIDDEN NAME="Constant" Value=0>';

	} else {  /*it IS a calculated column */

		echo '<TR><TD>' . _('Numerator Column') . ' #:</TD>
              <TD><INPUT TYPE="TEXT" size=4 maxlength=3 name="ColNumerator" value=' . $_POST['ColNumerator'] . '></TD></TR>';
		echo '<TR><TD>' . _('Denominator Column') . ' #:</TD>
              <TD><INPUT TYPE="TEXT" size=4 maxlength=3 name="ColDenominator" value=' . $_POST['ColDenominator'] . '></TD></TR>';
		echo '<TR><TD>' . _('Calculation Operator') . ':</TD>
              <TD><SELECT name="CalcOperator">';
		if ($_POST['CalcOperator'] == '/'){
		     echo '<OPTION SELECTED value="/">' . _('Numerator Divided By Denominator');
		} else {
		    echo '<OPTION value="/">' . _('Numerator Divided By Denominator');
		}
		if ($_POST['CalcOperator'] == 'C'){
		     echo '<OPTION SELECTED value="/">' . _('Numerator Divided By Constant');
		} else {
		    echo '<OPTION value="/C">' . _('Numerator Divided By Constant');
		}
		if ($_POST['CalcOperator'] == '*'){
		     echo '<OPTION SELECTED value="*">' . _('Numerator Col x Constant');
		} else {
		    echo '<OPTION value="*">' . _('Numerator Col x Constant');
		}
		if ($_POST['CalcOperator'] == '+'){
		     echo '<OPTION SELECTED value="+">' . _('Add to');
		} else {
		    echo '<OPTION value="+">' . _('Add to');
		}
		if ($_POST['CalcOperator'] == '-'){
		     echo '<OPTION SELECTED value="-">' . _('Numerator Minus Denominator');
		} else {
		    echo '<OPTION value="-">' . _('Numerator Minus Denominator');
		}

		echo '</SELECT></TD></TR>';
		echo '<TR><TD>' . _('Constant') . ':</TD><TD><INPUT TYPE="TEXT" size=10 maxlength=10 name="Constant" value=' . $_POST['Constant'] . '></TD></TR>';
		echo '<TR><TD>' . _('Format Type') . ':</TD><TD><SELECT name="ValFormat">';
		if ($_POST['ValFormat']=='N'){
			  echo '<OPTION SELECTED Value="N">' . _('Numeric');
			  echo '<OPTION Value="P">' . _('Percentage');
		} else {
			  echo '<OPTION Value="N">' . _('Numeric');
		  	echo '<OPTION SELECTED Value="P">' . _('Percentage');
		}
		echo '</SELECT></TD</TR><INPUT TYPE=HIDDEN NAME="BudgetOrActual" Value=0>
                            <INPUT TYPE=HIDDEN NAME="DataType" Value="">
                            <INPUT TYPE=HIDDEN NAME="PeriodFrom" Value=0>
                            <INPUT TYPE=HIDDEN NAME="PeriodTo" Value=0>';
	}


	echo '</TABLE>';

	echo '<input type="Submit" name="submit" value="' . _('Enter Information') . '"></CENTER></FORM>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>