<?php


include('includes/session.php');

$Title = _('Stock Usage');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_POST['ShowGraphUsage'])) {
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/StockUsageGraph.php?StockLocation=' . $_POST['StockLocation']  . '&amp;StockID=' . $StockID . '">';
	prnMsg(_('You should automatically be forwarded to the usage graph') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $RootPath . '/StockUsageGraph.php?StockLocation=' . $_POST['StockLocation'] .'&amp;StockID=' . $StockID . '">' . _('click here') . '</a> ' . _('to continue'),'info');
	exit();
}

$ViewTopic = 'Inventory';
$BookMark = '';

include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Dispatch') .
		'" alt="" />' . ' ' . $Title . '
	</p>';

$Result = DB_query("SELECT description,
						units,
						mbflag,
						decimalplaces
					FROM stockmaster
					WHERE stockid='".$StockID."'");
$MyRow = DB_fetch_row($Result);

$DecimalPlaces = $MyRow[3];

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>';

$Its_A_KitSet_Assembly_Or_Dummy =False;
if ($MyRow[2]=='K'
	OR $MyRow[2]=='A'
	OR $MyRow[2]=='D') {

	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo '<h3>' . $StockID . ' - ' . $MyRow[0] . '</h3>';

	prnMsg( _('The selected item is a dummy or assembly or kit-set item and cannot have a stock holding') . '. ' . _('Please select a different item'),'warn');

	$StockID = '';
} else {
	echo '<legend>
			' . _('Item') . ' : ' . $StockID . ' - ' . $MyRow[0] . '   (' . _('in units of') . ' : ' . $MyRow[1] . ')
		</legend>';
}

echo '<field>
		<label for="StockID">' . _('Stock Code') . ':</label>
		<input type="text" pattern="(?!^\s+$)[^%]{1,20}" title="" required="required" name="StockID" size="21" maxlength="20" value="' . $StockID . '" />
		<fieldhelp>'._('The input should not be blank or percentage mark').'</fieldhelp>
	</field>';

echo '<field>
		<label for="StockLocation">', _('From Stock Location') . ':</label>
		<select name="StockLocation">';

$SQL = "SELECT locations.loccode, locationname FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
$ResultStkLocs = DB_query($SQL);
while ($MyRow=DB_fetch_array($ResultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($MyRow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif ($MyRow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		 $_POST['StockLocation']=$MyRow['loccode'];
	} else {
		 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}
if (isset($_POST['StockLocation'])){
	if ('All'== $_POST['StockLocation']){
	     echo '<option selected="selected" value="All">' . _('All Locations') . '</option>';
	} else {
	     echo '<option value="All">' . _('All Locations') . '</option>';
	}
}
echo '</select>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowUsage" value="' . _('Show Stock Usage') . '" />
		<input type="submit" name="ShowGraphUsage" value="' . _('Show Graph Of Stock Usage') . '" />
	</div>';


/*HideMovt ==1 if the movement was only created for the purpose of a transaction but is not a physical movement eg. A price credit will create a movement record for the purposes of display on a credit note
but there is no physical stock movement - it makes sense honest ??? */

$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));

if (isset($_POST['ShowUsage'])){
	if($_POST['StockLocation']=='All'){
		$SQL = "SELECT periods.periodno,
				periods.lastdate_in_period,
				canview,
				SUM(CASE WHEN (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=17 OR stockmoves.type=28 OR stockmoves.type=38)
							AND stockmoves.hidemovt=0
							AND stockmoves.stockid = '" . $StockID . "'
						THEN -stockmoves.qty ELSE 0 END) AS qtyused
				FROM periods LEFT JOIN stockmoves
					ON periods.periodno=stockmoves.prd
				INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE periods.periodno <='" . $CurrentPeriod . "'
				GROUP BY periods.periodno,
					periods.lastdate_in_period
				ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
	} else {
		$SQL = "SELECT periods.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=17 OR stockmoves.type=28 OR stockmoves.type=38)
								AND stockmoves.hidemovt=0
								AND stockmoves.stockid = '" . $StockID . "'
								AND stockmoves.loccode='" . $_POST['StockLocation'] . "'
							THEN -stockmoves.qty ELSE 0 END) AS qtyused
				FROM periods LEFT JOIN stockmoves
					ON periods.periodno=stockmoves.prd
				WHERE periods.periodno <='" . $CurrentPeriod . "'
				GROUP BY periods.periodno,
					periods.lastdate_in_period
				ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];

	}
	$MovtsResult = DB_query($SQL);
	if (DB_error_no() !=0) {
		echo _('The stock usage for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();
		if ($Debug==1){
		echo '<br />' . _('The SQL that failed was') . $SQL;
		}
		exit();
	}

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . _('Month') . '</th>
					<th class="SortedColumn">' . _('Usage') . '</th>
				</tr>
			</thead>
			<tbody>';

	$TotalUsage = 0;
	$PeriodsCounter =0;

	while ($MyRow=DB_fetch_array($MovtsResult)) {

		$DisplayDate = MonthAndYearFromSQLDate($MyRow['lastdate_in_period']);

		$TotalUsage += $MyRow['qtyused'];
		$PeriodsCounter++;
		echo '<tr class="striped_row">
				<td>', $DisplayDate, '</td>
				<td class="number">', locale_number_format($MyRow['qtyused'],$DecimalPlaces), '</td>
			</tr>';
	} //end of while loop

	echo '</tbody></table>';

	if ($TotalUsage>0 AND $PeriodsCounter>0){
		echo '<table class="selection"><tr>
				<th colspan="2">' . _('Average Usage per month is') . ' ' . locale_number_format($TotalUsage/$PeriodsCounter) . '</th>
			</tr></table>';
	}

} /* end if Show Usage is clicked */


echo '<div class="centre">';
echo '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status')  . '</a>';
if (isset($_POST['StockLocation'])) {
	echo '<br />
		<a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '&amp;StockLocation=' . $_POST['StockLocation'] . '">' . _('Show Stock Movements') . '</a>';
	echo '<br />
		<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '&amp;StockLocation=' . $_POST['StockLocation'] . '">' . _('Search Outstanding Sales Orders') . '</a>';
}
echo '<br />
	<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a>';
echo '<br />
	<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</a>';

echo '</div>
	</form>';
include('includes/footer.php');
