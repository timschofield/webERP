<?php
/* $Revision: 1.2 $ */
/* Script to delete all supplier payments entered or created from a payment run on a specified day
 */
$title = "Reverse and Delete Supplier Payments";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");
include ("includes/DateFunctions.inc");


/*Only do deletions if user hits the button */
if ($_POST['RevPayts']=="Reverse Supplier Payments on the Date Entered" AND Is_Date($_POST["PaytDate"])==1){

	$SQLTranDate = FormatDateForSQL($_POST["PaytDate"]);

	$SQL = "SELECT TransNo, SupplierNo, OvAmount, SuppReference, Rate FROM SuppTrans WHERE Type = 22 AND TranDate = '" . $SQLTranDate . "'";

	$Result = DB_query($SQL,$db);
	echo "<BR>The number of payments that will be deleted is :" . DB_num_rows($Result);

	while ($Payment = DB_fetch_array($Result)){
		echo "<BR>Deleting payment number " . $Payment['TransNo'] . " to supplier code " . $Payment['SupplierNo'] . " for an amount of " . $Payment['OvAmount'];

		$SQL = "DELETE FROM SuppTrans WHERE Type=22 AND TransNo=" . $Payment['TransNo'] . " AND TranDate='" . $SQLTranDate . "'";
		$DelResult = DB_query($SQL,$db);
		echo "<BR>Deleted the SuppTran record";


		$SQL = "SELECT TransNo, TypeNo, Amt FROM SuppAllocs WHERE PaytNo = " .  $Payment['TransNo'] . " AND PaytTypeNo=22";
		$AllocsResult = DB_query($SQL,$db);
		while ($Alloc = DB_fetch_array($AllocsResult)){

			$SQL= "UPDATE SuppTrans SET Settled=0, Alloc=Alloc-" . $Alloc["Amt"] . ", DiffOnExch = DiffOnExch - ((" . $Alloc["Amt"] . "/Rate ) - " . $Alloc["Amt"]/$Payment["Rate"] . ") WHERE Type=" . $Alloc["TypeNo"] . " AND TransNo=" . $Alloc['TransNo'];
			$UpdResult = DB_query($SQL,$db);
			if (DB_error_no($db) !=0) {

				echo "<BR>The update to the suppliers charges that were settled by the payment failed because - " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The SQL that failed was $SQL";
				}
			}
		}

		echo " ... reversed the allocations";
		$SQL= "Delete FROM SuppAllocs WHERE PaytNo=" . $Payment['TransNo'] . " AND PaytTypeNo=22";
		$DelResult = DB_query($SQL,$db);
		echo " ... deleted the SuppAllocs records";

		$SQL= "Delete FROM GLTrans WHERE TypeNo=" . $Payment['TransNo'] . " AND Type=22";
		$DelResult = DB_query($SQL,$db);
		echo " .... the GLTrans records (if any)";

		$SQL= "Delete FROM BankTrans WHERE Ref='" . $Payment['SuppReference'] . " " . $Payment['SupplierNo'] . "' AND Amount=" . $Payment['OvAmount'] . " AND TransDate = '" . $SQLTranDate . "'";
		$DelResult = DB_query($SQL,$db);
		echo " .... and the BankTrans record";

	}


}


echo "<FORM METHOD=POST ACTION='" . $_SERVER["PHP_SELF"] . "?" . SID . "'>";
echo "<INPUT TYPE=text name='PaytDate' maxlength=11 size=11 value='" . $_POST['PaytDate'] . "'>";
echo "<INPUT TYPE=submit name='RevPayts' value='Reverse Supplier Payments on the Date Entered'>";
echo "</FORM>";

include("includes/footer.inc");
?>
