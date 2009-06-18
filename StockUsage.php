<?php

/* $Revision: 1.15 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Stock Usage');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_POST['ShowGraphUsage'])) {
	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/SuppInvGRNs.php?" . SID . "'>";
	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/StockUsageGraph.php?" . SID . "&StockLocation=" . $_POST['StockLocation']  . "&StockID=" . $StockID . "'>";
	echo '<p>' . _('You should automatically be forwarded to the usage graph') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			"<a href='" . $rootpath . "/StockUsageGraph.php?" . SID . '&StockLocation=' . $_POST['StockLocation'] .'&StockID=' . $StockID . '">' . _('click here') . '</a> ' . _('to continue') . '.<br>';
	exit;
}

include('includes/header.inc');

$result = DB_query("SELECT description, 
				units, 
				mbflag, 
				decimalplaces 
			FROM stockmaster 
			WHERE stockid='$StockID'",$db);
$myrow = DB_fetch_row($result);

$DecimalPlaces = $myrow[3];

echo '<div class="centre">';

$Its_A_KitSet_Assembly_Or_Dummy =False;
if (($myrow[2]=='K') OR ($myrow[2]=='A') OR ($myrow[2]=='D')) {
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo "<br><font color=BLUE size=3><b>$StockID - $myrow[0] </b></font>";

	echo '<br>' . _('The selected item is a dummy or assembly or kit-set item and cannot have a stock holding') . '. ' . _('Please select a different item');

	$StockID = '';
} else {
echo '<br><font size=3>' . _('Item') . ' :<b> ' . $StockID . ' - ' . $myrow[0] . ' </b>  (' . _('in units of') . ' :<b> ' . $myrow[1] . ')</b></font><br><br>';
}

echo "<form action='" . $_SERVER['PHP_SELF'] . '?'. SID ."' method=post>";
echo _('Stock Code') . ":<input type=text name='StockID' size=21 maxlength=20 value='$StockID' >";

echo _('From Stock Location') . ":<select name='StockLocation'> ";

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
		     echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}
if (isset($_POST['StockLocation'])){
	if ('All'== $_POST['StockLocation']){
	     echo "<option selected Value='All'>" . _('All Locations');
	} else {
	     echo "<option Value='All'>" . _('All Locations');
	}
}
echo '</select>';

echo " <input type=submit name='ShowUsage' VALUE='" . _('Show Stock Usage') . "'>";
echo " <input type=submit name='ShowGraphUsage' VALUE='" . _('Show Graph Of Stock Usage') . "'>";
echo '<hr>';

/* $_SESSION['NumberOfPeriodsOfStockUsage']  is defined in config.php as a user definable variable
config.php is loaded by header.inc */

/*HideMovt ==1 if the movement was only created for the purpose of a transaction but is not a physical movement eg. A price credit will create a movement record for the purposes of display on a credit note
but there is no physical stock movement - it makes sense honest ??? */
if (isset($_POST['ShowUsage'])){
	if($_POST['StockLocation']=='All'){
		$sql = "SELECT periods.periodno, 
				periods.lastdate_in_period, 
				SUM(-stockmoves.qty) AS qtyused 
			FROM stockmoves INNER JOIN periods 
				ON stockmoves.prd=periods.periodno 
			WHERE (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=28) 
			AND stockmoves.hidemovt=0 
			AND stockmoves.stockid = '" . $StockID . "' 
			GROUP BY periods.periodno, 
				periods.lastdate_in_period 
			ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
	} else {
		$sql = "SELECT periods.periodno, 
				periods.lastdate_in_period, 
				SUM(-stockmoves.qty) AS qtyused 
			FROM stockmoves INNER JOIN periods 
				ON stockmoves.prd=periods.periodno 
			WHERE (stockmoves.type=10 Or stockmoves.type=11 OR stockmoves.type=28) 
			AND stockmoves.hidemovt=0 
			AND stockmoves.loccode='" . $_POST['StockLocation'] . "' 
			AND stockmoves.stockid = '" . $StockID . "' 
			GROUP BY periods.periodno, 
				periods.lastdate_in_period 
			ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
	}
	$MovtsResult = DB_query($sql, $db);
	if (DB_error_no($db) !=0) {
		echo _('The stock usage for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
		if ($debug==1){
		echo '<br>' . _('The SQL that failed was') . $sql;
		}
		exit;
	}
	
	echo '</div><table cellpadding=2 border=0>';
	$tableheader = "<tr><th>" . _('Month') . "</th><th>" . _('Usage') . '</th></tr>';
	echo $tableheader;
	
	$j = 1;
	$k=0; //row colour counter
	
	$TotalUsage = 0;
	$PeriodsCounter =0;
	
	while ($myrow=DB_fetch_array($MovtsResult)) {
	
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
	
		$DisplayDate = MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
	
		$TotalUsage += $myrow['qtyused'];
		$PeriodsCounter++;
		printf('<td>%s</td><td align=right>%s</td></tr>', $DisplayDate, number_format($myrow['qtyused'],$DecimalPlaces));
	
	//end of page full new headings if
	}
	//end of while loop
	
	echo '</table>';
	if ($TotalUsage>0 && $PeriodsCounter>0){
	echo '<br><div class="centre">' . _('Average Usage per month is') . ' ' . number_format($TotalUsage/$PeriodsCounter);
	echo '</div>';
	}
} /* end if Show Usage is clicked */

echo '<div class="centre">';
echo "<hr><a href='$rootpath/StockStatus.php?". SID . "&StockID=$StockID'>" . _('Show Stock Status') .'</a>';
echo "<br><a href='$rootpath/StockMovements.php?". SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Show Stock Movements') . '</a>';
echo "<br><a href='$rootpath/SelectSalesOrder.php?". SID . "&SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Search Outstanding Sales Orders') . '</a>';
echo "<br><a href='$rootpath/SelectCompletedOrder.php?". SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</a>';
echo "<br><a href='$rootpath/PO_SelectOSPurchOrder.php?" .SID . "&SelectedStockItem=$StockID'>" . _('Search Outstanding Purchase Orders') . '</a>';

echo '</form></div>';
include('includes/footer.inc');

?>
