<?php

include("includes/DefineSerialItems.php");
include("includes/DefineStockTransfers.php");

$title = "Inventory Transfer - Receiving";

$PageSecurity = 8;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

if (isset($_GET['NewTransfer'])){
	unset($_SESSION['Transfer']);
}


if(isset($_POST['ProcessTransfer'])){
/*Ok Time To Post transactions to Inventory Transfers, and Update Posted variable & received Qty's  to LocTransfers */

	$PeriodNo = GetPeriod ($_SESSION['Transfer']->TranDate, $db);
	$SQLTransferDate = FormatDateForSQL($_SESSION['Transfer']->TranDate);

	$InputError = False; /*Start off hoping for the best */
	$i=0;

	foreach ($_SESSION['Transfer']->TransferItem AS $TrfLine) {
		if (is_numeric($_POST['Qty' . $i])){
		/*Update the quantity received from the inputs */
			$_SESSION['Transfer']->TransferItem[$i]->Quantity= $_POST['Qty' . $i];
  		} else {
			echo "<P>The quantity entered for " . $TrfLine->StockID . " it not numeric, all quantities must be numeric.";
			$InputError = True;
		}
		if ($_POST['Qty' . $i]<0){
			echo "<P>The quantity entered  for " . $TrfLine->StockID . " is negative. All quantities must be for positive numbers greater than zero.";
			$InputError = True;
		}
		$i++;
	} /*end loop to validate and update the SESSION['Transfer'] data */

	if (!$InputError){
	/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		foreach ($_SESSION['Transfer']->TransferItem AS $TrfLine) {
			if ($TrfLine->Quantity >0){
				$Result = DB_query("BEGIN",$db,"<BR>Could not initiate a transaction - perhaps the database does not support transactions");

				/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $TrfLine->StockID . "' AND LocCode= '" . $_SESSION['Transfer']->StockLocationFrom . "'";
				$Result = DB_query($SQL, $db,"<BR>Could not retrieve the stock quantity at the dispatch stock location prior to this transfer being processed");
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				/* Insert the stock movement for the stock going out of the from location */
				$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $TrfLine->StockID . "', 16, " . $_SESSION['Transfer']->TrfID . ", '" . $_SESSION['Transfer']->StockLocationFrom . "','" . $SQLTransferDate . "'," . $PeriodNo . ", 'To " . $_SESSION['Transfer']->StockLocationToName . "', " . -$TrfLine->Quantity . ", " . ($QtyOnHandPrior - $TrfLine->Quantity) . ")";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because:";
				$DbgMsg = "<BR>The following SQL to insert the stock movement record was used:";
				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg,True);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID($db);

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

				if ($TrfLine->Controlled ==1){
					foreach($TrfLine->SerialItems as $Item){
					/*We need to add or update the StockSerialItem record and
					The StockSerialMoves as well */

						/*First need to check if the serial items already exists or not in the location from */
						$SQL = "SELECT Count(*)
							FROM StockSerialItems
							WHERE
							StockID='" . $TrfLine->StockID . "'
							AND LocCode='" . $_SESSION['Transfer']->StockLocationFrom . "'
							AND SerialNo='" . $Item->BundleRef . "'";

						$Result = DB_query($SQL,$db,"<BR>Could not determine if the serial item exists");
						$SerialItemExistsRow = DB_fetch_row($Result);

						if ($SerialItemExistsRow[0]==1){

							$SQL = "UPDATE StockSerialItems SET
								Quantity= Quantity - " . $Item->BundleQty . "
								WHERE
								StockID='" . $TrfLine->StockID . "'
								AND LocCode='" . $_SESSION['Transfer']->StockLocationFrom . "'
								AND SerialNo='" . $Item->BundleRef . "'";

							$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because:";
							$DbgMsg = "<BR>The following SQL to update the serial stock item record was used:";
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						} else {
							/*Need to insert a new serial item record */
							$SQL = "INSERT INTO StockSerialItems (StockID,
												LocCode,
												SerialNo,
												Quantity)
								VALUES ('" . $TrfLine->StockID . "',
								'" . $_SESSION['Transfer']->StockLocationFrom . "',
								'" . $Item->BundleRef . "',
								" . -$Item->BundleQty . ")";

							$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item for the stock being transferred out of the existing location could not be inserted because:";
							$DbgMsg = "<BR>The following SQL to update the serial stock item record was used:";
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}


						/* now insert the serial stock movement */

						$SQL = "INSERT INTO StockSerialMoves (
								StockMoveNo,
								StockID,
								SerialNo,
								MoveQty
							) VALUES (
								" . $StkMoveNo . ",
								'" . $TrfLine->StockID . "',
								'" . $Item->BundleRef . "',
								" . -$Item->BundleQty . "
							)";
						$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because:";
						$DbgMsg = "<BR>The following SQL to insert the serial stock movement records was used:";
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach controlled item in the serialitems array */
				} /*end if the transferred item is a controlled item */


				/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT LocStock.Quantity
					FROM LocStock
					WHERE LocStock.StockID='" . $TrfLine->StockID . "'
					AND LocCode= '" . $_SESSION['Transfer']->StockLocationTo . "'";

				$Result = DB_query($SQL, $db, "<BR>Could not retrieve the quantity on hand at the location being transferred to");
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}

				// Insert the stock movement for the stock coming into the to location
				$SQL = "INSERT INTO StockMoves (
						StockID,
						Type,
						TransNo,
						LocCode,
						TranDate,
						Prd,
						Reference,
						Qty,
						NewQOH)
					VALUES (
						'" . $TrfLine->StockID . "',
						16,
						" . $_SESSION['Transfer']->TrfID . ",
						'" . $_SESSION['Transfer']->StockLocationTo . "',
						'" . $SQLTransferDate . "'," . $PeriodNo . ",
						'From " . $_SESSION['Transfer']->StockLocationFromName ."',
						" . $TrfLine->Quantity . ", " . ($QtyOnHandPrior + $TrfLine->Quantity) . "
						)";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record for the incoming stock cannot be added because:";
				$DbgMsg = "<BR>The following SQL to insert the stock movement record was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID($db);

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

				if ($TrfLine->Controlled ==1){
					foreach($TrfLine->SerialItems as $Item){
					/*We need to add or update the StockSerialItem record and
					The StockSerialMoves as well */

						/*First need to check if the serial items already exists or not in the location from */
						$SQL = "SELECT Count(*)
							FROM StockSerialItems
							WHERE
							StockID='" . $TrfLine->StockID . "'
							AND LocCode='" . $_SESSION['Transfer']->StockLocationTo . "'
							AND SerialNo='" . $Item->BundleRef . "'";

						$Result = DB_query($SQL,$db,"<BR>Could not determine if the serial item exists");
						$SerialItemExistsRow = DB_fetch_row($Result);

						
						if ($SerialItemExistsRow[0]==1){

							$SQL = "UPDATE StockSerialItems SET
								Quantity= Quantity + " . $Item->BundleQty . "
								WHERE
								StockID='" . $TrfLine->StockID . "'
								AND LocCode='" . $_SESSION['Transfer']->StockLocationTo . "'
								AND SerialNo='" . $Item->BundleRef . "'";

							$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated for the quantity coming in because:";
							$DbgMsg = "<BR>The following SQL to update the serial stock item record was used:";
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						} else {
							/*Need to insert a new serial item record */
							$SQL = "INSERT INTO StockSerialItems (StockID,
											LocCode,
											SerialNo,
											Quantity)
								VALUES ('" . $TrfLine->StockID . "',
								'" . $_SESSION['Transfer']->StockLocationTo . "',
								'" . $Item->BundleRef . "',
								" . $Item->BundleQty . ")";

							$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record for the stock coming in could not be added because:";
							$DbgMsg = "<BR>The following SQL to update the serial stock item record was used:";
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}


						/* now insert the serial stock movement */

						$SQL = "INSERT INTO StockSerialMoves (StockMoveNo, StockID, SerialNo, MoveQty) VALUES (" . $StkMoveNo . ", '" . $TrfLine->StockID . "', '" . $Item->BundleRef . "', " . $Item->BundleQty . ")";
						$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because:";
						$DbgMsg = "<BR>The following SQL to insert the serial stock movement records was used:";
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach controlled item in the serialitems array */
				} /*end if the transfer item is a controlled item */

				$SQL = "UPDATE LocStock
					SET Quantity = Quantity - " . $TrfLine->Quantity . "
					WHERE StockID='" . $TrfLine->StockID . "'
					AND LocCode='" . $_SESSION['Transfer']->StockLocationFrom . "'";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because:";
				$DbgMsg = "<BR>The following SQL to update the stock record was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE LocStock
					SET Quantity = Quantity + " . $TrfLine->Quantity . "
					WHERE StockID='" . $TrfLine->StockID . "'
					AND LocCode='" . $_SESSION['Transfer']->StockLocationTo . "'";

				$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because:";
				$DbgMsg = "<BR>The following SQL to update the stock record was used:";
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				echo "<P>A stock transfer for item code - "  . $TrfLine->StockID . " " . $TrfLine->ItemDescription . " has been created from " . $_SESSION['Transfer']->StockLocationFromName . " to " . $_SESSION['Transfer']->StockLocationToName . " for a quantity of " . $TrfLine->Quantity ." </P>";

				unset ($_SESSION['Transfer']->LineItem[$i]);
				unset ($_POST['Qty' . $i]);

				$result = DB_query("commit",$db);

			} /*end if Quantity > 0 */
			$i++;
		} /*end of foreach TransferItem */

		$result = DB_query("DELETE FROM LocTransfers WHERE Reference=" . $_SESSION['Transfer']->TrfID,$db);
		unset($_SESSION['Transfer']->LineItem);
		unset($_SESSION['Transfer']);
	} /* end of if no input errors */

} /*end of PRocess Transfer */

