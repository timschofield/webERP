<?php
/* $Revision: 1.5 $ */
// MRPCalendar.php
// Maintains the calendar of valid manufacturing dates for MRP

$PageSecurity=9;

include('includes/session.inc');
$title = _('MRP Calendar');
include('includes/header.inc');


if (isset($_POST['ChangeDate'])){
	$ChangeDate =trim(strtoupper($_POST['ChangeDate']));
} elseif (isset($_GET['ChangeDate'])){
	$ChangeDate =trim(strtoupper($_GET['ChangeDate']));
}

if (isset($_POST['submit'])) {
    submit($db,$ChangeDate);
} elseif (isset($_POST['update'])) {
    update($db,$ChangeDate);
} elseif (isset($_POST['listall'])) {
    listall($db);
} else {
    display($db,$ChangeDate);
}


function submit(&$db,&$ChangeDate)  //####SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
{

	//initialize no input errors
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'),'error');
	} 

	if (!is_date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'),'error');

	}

// Use FormatDateForSQL to put the entered dates into right format for sql
// Use ConvertSQLDate to put sql formatted dates into right format for functions such as
// DateDiff and DateAdd
	$formatfromdate = FormatDateForSQL($_POST['FromDate']);
	$formattodate = FormatDateForSQL($_POST['ToDate']);
	$convertfromdate = ConvertSQLDate($formatfromdate);
	$converttodate = ConvertSQLDate($formattodate);
		
	$dategreater = Date1GreaterThanDate2($_POST['ToDate'],$_POST['FromDate']);
	$datediff = DateDiff($converttodate,$convertfromdate,"d"); // Date1 minus Date2
	
	if ($datediff < 1) {
		$InputError = 1;
		prnMsg(_('To Date Must Be Greater Than From Date'),'error');

	}

     if ($InputError == 1) {
		display($db,$ChangeDate);
		return;     
     }
     
	$sql = 'DROP TABLE IF EXISTS mrpcalendar';
	$result = DB_query($sql,$db);
	
	$sql = 'CREATE TABLE mrpcalendar (
				calendardate date NOT NULL,
				daynumber int(6) NOT NULL,
				manufacturingflag smallint(6) NOT NULL default "1",
				INDEX (daynumber),
				PRIMARY KEY (calendardate))';
	$ErrMsg = _('The SQL to to create passbom failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
	
	$i = 0;
	
	// $daystext used so can get text of day based on the value get from DayOfWeekFromSQLDate of
	// the calendar date. See if that text is in the ExcludeDays array
	$daysText = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	$ExcludeDays = array($_POST['Sunday'],$_POST['Monday'],$_POST['Tuesday'],$_POST['Wednesday'],
						 $_POST['Thursday'],$_POST['Friday'],$_POST['Saturday']);
	$caldate = $convertfromdate;
	for ($i = 0; $i <= $datediff; $i++) {
		 $dateadd = FormatDateForSQL(DateAdd($caldate,"d",$i));
		 
		 // If the check box for the calendar date's day of week was clicked, set the manufacturing flag to 0
		 $dayofweek = DayOfWeekFromSQLDate($dateadd);
		 $manuflag = 1;
		 foreach ($ExcludeDays as $exday) {
			 if ($exday == $daysText[$dayofweek]) {
				 $manuflag = 0;
			 }
		 }
		 
		 $sql = "INSERT INTO mrpcalendar (
					calendardate,
					daynumber,
					manufacturingflag)
				 VALUES ('$dateadd',
						'1',
						'$manuflag')";
		$result = DB_query($sql,$db,$ErrMsg);
	}
	
	// Update daynumber. Set it so non-manufacturing days will have the same daynumber as a valid
	// manufacturing day that precedes it. That way can read the table by the non-manufacturing day,
	// subtract the leadtime from the daynumber, and find the valid manufacturing day with that daynumber.
	$daynumber = 1;
	$sql = 'SELECT * FROM mrpcalendar ORDER BY calendardate';
	$result = DB_query($sql,$db,$ErrMsg);
	while ($myrow = DB_fetch_array($result)) {
		   if ($myrow['manufacturingflag'] == "1") {
			   $daynumber++;
		   }
		   $caldate = $myrow['calendardate'];
		   $sql = "UPDATE mrpcalendar SET daynumber = '$daynumber'
					WHERE calendardate = '$caldate'";
		   $resultupdate = DB_query($sql,$db,$ErrMsg);
	}
	prnMsg(_("The MRP Calendar has been created"),'succes');
	display($db,$ChangeDate);

} // End of function submit()


