<?php
/* $Revision: 1.3 $ */
/* contributed by Chris Bice */

$title = "Inventory Location Transfer Shipment";
$PageSecurity = 11;
include("includes/session.inc");
include("includes/header.inc");
include("includes/SQL_CommonFunctions.inc");

If (isset($_POST['submit']) OR isset($_POST['EnterMoreItems'])){
/*Trap any errors in input */

	$InputError = False; /*Start off hoping for the best */

	for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){

		if ($_POST['StockID' . $i]!=""){
			$result = DB_query("SELECT COUNT(StockID) FROM StockMaster WHERE StockID='" . $_POST['StockID' . $i] . "'",$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]==0){
				$InputError = True;
				$ErrorMessage .= "The part code entered of " . $_POST['StockID' . $i] . " is not set up in the database. Only valid parts can be entered for transfers<BR>";
				$_POST['LinesCounter'] -= 10;
			}
			if (!is_numeric($_POST['StockQTY' . $i])){
				$InputError = True;
				$ErrorMessage .= "The quantity entered of " . $_POST['StockQTY' . $i] . " for part code " . $_POST['StockID' . $i] . " is not numeric. The quantity entered for transfers is expected to be numeric<BR>";
				$_POST['LinesCounter'] -= 10;
			}
		}
	}

/*Ship location and Receive location are different */
	If ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
		$InputError=True;
		$ErrorMessage .= "The transfer must have a different location to receive into and location sent from";
	}
}

if(isset($_POST['submit']) AND $InputError==False){

	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if($_POST['StockID' . $i] != ""){
			$sql = "INSERT INTO LocTransfers (Reference, StockID, ShipQty, ShipDate, ShipLoc, RecLoc) VALUES ('" . $_POST['Trf_ID'] . "', '" . $_POST['StockID' . $i] . "', '" . $_POST['StockQTY' . $i] . "', '" . Date("Y-m-d") . "', '" . $_POST['FromStockLocation']  ."', '" . $_POST['ToStockLocation'] . "')";
			$resultLocShip = DB_query($sql,$db);
		}
	}
	echo "<P>The inventory transfer record(s) have been created successfully.";
	echo "<P><A HREF='$rootpath/PDFStockLocTransfer.php?" . SID . "TransferNo=" . $_POST['Trf_ID'] . "'>Print the Transfer Docket</A>";


} else {
	//Get next Inventory Transfer Shipment Reference Number

	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if(!isset($Trf_ID)){
		$Trf_ID = GetNextTransNo(16,$db);
	}

	If ($InputError==true){
		echo "<BR>$ErrorMessage<BR>";
	}

	echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";

	echo "<input type=HIDDEN NAME='Trf_ID' VALUE=" . $Trf_ID . "><h2>Inventory Location Transfer Shipment Reference # $Trf_ID</h2>";

	$sql = "SELECT LocCode, LocationName FROM Locations";
	$resultStkLocs = DB_query($sql,$db);
	echo "From Stock Location:<SELECT name='FromStockLocation'>";
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
	echo "</SELECT>";

	DB_data_seek($resultStkLocs,0); //go back to the start of the locations result
	echo " To Stock Location:<SELECT name='ToStockLocation'>";
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
	echo "</SELECT><BR>";

	echo "<CENTER><TABLE>";

	$tableheader = "<TR><TD class='tableheader'>Item Code</TD><TD class='tableheader'>Quantity</TD></TR>";
	echo $tableheader;

	$k=0; /* row counter */
	if(isset($_POST['LinesCounter'])){

		for ($i=0;$i < $_POST['LinesCounter'] AND $_POST['StockID' . $i] !="";$i++){

			if ($k==18){
				echo $tableheader;
				$k=0;
			}
			echo "<TR><td><input type=text name='StockID" . $i ."' size=21  maxlength=20 Value=" . $_POST['StockID' . $i] . "></td><td><input type=text name='StockQTY" . $i ."' size=5 maxlength=4 Value=" . $_POST['StockQTY' . $i] . "></td></tr>";
		}
	}else {
		$i = 0;
	}
	$z=($i + 10);

	while($i < $z) {
		echo "<TR><td><input type=text name='StockID" . $i ."' size=21  maxlength=20 Value=" . $_POST['StockID' . $i] . "></td><td><input type=text name='StockQTY" . $i ."' size=5 maxlength=4 Value=" . $_POST['StockQTY' . $i] . "></td></tr>";
		$i++;
	}

	echo "</table><br><input type=hidden name='LinesCounter' value=$i><INPUT TYPE=SUBMIT NAME='EnterMoreItems' VALUE='Add More Items'><INPUT TYPE=SUBMIT NAME='submit' VALUE='Create Transfer Shipment'><BR><HR>";
	echo "</FORM></CENTER>";
	include("includes/footer.inc");
}
?>
