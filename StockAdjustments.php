<?php
$title = "Stock Adjustments";

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

$result = DB_query("SELECT Description, Units, MBflag, Materialcost+Labourcost+Overheadcost AS StandardCost FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);

if (DB_num_rows($result)>0){

	$PartDescription = $myrow[0];
	$PartUnit = $myrow[1];
	$StandardCost = $myrow[3];

	if ($myrow[2]=="D" OR $myrow[2]=="A" OR $myrow[2]=="K"){
		echo "<P>The part entered is either or a dummy part or an assembly/kit-set part. These parts are not physical parts and no stock holding is maintained for them. Stock adjustments are therefore not possible.<HR>";
		echo "<A HREF='$rootpath/StockAdjustments.php?" . SID ."'>Enter another adjustment</A>";
		exit;
	}
}

if ($_POST['EnterAdjustment']=="Enter Stock Adjustment"){

	$result = DB_query("SELECT * FROM StockMaster WHERE StockID='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		echo "<P>The entered item code does not exist.";
	} elseif (!is_numeric($_POST['Quantity'])){
		echo "<P>The quantity entered must be numeric";
	} elseif ($_POST['Quantity']==0){
		echo "<P>The quantity entered cannot be zero! There would be no adjustment to make";
	} else {

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


		$SQL = "UPDATE LocStock SET Quantity = Quantity + " . $_POST['Quantity'] . " WHERE StockID='" . $StockID . "' AND LocCode='" . $_POST['StockLocation'] . "'";
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

		if ($CompanyRecord["GLLink_Stock"]==1 AND $StandardCost > 0){

			$StockGLCodes = GetStockGLCode($StockID,$db);

			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Amount, Narrative) VALUES (17," .$AdjustmentNumber . ", '" . $SQLAdjustmentDate . "', " . $PeriodNo . ", " .  $StockGLCodes['AdjGLAct'] . ", " . $StandardCost * -($_POST['Quantity']) . ", '" . $StockID . " x " . $_POST['Quantity'] . " @ " . $StandardCost . " - " . $_POST['Narrative'] . "')";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction entries could not be added because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GL entries was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}

			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Amount, Narrative) VALUES (17," .$AdjustmentNumber . ", '" . $SQLAdjustmentDate . "', " . $PeriodNo . ", " .  $StockGLCodes['StockAct'] . ", " . $StandardCost * $_POST['Quantity'] . ", '" . $StockID . " x " . $_POST['Quantity'] . " @ " . $StandardCost . " - " . $_POST['Narrative'] . "')";
			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction entries could not be added because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GL entries was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}

		}


		$SQL = "Commit";
		$Result = DB_query($SQL,$db);

		echo "<P>A stock Adjustment of $StockID - $PartDescription has been created for " . $_POST['StockLocation'] ." for a quantity of " . $_POST['Quantity'];
		unset ($_POST['StockID']);
		unset ($StockID);
		unset ($PartDescription);
		unset ($StandardCost);
		unset ($PartUnit);
		unset ($_POST['Quantity']);
		unset ($_POST['Narrative']);
	}

}



echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><TABLE><TR><TD>Stock Code:</TD><TD><input type=text name='StockID' size=21 value='$StockID' maxlength=20> <INPUT TYPE=SUBMIT NAME='CheckCode' VALUE='Check Part'></TD></TR>";

if (strlen($PartDescription)>1){
	echo "<TR><TD COLSPAN=3><FONT COLOR=BLUE SIZE=3>" . $PartDescription . " (In Units of " . $PartUnit . " ) - Unit Cost = " . $StandardCost . "</FONT></TD></TR>";
}

echo "<TR><TD>Adjustment to stock location:</TD><TD><SELECT name='StockLocation'> ";

$sql = "SELECT LocCode, LocationName FROM Locations";
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation'])){
		if ($myrow["LocCode"] == $_POST['StockLocation']){
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

echo "<TR><TD>Comments on why:</TD><TD><input type=text name='Narrative' size=32 maxlength=30 value='" . $_POST['Narrative'] . "'></TD></TR>";

echo "<TR><TD>Adjustment Quantity:</TD><TD><INPUT TYPE=TEXT NAME='Quantity' SIZE=12 MAXLENGTH=12 Value='" . $_POST['Quantity'] . "'></TD></TR>";


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
