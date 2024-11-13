<?php

/////////////////////////////////////////////////////////////////////
//  Company, Month and Type of Salary Selection
/////////////////////////////////////////////////////////////////////

	echo '<tr>
			<td>' . _('For Employees of') . ':</td>
			<td><select name="Company">';
	if($_POST['Company']=="PTBB") {
		echo '<option selected="selected" value="PTBB">' . 'PT Bumi Biru' . '</option>';
		echo '<option value="PTADU">' . 'PT Angin Dingin Utara' . '</option>';
		echo '<option value="PTSMH">' . 'PT Sungai Mutiara Hitam' . '</option>';
	} else if($_POST['Company']=="PTSMH") {
		echo '<option selected="selected" value="PTSMH">' . 'PT Sungai Mutiara Hitam' . '</option>';
		echo '<option value="PTADU">' . 'PT Angin Dingin Utara' . '</option>';
		echo '<option value="PTBB">' . 'PT Bumi Biru' . '</option>';
	} else {
		echo '<option selected="selected" value="PTADU">' . 'PT Angin Dingin Utara' . '</option>';
		echo '<option value="PTBB">' . 'PT Bumi Biru' . '</option>';
		echo '<option value="PTSMH">' . 'PT Sungai Mutiara Hitam' . '</option>';
	}
	echo '</select></td></tr>';	

	echo '<tr><td>' . _('Select Month of the Salaries') . '</td>
							<td><select name="DateOfFile">';
							
	$PeriodNow = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$PeriodsResult = DB_query("SELECT lastdate_in_period, periodno FROM periods ORDER BY periodno");
	while ($PeriodRow = DB_fetch_row($PeriodsResult)){
		if ($PeriodRow[1] == ($PeriodNow-1)){
			echo '<option selected="selected" value="' . $PeriodRow[0] . '">' . MonthAndYearFromSQLDate($PeriodRow[0]) . '</option>';
		}else{
			echo '<option value="' . $PeriodRow[0] . '">' . MonthAndYearFromSQLDate($PeriodRow[0]) . '</option>';
		}
	}
	echo '</select></td></tr>';

	// check the type of salary to import
	if(!isset($_POST['SalaryType'])) {
		$_POST['SalaryType']='MONTHLY';
	}

	echo '<tr>
			<td>' . _('Type Of Salary') . ':</td>
			<td><select name="SalaryType">';
	if($_POST['SalaryType']=="MONTHLY") {
		echo '<option selected="selected" value="MONTHLY">' . _('Monthly Salary') . '</option>';
		echo '<option value="THRONLY">' . _('THR Only') . '</option>';
	} else {
		echo '<option selected="selected" value="THRONLY">' . _('THR Only') . '</option>';
		echo '<option value="MONTHLY">' . _('Monthly Salary') . '</option>';
	}
	echo '</select></td></tr>';	



?>
