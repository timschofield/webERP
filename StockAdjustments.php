<?php
/* $Revision: 1.3 $ */
$title = "Stock Adjustments";

$PageSecurity = 11;


include("includes/DefineStockAdjustmentClass.php");
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

if (isset($_GET['NewAdjustment'])){
     unset($_SESSION['Adjustment']);
     $_SESSION['Adjustment'] = new StockAdjustment;
}

if (!isset($_SESSION['Adjustment'])){
     $_SESSION['Adjustment'] = new StockAdjustment;
}

$NewAdjustment = false;

if (isset($_GET['StockID'])){
	$_SESSION['Adjustment']->StockID =$_GET['StockID'];
	$NewAdjustment = true;
} elseif (isset($_POST['StockID'])){
	if ($_POST['StockID'] != $_SESSION['Adjustment']->StockID){
		$NewAdjustment = true;
		$_SESSION['Adjustment']->StockID =$_POST['StockID'];
	} else {
		$_SESSION['Adjustment']->Narrative = $_POST['Narrative'];
		$_SESSION['Adjustment']->StockLocation = $_POST['StockLocation'];
		$_SESSION['Adjustment']->Quantity = $_POST['Quantity'];
	}
}

if ($NewAdjustment){

	$result = DB_query("SELECT Description, Units, MBflag, Materialcost+Labourcost+Overheadcost AS StandardCost, Controlled, Serialised, DecimalPlaces FROM StockMaster WHERE StockID='" . $_SESSION['Adjustment']->StockID . "'",$db);
	$myrow = DB_fetch_row($result);

	if (DB_num_rows($result)>0){

		$_SESSION['Adjustment']->PartDescription = $myrow[0];
		$_SESSION['Adjustment']->PartUnit = $myrow[1];
		$_SESSION['Adjustment']->StandardCost = $myrow[3];
		$_SESSION['Adjustment']->Controlled = $myrow[4];
		$_SESSION['Adjustment']->Serialised = $myrow[5];
		$_SESSION['Adjustment']->DecimalPlaces = $myrow[6];

		if ($myrow[2]=="D" OR $myrow[2]=="A" OR $myrow[2]=="K"){
			echo "<P>The part entered is either or a dummy part or an assembly/kit-set part. These parts are not physical parts and no stock holding is maintained for them. Stock adjustments are therefore not possible.<HR>";
			echo "<A HREF='$rootpath/StockAdjustments.php?" . SID ."'>Enter another adjustment</A>";
			unset ($_SESSION['Adjustment']);
			include ("includes/footer.inc");
			exit;
		}
	}
}

