<?php

$title = "Costed Bill Of Material";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";
echo "Item Code:<input type=text name='StockID' size=21 value='$StockID' maxlength=20>";
echo " <INPUT TYPE=SUBMIT NAME='ShowBOM' VALUE='Show Bill Of Material'><HR>";

if ($StockID!=""){
	$result = DB_query("SELECT Description, Units FROM StockMaster WHERE StockID='" . $StockID  . "'",$db);
	$myrow = DB_fetch_row($result);

	echo "<BR><FONT SIZE=4><B>" . $myrow[0] . " : Per " . $myrow[1] . "</B></FONT>";

	$sql = "SELECT BOM.Parent, BOM.Component, StockMaster.Description, StockMaster.MaterialCost+ StockMaster.LabourCost+StockMaster.OverheadCost AS StandardCost, BOM.Quantity, BOM.Quantity * (StockMaster.MaterialCost+ StockMaster.LabourCost+ StockMaster.OverheadCost) AS ComponentCost FROM BOM INNER JOIN StockMaster ON BOM.Component = StockMaster.StockID WHERE BOM.Parent = '" . $StockID . "' AND BOM.EffectiveAfter < Now() AND BOM.EffectiveTo > Now()";
	$BOMResult = DB_query ($sql,$db);

	if (DB_error_no($db) !=0) {	echo "<P>The bill of material could not be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
			   echo "<BR>The SQL that failed was $sql";
		}
		include("includes/footer.inc");
		exit;
	}
	if (DB_num_rows($BOMResult)==0){
		echo "<P>The bill of material for this part is not set up - there are no components defined for it.";
	} else {

		echo "<TABLE CELLPADDING=2 BORDER=2>";
		$TableHeader = "<TR><TD class=tableheader>Component</TD><TD class=tableheader>Description</TD><TD class=tableheader>Quantity</TD><TD class=tableheader>Unit Cost</TD><TD class=tableheader>Total Cost</TD></TR>";

		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter

		$TotalCost = 0;

		while ($myrow=DB_fetch_array($BOMResult)) {

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k=1;
			}

			$ComponentLink = "<A HREF='$rootpath/SelectProduct.php?" . SID . "StockID=" . $myrow['Component'] . "'>" . $myrow['Component'] . "</A>";

			/* Component Code  Description                 Quantity            Std Cost*                Total Cost */
			printf("<td>%s</td><td>%s</td><td ALIGN=RIGHT>%.2f</td><td ALIGN=RIGHT>%.2f</td><td ALIGN=RIGHT>%.2f</td></tr>", $ComponentLink, $myrow["Description"], $myrow["Quantity"], $myrow["StandardCost"], $myrow["ComponentCost"]);

			$TotalCost += $myrow["ComponentCost"];

			$j++;
			If ($j == 12){
				$j=1;
				echo $TableHeader;
			}//end of page full new headings if}//end of while
		}

		echo "<TR><TD COLSPAN=4 ALIGN=RIGHT><B>Total Cost</B></TD><TD ALIGN=RIGHT><B>" . number_format($TotalCost,2) . "</B></TD></TR>";

		echo "</TABLE>";
	}
} else { //no stock item entered
	echo "<P>Enter a stock item code above, to view the costed bill of material for.";
}
echo "</form>";
include("includes/footer.inc");
?>
