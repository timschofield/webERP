<?php
/* $Revision: 1.2 $ */
$title = "Stock Cost Update";

$PageSecurity = 2; /*viewing possible with inquiries but not mods */

$UpdateSecurity =10;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

if (isset($_POST['UpdateData'])){

   	$OldCost =$_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldLabourCost'];
   	$NewCost =$_POST['MaterialCost'] + $_POST['LabourCost'] + $_POST['OverheadCost'];

	$result = DB_query("SELECT * FROM StockMaster WHERE StockID='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		echo "<P>The entered item code does not exist.";
	} elseif ($OldCost != $NewCost AND $_POST['QOH']!=0){

		$SQL = "Begin";
		$Result = DB_query($SQL,$db);

		$CompanyRecord = ReadInCompanyRecord($db);
		if ($CompanyRecord["GLLink_Stock"]==1){

			$CostUpdateNo = GetNextTransNo(35, $db);
			$PeriodNo = GetPeriod(Date("d/m/Y"), $db);
			$StockGLCode = GetStockGLCode($StockID,$db);

			$ValueOfChange = $_POST['QOH'] * ($NewCost - $OldCost);

			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (35, " . $CostUpdateNo . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCode["AdjGLAct"] . ", '" . $StockID . " cost was " . $OldCost . " changed to " . $NewCost . " x Quantity on hand of " . $_POST['QOH'] . "', " . (-$ValueOfChange) . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL credit for the stock cost adjustment posting could not be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);

				include ("includes/footer.inc");
				exit;

			}

			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (35, " . $CostUpdateNo . ", '" . Date('Y-m-d') . "', " . $PeriodNo . ", " . $StockGLCode["StockAct"] . ", '" . $StockID . " cost was " . $OldCost . " changed to " . $NewCost . " x Quantity on hand of " . $_POST['QOH'] . "', " . $ValueOfChange . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The GL debit for stock cost adjustment posting could not be inserted because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);

				exit;
			}

		}

		$sql = "UPDATE StockMaster SET MaterialCost=" . $_POST['MaterialCost'] . ", LabourCost=" . $_POST['LabourCost'] . ", OverheadCost=" . $_POST['OverheadCost'] . ", LastCost=" . $OldCost . " WHERE StockID='" . $StockID . "'";
		$result = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "The cost details for the stock item could not be updated because - " . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The SQL that failed was $sql";
			}
			$SQL = "rollback";
			$Result = DB_query($SQL,$db);
			exit;
		} else {
			$SQL = "commit";
			$Result = DB_query($SQL,$db);
		}
   	}
}

$result = DB_query("SELECT Description, Units, LastCost, ActualCost, MaterialCost, LabourCost, OverheadCost, MBflag, Sum(Quantity) AS TotalQOH FROM StockMaster INNER JOIN LocStock ON StockMaster.StockID=LocStock.StockID WHERE StockMaster.StockID='$StockID' GROUP BY Description, Units, LastCost, ActualCost, MaterialCost, LabourCost, OverheadCost, MBflag",$db);
if (DB_error_no($db) !=0) {
	echo "The cost details for the stock item could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
}
$myrow = DB_fetch_array($result);
echo "<BR><FONT COLOR=BLUE SIZE=3><B>" . $StockID . " - " . $myrow["Description"] . "</B>	 - Total Quantity On Hand:	" . $myrow["TotalQOH"] . " " . $myrow["Units"] ."</FONT>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID ."' METHOD=POST>";
echo "Stock Code:<input type=text name='StockID' value='$StockID' 1 maxlength=20>";

echo "     <INPUT TYPE=SUBMIT NAME='Show' VALUE='Show Cost Details'><HR>";

if ($myrow["MBflag"]=='D' OR $myrow["MBflag"]=='A' OR $myrow["MBflag"]=='K'){

   if ($myrow["MBflag"]=='D'){
   	echo "<BR$StockID is a dummy part";
   } else if ($myrow["MBflag"]=='A'){
   	echo "<BR>$StockID is an assembly part";
   } else if ($myrow["MBflag"]=='K'){
   	echo "<BR>$StockID is a kit set part";
   }
   echo "<BR>Cost information cannot be modified for kits, assemblies or dummy parts. Please select a different part.";
   exit;
}

echo "<INPUT TYPE=HIDDEN NAME=OldMaterialCost VALUE=" . $myrow["MaterialCost"] .">";
echo "<INPUT TYPE=HIDDEN NAME=OldLabourCost VALUE=" . $myrow["LabourCost"] .">";
echo "<INPUT TYPE=HIDDEN NAME=OldOverheadCost VALUE=" . $myrow["OverheadCost"] .">";
echo "<INPUT TYPE=HIDDEN NAME=QOH VALUE=" . $myrow["TotalQOH"] .">";

echo "<CENTER><TABLE CELLPADDING=2 BORDER=2>";
echo "<TR><TD>Last Cost:</TD><TD ALIGN=RIGHT>" . number_format($myrow["LastCost"],2) . "</TD></TR>";
if (! in_array($UpdateSecurity,$SecurityGroups[$_SESSION["AccessLevel"]]) OR !isset($UpdateSecurity)){
	echo "<TR><TD>Cost:</TD><TD ALIGN=RIGHT>" . number_format($myrow["MaterialCost"]+$myrow["LabourCost"]+$myrow["OverheadCost"],2) . "</TD></TR></TABLE>";
} else {
	echo "<TR><TD>Standard Material Cost Per Unit:</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME=MaterialCost VALUE=" . $myrow["MaterialCost"] . "></TD></TR>";

	if ($myrow["MBflag"]=='M'){
		echo "<TR><TD>Standard Labour Cost Per Unit:</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME=LabourCost VALUE=" . $myrow["LabourCost"] . "></TD></TR>";
		echo "<TR><TD>Standard Overhead Cost Per Unit:</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME=OverheadCost VALUE=" . $myrow["OverheadCost"] . "></TD></TR>";
	} else {
		echo "<INPUT TYPE=HIDDEN NAME=LabourCost VALUE=0>";
		echo "<INPUT TYPE=HIDDEN NAME=OverheadCost VALUE=0>";
	}

echo "</TABLE><INPUT TYPE=SUBMIT NAME='UpdateData' VALUE='Update'><HR>";
}
echo "<A HREF='$rootpath/StockStatus.php?" . SID . "StockID=$StockID'>Show Stock Status</A>";
echo "<BR><A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>Show Stock Movements</A>";
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID'>Show Stock Usage</A>";
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Outstanding Sales Orders</A>";
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";

echo "</form>";

include("includes/footer.inc");

?>
