<?php

/* $Revision: 1.12 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Stock Usage');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}

if (isset($_POST['ShowGraphUsage'])) {
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SuppInvGRNs.php?" . SID . "'>";
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/StockUsageGraph.php?" . SID . "&StockLocation=" . $_POST['StockLocation']  . "&StockID=" . $StockID . "'>";
	echo '<P>' . _('You should automatically be forwarded to the usage graph') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			"<A HREF='" . $rootpath . "/StockUsageGraph.php?" . SID . '&StockLocation=' . $_POST['StockLocation'] .'&StockID=' . $StockID . '">' . _('click here') . '</a> ' . _('to continue') . '.<BR>';
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

echo '<CENTER>';

$Its_A_KitSet_Assembly_Or_Dummy =False;
if (($myrow[2]=='K') OR ($myrow[2]=='A') OR ($myrow[2]=='D')) {
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B></FONT>";

	echo '<BR>' . _('The selected item is a dummy or assembly or kit-set item and cannot have a stock holding') . '. ' . _('Please select a different item');

	$StockID = '';
} else {
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B> (" . _('in units of') . " $myrow[1])</FONT>";
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?'. SID ."' METHOD=POST>";
echo _('Stock Code') . ":<input type=text name='StockID' size=21 maxlength=20 value='$StockID' >";

echo _('From Stock Location') . ":<SELECT name='StockLocation'> ";

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
		     echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}
if (isset($_POST['StockLocation'])){
	if ('All'== $_POST['StockLocation']){
	     echo "<OPTION SELECTED Value='All'>" . _('All Locations');
	} else {
	     echo "<OPTION Value='All'>" . _('All Locations');
	}
}
echo '</SELECT>';

echo " <INPUT TYPE=SUBMIT NAME='ShowUsage' VALUE='" . _('Show Stock Usage') . "'>";
echo " <INPUT TYPE=SUBMIT NAME='ShowGraphUsage' VALUE='" . _('Show Graph Of Stock Usage') . "'>";
echo '<HR>';

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
		echo '<BR>' . _('The SQL that failed was') . $sql;
		}
		exit;
	}
	
	echo '<TABLE CELLPADDING=2 BORDER=0>';
	$tableheader = "<TR><TD class='tableheader'>" . _('Month') . "</TD><TD class='tableheader'>" . _('Usage') . '</TD></TR>';
	echo $tableheader;
	
	$j = 1;
	$k=0; //row colour counter
	
	$TotalUsage = 0;
	$PeriodsCounter =0;
	
	while ($myrow=DB_fetch_array($MovtsResult)) {
	
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
	
		$DisplayDate = MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
	
		$TotalUsage += $myrow['qtyused'];
		$PeriodsCounter++;
		printf('<td>%s</td><td ALIGN=RIGHT>%s</td></tr>', $DisplayDate, number_format($myrow['qtyused'],$DecimalPlaces));
	
		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop
	
	echo '</TABLE>';
	if ($TotalUsage>0 && $PeriodsCounter>0){
	echo '<BR>' . _('Average Usage per month is') . ' ' . number_format($TotalUsage/$PeriodsCounter);
	}
} /* end if Show Usage is clicked */


echo "<HR><A HREF='$rootpath/StockStatus.php?". SID . "&StockID=$StockID'>" . _('Show Stock Status') .'</A>';
echo "<BR><A HREF='$rootpath/StockMovements.php?". SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Show Stock Movements') . '</A>';
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?". SID . "&SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Search Outstanding Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?". SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/PO_SelectOSPurchOrder.php?" .SID . "&SelectedStockItem=$StockID'>" . _('Search Outstanding Purchase Orders') . '</A>';

echo '</FORM></center>';
include('includes/footer.inc');

?>