function update(&$db,&$ChangeDate)  //####UPDATE_UPDATE_UPDATE_UPDATE_UPDATE_UPDATE_UPDATE_####
{
// Change manufacturing flag for a date. The value "1" means the date is a manufacturing date.
// After change the flag, re-calculate the daynumber for all dates.

    $InputError = 0;
    $caldate = FormatDateForSQL($ChangeDate);
	$sql="SELECT COUNT(*) FROM mrpcalendar 
	      WHERE calendardate='$caldate'
	      GROUP BY calendardate";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] < 1  ||  !is_date($ChangeDate))  {
	    $InputError = 1;
		prnMsg(_('Invalid Change Date'),'error');
	}
	
     if ($InputError == 1) {
		display($db,$ChangeDate);
		return;     
     }

	$sql="SELECT mrpcalendar.* FROM mrpcalendar WHERE calendardate='$caldate'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	$newmanufacturingflag = 0;
	if ($myrow[2] == 0) {
		$newmanufacturingflag = 1;
	}
	$sql = "UPDATE mrpcalendar SET manufacturingflag = '$newmanufacturingflag'
				WHERE calendardate = '$caldate'";
	$resultupdate = DB_query($sql,$db,$ErrMsg);
	prnMsg(_("The MRP calendar record for $ChangeDate has been updated"),'succes');
	unset ($ChangeDate);
	display($db,$ChangeDate);
	
	// Have to update daynumber any time change a date from or to a manufacturing date
	// Update daynumber. Set it so non-manufacturing days will have the same daynumber as a valid
	// manufacturing day that precedes it. That way can read the table by the non-manufacturing day,
	// subtract the leadtime from the daynumber, and find the valid manufacturing day with that daynumber.
	$daynumber = 1;
	$sql = 'SELECT * FROM mrpcalendar ORDER BY calendardate';
	$result = DB_query($sql,$db,$ErrMsg);
	while ($myrow = DB_fetch_array($result)) {
		   if ($myrow['manufacturingflag'] == "1") {
			   $daynumber++;
		   }
		   $caldate = $myrow['calendardate'];
		   $sql = "UPDATE mrpcalendar SET daynumber = '$daynumber'
					WHERE calendardate = '$caldate'";
		   $resultupdate = DB_query($sql,$db,$ErrMsg);
	} // End of while

} // End of function update()


function listall(&$db)  //####LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_####
{
// List all records in date range
    $fromdate = FormatDateForSQL($_POST['FromDate']);
    $todate = FormatDateForSQL($_POST['ToDate']);
	$sql = "SELECT calendardate,
	               daynumber,
	               manufacturingflag,
	               DAYNAME(calendardate) as dayname
		FROM mrpcalendar
		WHERE calendardate >='$fromdate'
		  AND calendardate <='$todate'";

	$ErrMsg = _('The SQL to find the parts selected failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
		
	echo "</br><table border=1>
		<tr BGCOLOR =#800000>
		    <th>" . _('Date') . "</th>
			<th>" . _('Manufacturing Date') . "</th>
		</tr></font>";
    $ctr = 0;
	while ($myrow = DB_fetch_array($result)) {
	    $flag = _('Yes');
	    if ($myrow['manufacturingflag'] == 0) {
	        $flag = _('No');
	    }
		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>",
				ConvertSQLDate($myrow[0]),
				_($myrow[3]),
				$flag);
	} //END WHILE LIST LOOP
	
	echo '</table>';
    echo '</br></br>';
    unset ($ChangeDate);
    display($db,$ChangeDate);



} // End of function listall()


function display(&$db,&$ChangeDate)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called, and is also invoked at the end of all of the other functions.

	echo "<form action=" . $_SERVER['PHP_SELF'] . "?" . SID ." method=post></br></br>";

	echo '<table>';

    echo '<tr>
        <td>' . _('From Date') . ":</td>
	    <td><input type='Text' class=date alt='".$_SESSION['DefaultDateFormat'] ."' name='FromDate' size=10 maxlength=10 value=" . $_POST['FromDate'] . '></td>
        <td>' . _('To Date') . ":</td>
	    <td><input type='Text' class=date alt='".$_SESSION['DefaultDateFormat'] ."' name='ToDate' size=10 maxlength=10 value=" . $_POST['ToDate'] . '></td>
	</tr>
	<tr><td></td></tr>
	<tr><td></td></tr>
	<tr><td>Exclude The Following Days</td></tr>
     <tr>
        <td>' . _('Saturday') . ":</td>
	    <td><input type='checkbox' name='Saturday' value='Saturday'></td>
	</tr>
     <tr>
        <td>" . _('Sunday') . ":</td>
	    <td><input type='checkbox' name='Sunday' value='Sunday'></td>
	</tr>
     <tr>
        <td>" . _('Monday') . ":</td>
	    <td><input type='checkbox' name='Monday' value='Monday'></td>
	</tr>
     <tr>
        <td>" . _('Tuesday') . ":</td>
	    <td><input type='checkbox' name='Tuesday' value='Tuesday'></td>
	</tr>
     <tr>
        <td>" . _('Wednesday') . ":</td>
	    <td><input type='checkbox' name='Wednesday' value='Wednesday'></td>
	</tr>
     <tr>
        <td>" . _('Thursday') . ":</td>
	    <td><input type='checkbox' name='Thursday' value='Thursday'></td>
	</tr>
     <tr>
        <td>" . _('Friday') . ":</td>
	    <td><input type='checkbox' name='Friday' value='Friday'></td>
	</tr>
	<tr></tr><tr></tr>
	<tr>
	    <td></td>
	</tr>
	<tr>
	    <td></td>
	</tr>
	<tr>
	    <td></td>
	    <td><input type='submit' name='submit' value='" . _('Create Calendar') . "'></td>
	    <td></td>
	    <td><input type='submit' name='listall' value='" . _('List Date Range') . "'></td>
	</tr>
	</table>
	</br>";

echo '</br></br><hr/>';
echo '<table>';
echo '<tr>
        <td>' . _('Change Date Status') . ":</td>
	    <td><input type='Text' name='ChangeDate' size=12 maxlength=12 value=" . $_POST['ChangeDate'] . '></td>
	  </tr></table>';
echo "</br></br><div class='centre'><input type='submit' name='update' value='" . _('Update') . "'></div>";
echo '</form>';

} // End of function display()


include('includes/footer.inc');
?>
