<?php
/* $Id: MRPCreateDemands.php 6310 2013-08-29 10:42:50Z daintree $*/
// MRPCreateDemands.php - Create mrpdemands based on sales order history

include('includes/session.inc');
$Title = _('MRP Create Demands');
include('includes/header.inc');

if (isset($_POST['submit'])) {
   // Create mrpdemands based on sales order history

	$InputError=0;

	if (isset($_POST['FromDate']) AND !Is_Date($_POST['FromDate'])){
		$msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
		$InputError=1;
		unset($_POST['FromDate']);
	}
	if (isset($_POST['ToDate']) AND !Is_Date($_POST['ToDate'])){
		$msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
		$InputError=1;
		unset($_POST['ToDate']);
	}
	if (isset($_POST['FromDate']) and isset($_POST['ToDate']) and
		 Date1GreaterThanDate2($_POST['FromDate'], $_POST['ToDate'])){
			$msg = _('The date to must be after the date from');
			$InputError=1;
			unset($_POST['ToDate']);
			unset($_POST['FromoDate']);
	}
	if (isset($_POST['DistDate']) AND !Is_Date($_POST['DistDate'])){
		$msg = _('The distribution start date must be specified in the format') . ' ' .  $_SESSION['DefaultDateFormat'];
		$InputError=1;
		unset($_POST['DistDate']);
	}
	if (!is_numeric(filter_number_format($_POST['ExcludeQuantity']))){
		$msg = _('The quantity below which no demand will be created must be numeric');
		$InputError=1;
	}
	if (!is_numeric(filter_number_format($_POST['Multiplier']))){
		$msg = _('The multiplier is expected to be a positive number');
		$InputError=1;
	}

	if ($InputError==1){
		prnMsg($msg,'error');
	}

	$WhereCategory = " ";
	if ($_POST['CategoryID']!='All') {
		$WhereCategory = " AND stockmaster.categoryid ='" . $_POST['CategoryID'] . "' ";
	}
	$WhereLocation = " ";
	if ($_POST['Location']!='All') {
		$WhereLocation = " AND salesorders.fromstkloc ='" . $_POST['Location'] . "' ";
	}

	$sql= "SELECT salesorderdetails.stkcode,
				  SUM(salesorderdetails.quantity) AS totqty,
				  SUM(salesorderdetails.qtyinvoiced) AS totqtyinvoiced,
				  SUM(salesorderdetails.quantity * salesorderdetails.unitprice ) AS totextqty
			FROM salesorders INNER JOIN salesorderdetails
				 ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN stockmaster
				 ON salesorderdetails.stkcode = stockmaster.stockid
			WHERE orddate >='" . FormatDateForSQL($_POST['FromDate']) ."'
			AND orddate <='" . FormatDateForSQL($_POST['ToDate']) .  "'
			" . $WhereLocation . "
			" . $WhereCategory . "
			AND salesorders.quotation=0
			GROUP BY salesorderdetails.stkcode";
	//echo "<br />$sql<br />";
	$result = DB_query($sql,$db);
	// To get the quantity per period, get the whole number amount of the total quantity divided
	// by the number of periods and also get the remainder from that calculation. Put the whole
	// number quantity into each entry of the periodqty array, and add 1 to the periodqty array
	// until the remainder number is used up. Then create an mrpdemands records for everything
	// in the array

	if (filter_number_format($_POST['Multiplier']) < 1) {
		$Multiplier = 1;
	} else {
		$Multiplier = filter_number_format($_POST['Multiplier']);
	}

	if ($_POST['ExcludeQuantity'] < 1) {
		$ExcludeQty = 1;
	} else {
		$ExcludeQty = filter_number_format($_POST['ExcludeQuantity']);
	}

	if ($_POST['ExcludeAmount'] < 1) {
		$ExcludeAmount = 0;
	} else {
		$ExcludeAmount = filter_number_format($_POST['ExcludeAmount']);
	}

	// Create array of dates based on DistDate and adding either weeks or months
	$FormatedDistdate = FormatDateForSQL($_POST['DistDate']);
	if (mb_strpos($FormatedDistdate,"/")) {
		list($yyyy,$mm,$dd) = explode("/",$FormatedDistdate);
	} else if (mb_strpos($FormatedDistdate,"-")) {
		list($yyyy,$mm,$dd) = explode("-",$FormatedDistdate);
	} else if (mb_strpos($FormatedDistdate,".")) {
		list($yyyy,$mm,$dd) = explode(".",$FormatedDistdate);
	}

	$datearray[0] = $FormatedDistdate;
	// Set first date to valid manufacturing date
	$calendarsql = "SELECT COUNT(*),cal2.calendardate
					  FROM mrpcalendar
						LEFT JOIN mrpcalendar as cal2
						  ON mrpcalendar.daynumber = cal2.daynumber
					  WHERE mrpcalendar.calendardate = '".$datearray[0]."'
						AND cal2.manufacturingflag='1'
						GROUP BY cal2.calendardate";
	$resultdate = DB_query($calendarsql,$db);
	$myrowdate=DB_fetch_array($resultdate);
	// If find date based on manufacturing calendar, change date in array
	if ($myrowdate[0] != 0){
		$datearray[0] = $myrowdate[1];
	}

	$date = date('Y-m-d',mktime(0,0,0,$mm,$dd,$yyyy));
	for ($i = 1; $i <= ( $_POST['PeriodNumber'] - 1); $i++) {
		if ($_POST['Period'] == 'weekly') {
			$date = strtotime(date('Y-m-d', strtotime($date)) . ' + 1 week');
		} else {
			$date = strtotime(date('Y-m-d', strtotime($date)) . ' + 1 month');
		}
		$datearray[$i] = date('Y-m-d',$date);
		// Following sql finds daynumber for the calculated date and finds
		// a valid manufacturing date for the daynumber. There is only one valid manufacturing date
		// for each daynumber, but there could be several non-manufacturing dates for the
		// same daynumber. MRPCalendar.php maintains the manufacturing calendar.
		$calendarsql = "SELECT COUNT(*),cal2.calendardate
						  FROM mrpcalendar
							LEFT JOIN mrpcalendar as cal2
							  ON mrpcalendar.daynumber = cal2.daynumber
						  WHERE mrpcalendar.calendardate = '".$datearray[$i]."'
							AND cal2.manufacturingflag='1'
							GROUP BY cal2.calendardate";
		$resultdate = DB_query($calendarsql,$db);
		$myrowdate=DB_fetch_array($resultdate);
		// If find date based on manufacturing calendar, change date in array
		if ($myrowdate[0] != 0){
			$datearray[$i] = $myrowdate[1];
		}
		$date = date('Y-m-d',$date);
	}

	$TotalRecords = 0;
	while ($myrow = DB_fetch_array($result)) {
		if (($myrow['totqty'] >= $ExcludeQty) AND ($myrow['totextqty'] >= $ExcludeAmount)) {
			unset($PeriodQty);
			$PeriodQty[] = ' ';
			$TotalQty = $myrow['totqtyinvoiced'] * $Multiplier;
			$WholeNumber = floor($TotalQty / $_POST['PeriodNumber']);
			$Remainder = ($TotalQty % $_POST['PeriodNumber']);
			if ($WholeNumber > 0) {
				for ($i = 0; $i <= ($_POST['PeriodNumber'] - 1); $i++) {
					$PeriodQty[$i] = $WholeNumber;
				}
			}
			if ($Remainder > 0) {
				for ($i = 0; $i <= ($Remainder - 1); $i++) {
					$PeriodQty[$i] += 1;
				}
			}

			$i = 0;
			foreach ($PeriodQty as $demandqty) {
					$sql = "INSERT INTO mrpdemands (stockid,
									mrpdemandtype,
									quantity,
									duedate)
								VALUES ('" . $myrow['stkcode'] . "',
									'" . $_POST['MRPDemandtype'] . "',
									'" . $demandqty . "',
									'" . $datearray[$i] . "')";
					$insertresult = DB_query($sql,$db);
					$i++;
					$TotalRecords++;

			} // end of foreach for INSERT
		} // end of if that checks exludeqty, ExcludeAmount

	} //end while loop

	prnMsg( $TotalRecords . ' ' . _('records have been created'),'success');

} // end if submit has been pressed

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
	_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">
	<tr>
		<td>' . _('Demand Type') . ':</td>
		<td><select name="MRPDemandtype">';
