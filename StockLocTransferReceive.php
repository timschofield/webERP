<?php
$title = "Inventory Transfer - Receiving";

$PageSecurity = 8;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");


if (isset($_POST['Trf_ID'])){
	$Trf_ID=$_POST['Trf_ID'];
} elseif(isset($_GET['Trf_ID'])){
	$Trf_ID=$_GET['Trf_ID'];
}

if(isset($_POST['ProcessTransfer'])){
/*Ok Time To Post transactions to Inventory Transfers, and Update Posted variable & received Qty's  to LocTransfers */

	$i = 0;
	$TransferDate = Date($DefaultDateFormat);

	echo "<BR>StockID is :" . $_POST['StockID'.$i];

	while(strlen($_POST['StockID' . $i])>0){

		$result = DB_query("SELECT * FROM StockMaster WHERE StockID='" . $_POST['StockID' . $i] . "'",$db);
		$myrow = DB_fetch_row($result);

		if (DB_num_rows($result)==0) {
			echo "<P>The entered item code does not exist.";
		} elseif (!is_numeric($_POST['Qty' . $i])){
			echo "<P>The quantity entered for " . $_POST['StockID' . $i ] . " it not numeric, all quantities must be numeric.";
		} elseif ($_POST['Qty' . $i]<=0){
			echo "<P>The quantity entered  for " . $_POST['StockID' . $i ] . " is negative. All quantities must be for positive numbers greater than zero.";
		} elseif ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
			echo "<P>The locations to transfer from and to must be different.";
		} else {

	/*All inputs must be sensible so make the stock movement records and update the locations stocks */

			$PeriodNo = GetPeriod ($TransferDate, $db);
			$SQLTransferDate = FormatDateForSQL($TransferDate);

			$Result = DB_query("BEGIN",$db);

			/* Need to get the current location quantity will need it later for the stock movement */
			$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $_POST['StockID' . $i] . "' AND LocCode= '" . $_POST['FromStockLocation'] . "'";
			$Result = DB_query($SQL, $db);
			if (DB_num_rows($Result)==1){
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}

			// Insert the stock movement for the stock going out of the from location
			$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $_POST['StockID' . $i] . "', 16, " . $Trf_ID . ", '" . $_POST['FromStockLocation'] . "','" . $SQLTransferDate . "'," . $PeriodNo . ", 'To " . $_POST['ToStockLocName'] . "', " . -$_POST['Qty' . $i] . ", " . ($QtyOnHandPrior - $_POST['Qty' . $i]) . ")";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the stock movement record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}

			// Need to get the current location quantity will need it later for the stock movement
			$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $_POST['StockID' . $i] . "' AND LocCode= '" . $_POST['ToStockLocation'] . "'";
			$Result = DB_query($SQL, $db);
			if (DB_num_rows($Result)==1){
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}

			// Insert the stock movement for the stock coming into the to location
			$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $_POST['StockID' . $i] . "',16, " . $Trf_ID . ", '" . $_POST['ToStockLocation'] . "','" . $SQLTransferDate . "'," . $PeriodNo . ", 'From " . $_POST['FromStockLocName'] ."', " . $_POST['Qty' . $i] . ", " . ($QtyOnHandPrior + $_POST['Qty' . $i]) . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the stock movement record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}

			$SQL = "UPDATE LocStock SET Quantity = Quantity - " . $_POST['Qty' . $i] . " WHERE StockID='" . $_POST['StockID' . $i] . "' AND LocCode='" . $_POST['FromStockLocation'] . "'";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to update the stock record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}

			$SQL = "UPDATE LocStock SET Quantity = Quantity + " . $_POST['Qty' . $i] . " WHERE StockID='" . $_POST['StockID' . $i] . "' AND LocCode='" . $_POST['ToStockLocation'] . "'";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to update the stock record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				include("includes/footer.inc");
				exit;
			}

			echo "<P>A stock transfer for item code - "  . $_POST['StockID' . $i] . " " . $myrow['Description'] . " has been created from " . $_POST['FromStockLocName'] . " to " . $_POST['ToStockLocName'] . " for a quantity of " . $_POST['Qty' . $i] ." </P>";

			unset ($_POST['StockID' . $i]);
			unset ($_POST['Qty' . $i]);
		} // end if the transfer is valid
		$i++;
	} // end loop of $_POST['StockID' . $i] s

	$result = DB_query("commit",$db);
	$result = DB_query("DELETE FROM LocTransfers WHERE Reference=$Trf_ID",$db);

	unset ($Trf_ID);

} elseif(isset($Trf_ID)){

	//Begin Form for receiving shipment
	echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";
	echo "<input type=HIDDEN NAME='Trf_ID' VALUE=" . $Trf_ID . ">";

	$sql = "Select LocTransfers.StockID, Description, ShipQty, Locations.LocationName AS ShipLocationName, RecLocations.LocationName AS RecLocationName, ShipLoc, RecLoc FROM LocTransfers	INNER JOIN Locations ON LocTransfers.ShipLoc=Locations.LocCode INNER JOIN Locations AS RecLocations ON LocTransfers.RecLoc = RecLocations.LocCode INNER JOIN StockMaster On LocTransfers.StockID=StockMaster.StockID WHERE Reference =" . $Trf_ID . " ORDER BY StockID";

	$result = DB_query($sql,$db);
	if (DB_error_no($db)!=0){
		echo "<BR>The details of transfer number " . $Trf_ID . " could not be retrieved.";
		if ($debug==1){
			echo "<BR>The error message returned from the database was:<BR>" . DB_error_msg($db) . "<BR>TheSQL to retrieve the transfer was:<BR>$sql";
		}
		include("includes/footer.inc");
		exit;
	}

	if(DB_num_rows($result) == 0){
		echo "</table></form><H3>Transfer #" . $Trf_ID . " Does Not Exist</H3><HR>";
		include("includes/footer.inc");
		exit;
	}

	$myrow=DB_fetch_array($result);

	echo "<h2>Location Transfer Reference #" . $Trf_ID . " from " . $myrow['ShipLocationName'] . " to " . $myrow['RecLocationName'] . "</h2>";

	echo "Please Verify Shipment Quantities Receivied";

	echo "<INPUT TYPE=HIDDEN NAME='FromStockLocation' VALUE='" . $myrow['ShipLoc'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='ToStockLocation' VALUE='" . $myrow['RecLoc'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='FromStockLocName' VALUE='" . $myrow['ShipLocationName'] . "'>";
	echo "<INPUT TYPE=HIDDEN NAME='ToStockLocName' VALUE='" . $myrow['RecLocationName'] . "'>";

	$i = 0; //StockID Array pointer

	echo "<CENTER><TABLE BORDER=1>";

	$tableheader = "<TR><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Item Description</TD><TD class='tableheader'>Quantity Dispatched</TD><TD class='tableheader'>Quantity Received</TD></TR>";

	echo $tableheader;

	do {
		echo "<TR><td><input type=hidden name='StockID" . $i . "' value='" . $myrow['StockID'] . "'>" . $myrow['StockID'] . "</td><td>" . $myrow['Description'] . "</td>";
		if (is_numeric($_POST['Qty' . $i])){
			$Qty =$_POST['Qty' . $i];
		} else {
			$Qty =$myrow['ShipQty'];
		}
		echo "<td ALIGN=RIGHT>" . $myrow['ShipQty'] . "</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME='Qty" . $i . "' MAXLENGTH=10 SIZE=10 VALUE=" . $Qty . "></td></TR>";

		$i++;
	} while ($myrow=DB_fetch_array($result));

	echo "</table><br /><INPUT TYPE=SUBMIT NAME='ProcessTransfer' VALUE='Process Inventory Transfer'><BR /></form></CENTER>";

} else {

	echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";

	$LocResult = DB_query("SELECT LocationName, LocCode FROM Locations",$db);

	echo "<TABLE BORDER=0>";
	echo "<TR><TD>Select Location Receiving Into:</TD><TD><SELECT NAME = 'RecLocation'>";
	if (!isset($_POST['RecLocation'])){
		$_POST['RecLocation'] = $_SESSION['UserStockLocation'];
	}
	while ($myrow=DB_fetch_array($LocResult)){
		if ($myrow["LocCode"] == $_POST['RecLocation']){
			echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
			echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	}
	echo "</SELECT><INPUT TYPE=SUBMIT NAME='RefreshTransferList' VALUE='Refresh Transfer List'></TD></TR></TABLE><P>";

	$sql = "SELECT DISTINCT Reference, Locations.LocationName AS TrfFromLoc, ShipDate FROM LocTransfers INNER JOIN Locations ON LocTransfers.ShipLoc=Locations.LocCode WHERE RecLoc='" . $_POST['RecLocation'] . "'";

	$TrfResult = DB_query($sql,$db);
	if (DB_num_rows($TrfResult)>0){

		echo "<TABLE BORDER=0>";

		echo "<TR><TD class='tableheader'>Transfer Ref</TD><TD class='tableheader'>Transfer From</TD><TD class='tableheader'>Dispatch Date</TD></TR>";

		while ($myrow=DB_fetch_array($TrfResult)){

			echo "<TR><TD ALIGN=RIGHT>" . $myrow['Reference'] . "</TD><TD>" . $myrow['TrfFromLoc'] . "</TD><TD>" . ConvertSQLDate($myrow['ShipDate']) . "</TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Trf_ID=" . $myrow['Reference'] . "'>Receive</A></TD></TR>";

		}

		echo "</table>";
	}
	echo "</FORM>";
}
include("includes/footer.inc");
?>