if(isset($_GET['Trf_ID'])){

	unset($_SESSION['Transfer']);

	$sql = "SELECT LocTransfers.StockID,
		StockMaster.Description,
		StockMaster.Units,
		StockMaster.Controlled,
		StockMaster.Serialised,
		StockMaster.DecimalPlaces,
		LocTransfers.ShipQty,
		Locations.LocationName AS ShipLocationName,
		RecLocations.LocationName AS RecLocationName,
		LocTransfers.ShipLoc,
		LocTransfers.RecLoc
		FROM LocTransfers INNER JOIN Locations
		ON LocTransfers.ShipLoc=Locations.LocCode
		INNER JOIN Locations AS RecLocations
		ON LocTransfers.RecLoc = RecLocations.LocCode
		INNER JOIN StockMaster
		ON LocTransfers.StockID=StockMaster.StockID
		WHERE Reference =" . $_GET['Trf_ID'] . " ORDER BY LocTransfers.StockID";


	$ErrMsg = "<BR>The details of transfer number " . $Trf_ID . " could not be retrieved because ";
	$DbgMsg = "<BR>TheSQL to retrieve the transfer was:";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if(DB_num_rows($result) == 0){
		echo "</table></form><H3>Transfer #" . $Trf_ID . " Does Not Exist</H3><HR>";
		include("includes/footer.inc");
		exit;
	}

	$myrow=DB_fetch_array($result);

	$_SESSION['Transfer']= new StockTransfer($_GET['Trf_ID'],
						$myrow['ShipLoc'],
						$myrow['ShipLocationName'],
						$myrow['RecLoc'],
						$myrow['RecLocationName'],
						Date($DefaultDateFormat)
						);
	/*Populate the StockTransfer TransferItem s array with the lines to be transferred */
	$i = 0;
	do {
		$_SESSION['Transfer']->TransferItem[$i]= new LineItem ($myrow['StockID'],
									$myrow['Description'],
									$myrow['ShipQty'],
									$myrow['Units'],
									$myrow['Controlled'],
									$myrow['Serialised'],
									$myrow['DecimalPlaces']
									);
		$i++; /*numerical index for the TransferItem[] array of LineItem s */

	} while ($myrow=DB_fetch_array($result));

} /* $_GET['Trf_ID'] is set */