if ($_POST['EnterAdjustment']=="Enter Stock Adjustment"){

	$InputError = false; /*Start by hoping for the best */
	$result = DB_query("SELECT * FROM StockMaster WHERE StockID='" . $_SESSION['Adjustment']->StockID . "'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		echo "<P>The entered item code does not exist.";
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Adjustment']->Quantity)){
		echo "<P>The quantity entered must be numeric";
		$InputError = true;
	} elseif ($_SESSION['Adjustment']->Quantity==0){
		echo "<P>The quantity entered cannot be zero! There would be no adjustment to make";
		$InputError = true;
	} elseif ($_SESSION['Adjustment']->Controlled==1 AND count($_SESSION['Adjustment']->SerialItems)==0) {
		echo "<P>The item entered is a controlled item that requires the detail of the serial numbers (or batch references) to be adjusted, to be entered.";
		$InputError = true;
	}

	if (!$InputError) {

/*All inputs must be sensible so make the stock movement records and update the locations stocks */


		$AdjustmentNumber = GetNextTransNo(17,$db);
		$PeriodNo = GetPeriod (Date($DefaultDateFormat), $db);
		$SQLAdjustmentDate = FormatDateForSQL(Date($DefaultDateFormat));
		$CompanyRecord = ReadInCompanyRecord($db);


		$SQL = "BEGIN";
		$Result = DB_query($SQL,$db);

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $StockID . "' AND LocCode= '" . $_POST['StockLocation'] . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $StockID . "', 17, " . $AdjustmentNumber . ", '" . $_POST['StockLocation'] . "','" . $SQLAdjustmentDate . "'," . $PeriodNo . ", '" . $_POST['Narrative'] ."', " . $_POST['Quantity'] . ", " . ($QtyOnHandPrior + $_POST['Quantity']) . ")";


		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because:";
		$DbgMsg = "<BR>The following SQL to insert the stock movement record was used:";
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


		$SQL = "UPDATE LocStock SET Quantity = Quantity + " . $_POST['Quantity'] . " WHERE StockID='" . $StockID . "' AND LocCode='" . $_POST['StockLocation'] . "'";

		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because:";
		$DbgMsg = "<BR>The following SQL to update the stock record was used:";

		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		if ($CompanyRecord["GLLink_Stock"]==1 AND $StandardCost > 0){

			$StockGLCodes = GetStockGLCode($StockID,$db);

			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Amount, Narrative) VALUES (17," .$AdjustmentNumber . ", '" . $SQLAdjustmentDate . "', " . $PeriodNo . ", " .  $StockGLCodes['AdjGLAct'] . ", " . $StandardCost * -($_POST['Quantity']) . ", '" . $StockID . " x " . $_POST['Quantity'] . " @ " . $StandardCost . " - " . $_POST['Narrative'] . "')";

			$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction entries could not be added because: ";
			$DbgMsg = "<BR>The following SQL to insert the GL entries was used:";
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Amount, Narrative) VALUES (17," .$AdjustmentNumber . ", '" . $SQLAdjustmentDate . "', " . $PeriodNo . ", " .  $StockGLCodes['StockAct'] . ", " . $StandardCost * $_POST['Quantity'] . ", '" . $StockID . " x " . $_POST['Quantity'] . " @ " . $StandardCost . " - " . $_POST['Narrative'] . "')";

			$Errmsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction entries could not be added because:";
			$DbgMsg = "<BR>The following SQL to insert the GL entries was used:";
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

		}

		$Result = DB_query("Commit",$db);

		echo "<P>A stock Adjustment of $StockID - $PartDescription has been created for " . $_POST['StockLocation'] ." for a quantity of " . $_POST['Quantity'];
		unset ($_SESSION['Adjustment']);
		unset ($_POST['Quantity']);
	} /* end if there was no input error */

}/* end if the user hit enter the adjustment */



echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><TABLE><TR><TD>Stock Code:</TD><TD><input type=text name='StockID' size=21 value='" . $_SESSION['Adjustment']->StockID . "' maxlength=20> <INPUT TYPE=SUBMIT NAME='CheckCode' VALUE='Check Part'></TD></TR>";

if (strlen($PartDescription)>1){
	echo "<TR><TD COLSPAN=3><FONT COLOR=BLUE SIZE=3>" . $_SESSION['Adjustment']->PartDescription . " (In Units of " . $_SESSION['Adjustment']->PartUnit . " ) - Unit Cost = " . $_SESSION['Adjustment']->StandardCost . "</FONT></TD></TR>";
}

echo "<TR><TD>Adjustment to stock location:</TD><TD><SELECT name='StockLocation'> ";

$sql = "SELECT LocCode, LocationName FROM Locations";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['Adjustment']->StockLocation)){
		if ($myrow["LocCode"] == $_SESSION['Adjustment']->StockLocation){
		     echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
		     echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	} elseif ($myrow["LocCode"]==$_SESSION['UserStockLocation']){
		 echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		 $_POST['StockLocation']=$myrow["LocCode"];
	} else {
		 echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
	}
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>Comments on why:</TD><TD><input type=text name='Narrative' size=32 maxlength=30 value='" . $_SESSION['Adjustment']->Narrative . "'></TD></TR>";

echo "<TR><TD>Adjustment Quantity:</TD>";


if ($_SESSION['Adjustment']->Controlled==1){

		echo "<TD><INPUT TYPE=HIDDEN NAME='Quantity' Value='" . $_SESSION['Adjustment']->Quantity . "'><A HREF='$rootpath/StockAdjustmentsControlled.php?" . SID . "'>" . $_SESSION['Adjustment']->Quantity . "</TD></TR>";

} else {
	echo "<TD><INPUT TYPE=TEXT NAME='Quantity' SIZE=12 MAXLENGTH=12 Value='" . $_SESSION['Adjustment']->Quantity . "'></TD></TR>";
}


echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='EnterAdjustment' VALUE='Enter Stock Adjustment'>";
echo "<HR>";


echo "<A HREF='$rootpath/StockStatus.php?" . SID . "StockID=$StockID'>Show Stock Status</A>";
echo "<BR><A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>Show Movements</A>";
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Show Stock Usage</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";


echo "</form>";

include("includes/footer.inc");

?>
