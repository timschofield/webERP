<?php
/* $Revision: 1.2 $ */
$title = "Stock Check Sheets Entry";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo "<CENTER>Stock Check Counts at Location:<SELECT NAME='Location'>";
$sql = "SELECT LocCode, LocationName FROM Locations";
$result = DB_query($sql,$db);

while ($myrow=DB_fetch_array($result)){

	if ($myrow['LocCode']==$_POST['Location']){
		echo "<OPTION SELECTED VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
	} else {
		echo "<OPTION VALUE='" . $myrow['LocCode'] . "'>" . $myrow['LocationName'];
	}
}

echo "</SELECT>";

echo "<TABLE CELLPADDING=2 BORDER=1>";
echo "<TR><TD class='tableheader'>Stock Code</TD><TD class='tableheader'>Quantity</TD><TD class='tableheader'>Reference</TD></TR>";

for ($i=1;$i<=10;$i++){

	echo "<TR><TD><INPUT TYPE=TEXT NAME='StockID_" . $i . "' MAXLENGTH=20 SIZE=20></TD><TD><INPUT TYPE=TEXT NAME='Qty_" . $i . "' MAXLENGTH=10 SIZE=10></TD><TD><INPUT TYPE=TEXT NAME='Ref_" . $i . "' MAXLENGTH=20 SIZE=20></TD></TR>";

}

echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='EnterCounts' VALUE='Enter Above Counts'>";



if (isset($_POST['EnterCounts'])){

	for ($i=1;$i<=10;$i++){
		$InputError =False; //always assume the best to start with

		$Quantity = "Qty_" . $i;
		$StockID = "StockID_" . $i;
		$Reference = "Ref_" . $i;

		if (strlen($_POST[$StockID])>0){
			if (!is_numeric($_POST[$Quantity])){
				echo "<BR>The quantity entered for line $i is not numeric - this line was for the part code " . $_POST[$StockID] . ". This line will have to be re-entered.";
				$InputError=True;
			}
			$SQL = "SELECT StockID FROM StockCheckFreeze WHERE StockID='" . $_POST[$StockID] . "'";
			$result = DB_query($SQL,$db);
			if (DB_num_rows($result)==0){
				echo "<BR>The stock code entered on line $i is not a part code that has been added to the stock check file - the code entered was " . $_POST[$StockID] . ". This line will have to be re-entered.";
				$InputError = True;
			}

			if ($InputError==False){
				$sql = "INSERT INTO StockCounts (StockID, LocCode, QtyCounted, Reference) VALUES ('" . $_POST[$StockID] . "', '" . $_POST['Location'] . "', " . $_POST[$Quantity] . ", '" . $_POST[$Reference] . "')";

				$EnterResult = DB_query($sql, $db);
				if (DB_error_no($db) !=0) {
					echo "<BR>The stock count line number $i could not be entered because - " . DB_error_msg($db) . ". This line will need to be re-entered";
					if ($debug==1){
						echo "<BR>The SQL that failed was $sql";
					}
				}
			}
		}
	} // end of loop
} // end of if enter counts button hit
echo "</FORM></CENTER>";
include("includes/footer.inc");

?>
