<?php
/* $Revision: 1.4 $ */
$title = "Stock Transfers";

$PageSecurity = 11;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

$result = DB_query("SELECT Description, Units, MBflag FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);

if (DB_num_rows($result)>0){
	$PartDescription = $myrow[0];
	$PartUnit = $myrow[1];

	if ($myrow[2]=="D" OR $myrow[2]=="A" OR $myrow[2]=="K"){
		echo "<P>The part entered is either or a dummy part or an assembly/kit-set part. These parts are not physical parts and no stock holding is maintained for them. Stock transfers are therefore not possible.<HR>";
		echo "<A HREF='$rootpath/StockTransfers.php?" . SID ."'>Enter another transfer</A>";
		exit;
	}
}

if ($_POST['EnterTransfer']=="Enter Stock Transfer"){

	$result = DB_query("SELECT * FROM StockMaster WHERE StockID='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		echo "<P>The entered item code does not exist.";
	} elseif (!is_numeric($_POST['Quantity'])){
		echo "<P>The quantity entered must be numeric.";
	} elseif ($_POST['Quantity']<=0){
		echo "<P>The quantity entered must be a positive number greater than zero.";
	} elseif (!Is_Date($_POST['TransferDate'])){
		echo "<P>The transfer date entered must be in the format $DefaultDateFormat.";
	} elseif ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
		echo "<P>The locations to transfer from and to must be different.";
	} else {

/*All inputs must be sensible so make the stock movement records and update the locations stocks */


		$TransferNumber = GetNextTransNo(16,$db);
		$PeriodNo = GetPeriod ($_POST['TransferDate'], $db);
		$SQLTransferDate = FormatDateForSQL($_POST['TransferDate']);

		$SQL = "BEGIN";
		$Result = DB_query($SQL,$db);

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $StockID . "' AND LocCode= '" . $_POST['FromStockLocation'] . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		// Insert the stock movement for the stock going out of the from location
		$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $StockID . "', 16, " . $TransferNumber . ", '" . $_POST['FromStockLocation'] . "','" . $SQLTransferDate . "'," . $PeriodNo . ", 'To " . $_POST['ToStockLocation'] ."', " . -$_POST['Quantity'] . ", " . ($QtyOnHandPrior - $_POST['Quantity']) . ")";

		$Result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to insert the stock movement record was used:<BR>$SQL<BR>";
			}
			$SQL = "Rollback";
			$Result = DB_query($SQL,$db);
			exit;
		}

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $StockID . "' AND LocCode= '" . $_POST['ToStockLocation'] . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		// Insert the stock movement for the stock coming into the to location
		$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $StockID . "',16, " . $TransferNumber . ", '" . $_POST['ToStockLocation'] . "','" . $SQLTransferDate . "'," . $PeriodNo . ", 'From " . $_POST['FromStockLocation'] ."', " . $_POST['Quantity'] . ", " . ($QtyOnHandPrior + $_POST['Quantity']) . ")";

		$Result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to insert the stock movement record was used:<BR>$SQL<BR>";
			}
			$SQL = "Rollback";
			$Result = DB_query($SQL,$db);
			exit;
		}

		$SQL = "UPDATE LocStock SET Quantity = Quantity - " . $_POST['Quantity'] . " WHERE StockID='" . $StockID . "' AND LocCode='" . $_POST['FromStockLocation'] . "'";
		$Result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to update the stock record was used:<BR>$SQL<BR>";
			}
			$SQL = "Rollback";
			$Result = DB_query($SQL,$db);
			exit;
		}

		$SQL = "UPDATE LocStock SET Quantity = Quantity + " . $_POST['Quantity'] . " WHERE StockID='" . $StockID . "' AND LocCode='" . $_POST['ToStockLocation'] . "'";
		$Result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to update the stock record was used:<BR>$SQL<BR>";
			}
			$SQL = "Rollback";
			$Result = DB_query($SQL,$db);
			exit;
		}

		$SQL = "Commit";
		$Result = DB_query($SQL,$db);

		echo "<P>A stock transfer of $StockID - $PartDescription has been created from " . $_POST['FromStockLocation'] . " to " . $_POST['ToStockLocation'] . " for a quantity of " . $_POST['Quantity'];
		unset ($_POST['StockID']);
		unset ($StockID);
		unset ($_POST['Quantity']);
		unset ($_POST['TransferDate']);
	}

}



echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><TABLE><TR><TD>Stock Code:</TD><TD><input type=text name='StockID' size=21 value='$StockID' maxlength=20></TD><TD><INPUT TYPE=SUBMIT NAME='CheckCode' VALUE='Check Part'></TR>";

if (strlen($PartDescription)>1){
	echo "<TR><TD COLSPAN=3><FONT COLOR=BLUE SIZE=3>" . $PartDescription . " (In Units of " . $PartUnit . " )</FONT></TD></TR>";
}

echo "<TR><TD>From Stock Location: </TD><TD><SELECT name='FromStockLocation'> ";

$sql = "SELECT LocCode, LocationName FROM Locations";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['FromStockLocation'])){
		if ($myrow["LocCode"] == $_POST['FromStockLocation']){
		     echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
		     echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	} elseif ($myrow["LocCode"]==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		 $_POST['FromStockLocation']=$myrow["LocCode"];
	} else {
		 echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
	}
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>To Stock Location: </TD><TD><SELECT name='ToStockLocation'> ";

DB_data_seek($resultStkLocs,0);

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['ToStockLocation'])){
		if ($myrow["LocCode"] == $_POST['ToStockLocation']){
		     echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
		     echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	} elseif ($myrow["LocCode"]==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		 $_POST['ToStockLocation']=$myrow["LocCode"];
	} else {
		 echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
	}
}

echo "</SELECT></TD></TR>";


if (!isset($_POST['TransferDate'])){
   $_POST['TransferDate'] = Date($DefaultDateFormat);
}
echo "<TR><TD>Date Transfer Received:</TD><TD><INPUT TYPE=TEXT NAME='TransferDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['TransferDate'] . "'></TD></TR>";
echo "<TR><TD>Transfer Quantity:</TD><TD><INPUT TYPE=TEXT NAME='Quantity' SIZE=12 MAXLENGTH=12 Value='" . $_POST['Quantity'] . "'></TD></TR>";


echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='EnterTransfer' VALUE='Enter Stock Transfer'>";
echo "<HR>";


echo "<A HREF='$rootpath/StockStatus.php?" . SID . "StockID=$StockID'>Show Stock Status</A>";
echo "<BR><A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>Show Movements</A>";
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Show Stock Usage</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";


echo "</form>";
include("includes/footer.inc");

?>
