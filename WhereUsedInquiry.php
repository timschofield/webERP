<?php

$title = "Where Used Inquiry";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

if (isset($StockID)){
	$result = DB_query("SELECT Description, Units, MBflag FROM StockMaster WHERE StockID='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0){
		echo "<BR>The item code entered - $StockID is not set up as an item in the system. Re-enter a valid item code or select from the Select Item link above.";
		exit;
	}
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (in units of $myrow[1])</FONT>";
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID ."' METHOD=POST>";
echo "Enter an Item Code: <input type=text name='StockID' size=21 maxlength=20 value='$StockID' >";
echo "<INPUT TYPE=SUBMIT NAME='ShowWhereUsed' VALUE='Show Where Used'>";

echo "<HR>";

if (isset($StockID)) {

    $SQL = "SELECT BOM.*, StockMaster.Description FROM BOM INNER JOIN StockMaster ON BOM.Parent = StockMaster.StockID WHERE Component='" . $StockID . "' AND BOM.EffectiveAfter<='" . Date("Y-m-d") . "' AND BOM.EffectiveTo >='" . Date("Y-m-d") . "'";

    $result = DB_query($SQL,$db);
    if (DB_error_no($db) !=0) {
		echo "<BR>The parents for the selected part could not be retrieved - there error was " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The SQL that failed was :<BR>" . $SQL;
    		}
    }
    if (DB_num_rows($result)==0){

	echo "<BR>The selected item $StockID is not used as a component of any other parts";

    } else {

    	echo "<TABLE WIDTH=100%>";

    	$tableheader = "<TR><TD class='tableheader'>Used By</TD><TD class='tableheader'>Work Centre</TD><TD class='tableheader'>Location</TD><TD class='tableheader'>Quantity Required</TD><TD class='tableheader'>Effective After</TD><TD class='tableheader'>Effective To</TD></TR>";
    	echo $tableheader;
	$k=0;
    	while ($myrow=DB_fetch_array($result)) {

    		if ($k==1){
    			echo "<tr bgcolor='#CCCCCC'>";
    			$k=0;
    		} else {
    			echo "<tr bgcolor='#EEEEEE'>";
    			$k=1;
    		}
		$k++;



    		echo "<td><A target='_blank' HREF='" . $rootpath . "/BOMInquiry.php?" . SID . "StockID=" . $myrow["Parent"] . "' ALT='Show Bill Of Material'>" . $myrow["Parent"]. " - " . $myrow["Description"]. "</a></td>";
    		echo "<td>" . $myrow["WorkCentreAdded"]. "</td>";
    		echo "<td>" . $myrow["LocCode"]. "</td>";
    		echo "<td>" . $myrow["Quantity"]. "</td>";
    		echo "<td>" . ConvertSQLDate($myrow["EffectiveAfter"]) . "</td>";
    		echo "<td>" . ConvertSQLDate($myrow["EffectiveTo"]) . "</td>";

    		$j++;
    		If ($j == 12){
    			$j=1;
    			echo $tableheader;
    		}
    		//end of page full new headings if
    	}

    	echo "</TABLE>";
   }
} // StockID is set

echo "</FORM>";

include("includes/footer.inc");

?>