$sql = "SELECT mrpdemandtype,
				description
		FROM mrpdemandtypes";
$result = DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) {
	 echo '<option value="' . $myrow['mrpdemandtype'] . '">' . $myrow['mrpdemandtype'] . ' - ' .$myrow['description'] . '</option>';
} //end while loop
echo '</select></td></tr>';
echo '<tr><td>' . _('Inventory Category') . ':</td>
		<td><select name="CategoryID">';
echo '<option selected="selected" value="All">' . _('All Stock Categories') . '</option>';
$sql = "SELECT categoryid,
			   categorydescription
		FROM stockcategory";
$result = DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) {
	echo '<option value="'. $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' .$myrow['categorydescription'] . '</option>';
} //end while loop
echo '</select></td>
		</tr>';
echo '<tr><td>' . _('Inventory Location') . ':</td>
		<td><select name="Location">';
echo '<option selected="selected" value="All">' . _('All Locations') . '</option>';

$result= DB_query("SELECT loccode,
						   locationname
					FROM locations",$db);
while ($myrow=DB_fetch_array($result)){
	echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
}
echo '</select></td></tr>';
if (!isset($_POST['FromDate'])) {
	$_POST['FromDate']=date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate']=date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['DistDate'])) {
	$_POST['DistDate']=date($_SESSION['DefaultDateFormat']);
}
echo '<tr>
		<td>' . _('From Sales Date') . ':</td>
		<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="FromDate" size="10" value="' . $_POST['FromDate'] . '" />&nbsp;&nbsp;&nbsp;'. _('To Sales Date') . ':<input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ToDate" size="10" value="' . $_POST['ToDate'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Start Date For Distribution') . ':</td>
		<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] .'" name="DistDate" size="10" value="' . $_POST['DistDate'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Distribution Period') . ':</td>
		<td><select name="Period">
			<option selected="selected" value="weekly">' . _('Weekly') . '</option>
			<option value="monthly">' . _('Monthly')  . '</option>
			</select></td>
	</tr>
	<tr>
		<td>' . _('Number of Periods') .':</td>
		<td><input type ="text" class="number" name="PeriodNumber" size="4" value="1" /></td>
	</tr>
	<tr>
		<td>' . _('Exclude Total Quantity Less Than') . ':</td>
		<td><input type ="text" class="number" name="ExcludeQuantity" size="4" value="1" /></td>
    </tr>
	<tr>
		<td>' . _('Exclude Total Dollars Less Than') . ':</td>
		<td><input type ="text" class="number" name="ExcludeAmount" size="8" value="0" /></td>
	</tr>
	<tr>
		<td>' . _('Multiplier') .':</td>
		<td><input type="text" class="number" name="Multiplier" size="2" value="1" /></td>
	</tr>
	<tr>
		<td></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Submit') .  '" />
	</div>';
echo '</div>
      </form>';

include('includes/footer.inc');
?>