if (isset($_SESSION['Transfer'])){
	//Begin Form for receiving shipment
	echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";

	echo "<H2>Location Transfer Reference #" . $_SESSION['Transfer']->TrfID . " from " . $_SESSION['Transfer']->StockLocationFromName . " to " . $_SESSION['Transfer']->StockLocationToName . "</H2>";

	echo "Please Verify Shipment Quantities Receivied";


	$i = 0; //Line Item Array pointer

	echo "<CENTER><TABLE BORDER=1>";

	$tableheader = "<TR>
			<TD class='tableheader'>Item Code</TD>
			<TD class='tableheader'>Item Description</TD>
			<TD class='tableheader'>Quantity Dispatched</TD>
			<TD class='tableheader'>Quantity Received</TD>
			<TD class='tableheader'>Units</TD>
			</TR>";

	echo $tableheader;

	foreach ($_SESSION['Transfer']->TransferItem AS $TrfLine) {

		echo "<TR>
			<td>" . $TrfLine->StockID . "</td>
			<td>" . $TrfLine->ItemDescription . "</td>";

		echo "<td ALIGN=RIGHT>" . number_format($TrfLine->ShipQty, $TrfLine->DecimalPlaces) . "</TD>";
		if (is_numeric($_POST['Qty' . $i])){
			$_SESSION['Transfer']->TransferItem[$i]->Quantity= $_POST['Qty' . $i];
			$Qty = $_POST['Qty' . $i];
		} else {
			$Qty = $TrfLine->Quantity;
		}

		if ($TrfLine->Controlled==1){
			echo "<TD ALIGN=RIGHT><INPUT TYPE=HIDDEN NAME='Qty" . $i . "' VALUE=" . $Qty . "><A HREF='" . $rootpath ."/StockTransferControlled.php?" . SID . "TransferItem=" . $i . "'>" . $Qty . "</A></td>";
		} else {
			echo "<TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME='Qty" . $i . "' MAXLENGTH=10 SIZE=10 VALUE=" . $Qty . "></td>";
		}

		echo "<td>" . $TrfLine->PartUnit . "</TD></TR>";

		$i++; /* the array of TransferItem s is indexed numerically and i matches the index no */
	} /*end of foreach TransferItem */

	echo "</table><br />
		<INPUT TYPE=SUBMIT NAME='ProcessTransfer' VALUE='Process Inventory Transfer'><BR />
		</form>
		</CENTER>";

} else { /*Not $_SESSION['Transfer'] set */

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

		echo "<CENTER><TABLE BORDER=0>";

		echo "<TR><TD class='tableheader'>Transfer Ref</TD><TD class='tableheader'>Transfer From</TD><TD class='tableheader'>Dispatch Date</TD></TR>";

		while ($myrow=DB_fetch_array($TrfResult)){

			echo "<TR><TD ALIGN=RIGHT>" . $myrow['Reference'] . "</TD><TD>" . $myrow['TrfFromLoc'] . "</TD><TD>" . ConvertSQLDate($myrow['ShipDate']) . "</TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Trf_ID=" . $myrow['Reference'] . "'>Receive</A></TD></TR>";

		}

		echo "</table></CENTER>";
	}
	echo "</FORM>";
}
include("includes/footer.inc");
?>
