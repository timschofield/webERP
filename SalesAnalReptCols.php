<?php

/* $Revision: 1.9 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Sales Analysis Report Columns');

include('includes/header.inc');


Function DataOptions ($DataX){

/*Sales analysis headers group by data options */
 if ($DataX == 'Quantity'){
     echo '<option selected Value="Quantity">' . _('Quantity');
 } else {
    echo '<option Value="Quantity">' . _('Quantity');
 }
 if ($DataX == 'Gross Value'){
     echo '<option selected Value="Gross Value">' . _('Gross Value');
 } else {
    echo '<option Value="Gross Value">' . _('Gross Value');
 }
 if ($DataX == 'Net Value'){
     echo '<option selected Value="Net Value">' . _('Net Value');
 } else {
    echo '<option Value="Net Value">' . _('Net Value');
 }
 if ($DataX == 'Gross Profit'){
     echo '<option selected Value="Gross Profit">' . _('Gross Profit');
 } else {
    echo '<option Value="Gross Profit">' . _('Gross Profit');
 }
 if ($DataX == 'Cost'){
     echo '<option selected Value="Cost">' . _('Cost');
 } else {
    echo '<option Value="Cost">' . _('Cost');
 }
 if ($DataX == 'Discount'){
     echo '<option selected Value="Discount">' . _('Discount');
 } else {
    echo '<option Value="Discount">' . _('Discount');
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
	echo '<div class="centre"><b>' . $myrow['reportheading'] . "</b><br></div><table border=1>\n";
	echo '<tr><th>' . _('Col') . ' #</th>
            <th>' . _('Heading 1') . '</th>
            <th>' . _('Heading 2') . '</th>';
	echo '<th>' . _('Calc') . '</th>
        <th>' . _('Prd From') . '</th>
        <th>' . _('Prd To') . '</th>';
	echo '<th>' . _('Data') . '</th>
        <th>' . _('Col') . ' #<br><font size=1>' . _('Numerator') . '</font></th>
        <th>' . _('Col') . ' #<br><font size=1>' . _('Denominator') . '</font></th>';
	echo '<th>' . _('Operator') . '</th>
        <th>' . _('Budget') . '<br>' . _('Or Actual') . '</th></tr>';
	$k=0; //row colour counter

	do {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
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

	printf("<td><a href=\"%sReportID=%s&SelectedCol=%s\">%s</a></td>
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

echo '</table><br><div class="centre"><a href="' . $rootpath . '/SalesAnalRepts.php?' . SID . '">' . _('Maintain Report Headers') . '</a></div><p>';
if (DB_num_rows($result)>10){
    prnMsg(_('WARNING') . ': ' . _('User defined reports can have up to 10 columns defined') . '. ' . _('The report will not be able to be run until some columns are deleted'),'warn');
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<input type=hidden name="ReportID" VALUE=' . $ReportID . '>';
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

		echo '<input type=hidden name="SelectedCol" VALUE=' . $SelectedCol . '>';
		echo '<table>';

	} else {
		echo '<table>';
		echo '<tr><td>' . _('Column Number') . ':</td>
              <td><input type="TEXT" class=number name=ColID size=3 maxlength=3 Value=' . $_POST['ColID'] . '><font size=1>(' . _('A number between 1 and 10 is expected') . ')</td>';
	}

	echo '<tr><td>' . _('Heading line 1') . ':</td>
            <td><input type="TEXT" size=16 maxlength=15 name="Heading1" value="' . $_POST['Heading1'] . '"></td></tr>';
	echo '<tr><td>' . _('Heading line 2') . ':</td><td><input type="TEXT" size=16 maxlength=15 name="Heading2" value="' . $_POST['Heading2'] . '"></td></tr>';
	echo '<tr><td>' . _('Calculation') . ':</td><td><select name="Calculation">';
	if ($_POST['Calculation'] ==1){
		echo '<option selected Value=1>' . _('Yes');
		echo '<option Value=0>' . _('No');
	} else {
		echo '<option Value=1>' . _('Yes');
		echo '<option selected Value=0>' . _('No');
	}
	echo '</select></td></tr>';

	if ($_POST['Calculation']==0){ /*Its not a calculated column */
		echo '<tr><td>' . _('From Period') . ':</td><td><input type="TEXT" class=number size=4 maxlength=3 name="PeriodFrom" value=' . $_POST['PeriodFrom'] . '>  <a target="_blank" href="' . $rootpath . '/PeriodsInquiry.php?' . SID . '">' . _('View Periods') . '</a></td></tr>';
		echo '<tr><td>' . _('To Period') . ':</td><td><input type="TEXT" class=number size=4 maxlength=3 name="PeriodTo" value=' . $_POST['PeriodTo'] . '></td></tr>';
		echo '<tr><td>' . _('Data to show') . ':</td><td><select name="DataType">';
		DataOptions($_POST['DataType']);
		echo '</select></td></tr>';
		echo '<tr><td>' . _('Budget or Actual') . ':</td><td><select name=BudgetOrActual>';
		if ($_POST['BudgetOrActual']==0){
			echo '<option selected Value=0>' . _('Budget');
			echo '<option Value=1>' . _('Actual');
		} else {
		      echo '<option Value=0>' . _('Budget');
		      echo '<option selected Value=1>' . _('Actual');
		}
		echo '</select></td></tr>';
		echo '<input type=hidden name="ValFormat" Value="N">
          <input type=hidden name="ColNumerator" Value=0>
          <input type=hidden name="ColDenominator" Value=0>
          <input type=hidden name="CalcOperator" Value="">
          <input type=hidden name="Constant" Value=0>';

	} else {  /*it IS a calculated column */

		echo '<tr><td>' . _('Numerator Column') . ' #:</td>
              <td><input type="TEXT" size=4 maxlength=3 name="ColNumerator" value=' . $_POST['ColNumerator'] . '></td></tr>';
		echo '<tr><td>' . _('Denominator Column') . ' #:</td>
              <td><input type="TEXT" size=4 maxlength=3 name="ColDenominator" value=' . $_POST['ColDenominator'] . '></td></tr>';
		echo '<tr><td>' . _('Calculation Operator') . ':</td>
              <td><select name="CalcOperator">';
		if ($_POST['CalcOperator'] == '/'){
		     echo '<option selected value="/">' . _('Numerator Divided By Denominator');
		} else {
		    echo '<option value="/">' . _('Numerator Divided By Denominator');
		}
		if ($_POST['CalcOperator'] == 'C'){
		     echo '<option selected value="/">' . _('Numerator Divided By Constant');
		} else {
		    echo '<option value="/C">' . _('Numerator Divided By Constant');
		}
		if ($_POST['CalcOperator'] == '*'){
		     echo '<option selected value="*">' . _('Numerator Col x Constant');
		} else {
		    echo '<option value="*">' . _('Numerator Col x Constant');
		}
		if ($_POST['CalcOperator'] == '+'){
		     echo '<option selected value="+">' . _('Add to');
		} else {
		    echo '<option value="+">' . _('Add to');
		}
		if ($_POST['CalcOperator'] == '-'){
		     echo '<option selected value="-">' . _('Numerator Minus Denominator');
		} else {
		    echo '<option value="-">' . _('Numerator Minus Denominator');
		}

		echo '</select></td></tr>';
		echo '<tr><td>' . _('Constant') . ':</td><td><input type="TEXT" size=10 maxlength=10 name="Constant" value=' . $_POST['Constant'] . '></td></tr>';
		echo '<tr><td>' . _('Format Type') . ':</td><td><select name="ValFormat">';
		if ($_POST['ValFormat']=='N'){
			  echo '<option selected Value="N">' . _('Numeric');
			  echo '<option Value="P">' . _('Percentage');
		} else {
			  echo '<option Value="N">' . _('Numeric');
		  	echo '<option selected Value="P">' . _('Percentage');
		}
		echo '</select></TD</tr><input type=hidden name="BudgetOrActual" Value=0>
                            <input type=hidden name="DataType" Value="">
                            <input type=hidden name="PeriodFrom" Value=0>
                            <input type=hidden name="PeriodTo" Value=0>';
	}


	echo '</table>';

	echo '<div class="centre"><input type="Submit" name="submit" value="' . _('Enter Information') . '"></div></form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>