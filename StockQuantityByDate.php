<?php

/* $Revision: 1.2 $ */
/* Contributed by Chris Bice */

$title = _("Stock On Hand By Date");

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

echo "<HR><FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID . "' METHOD=POST>";

$sql = "SELECT CategoryID, CategoryDescription FROM StockCategory";
$resultStkLocs = DB_query($sql,$db);

echo "<CENTER><TABLE><TR>";
echo "<TD>" . _('For Stock Category:') . "</TD><TD><SELECT name='StockCategory'> ";
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockCategory']) AND $_POST['StockCategory']!='All'){
		if ($myrow["CategoryID"] == $_POST['StockCategory']){
		     echo "<OPTION SELECTED Value='" . $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
		} else {
		     echo "<OPTION Value='" . $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
		}
	}else {
		 echo "<OPTION Value='" . $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	}
}
echo "</SELECT></TD>";

$sql = "SELECT LocCode, LocationName FROM Locations";
$resultStkLocs = DB_query($sql,$db);
echo "<TD>" . _('For Stock Location:') . "</TD><TD><SELECT name='StockLocation'> ";
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
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
echo "</SELECT></TD>";
if (!isset($_POST['OnHandDate'])){
	$_POST['OnHandDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m"),0,Date("y")));
}
echo "<TD>" . _("On-Hand On Date:") . "</TD><TD><INPUT TYPE=TEXT NAME='OnHandDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['OnHandDate'] . "'></TD>
	</TR>";
echo "<TR><TD COLSPAN=6 ALIGN=CENTER><INPUT TYPE=SUBMIT NAME='ShowStatus' VALUE='" . _('Show Stock Status') ."'></TD></TR></TABLE>";

echo "</form><HR>";

$TotalQuantity = 0;

if(isset($_POST['ShowStatus']) AND is_date($_POST['OnHandDate']))
{
	$sql = "SELECT StockID,
			Description,
			DecimalPlaces
		FROM StockMaster
		WHERE CategoryID = '" . $_POST['StockCategory'] . "'
		AND (MBflag='M' OR MBflag='B')";

	$ErrMsg = _("The stock items in the category selected cannot be retrieved because");
	$DbgMsg = _("The SQL that failed was");
	$StockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$SQLOnHandDate = FormatDateForSQL($_POST['OnHandDate']);

	echo "<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>";
	$tableheader = "<TR>
				<TD class='tableheader'>" . _('Item Code') . "</TD>
				<TD class='tableheader'>" . _('Description') . "</TD>
				<TD class='tableheader'>" . _('Quantity On Hand') . "</TD></TR>";
	echo $tableheader;

	while ($myrows=DB_fetch_array($StockResult)) {

		$sql = "SELECT StockID,
				NewQOH
			FROM StockMoves
			WHERE StockMoves.TranDate <= '". $SQLOnHandDate . "' AND StockID = '" . $myrows['StockID'] . "' AND LocCode = '" . $_POST['StockLocation'] ."' ORDER BY StkMoveNo DESC Limit 1";

		$ErrMsg =  _("The stock held as at") . $_POST['OnHandDate'] . " " . _("could not be retrieved because");				$LocStockResult = DB_query($sql, $db,$ErrMsg);

		$NumRows = DB_num_rows($LocStockResult, $db);

		$j = 1;
		$k=0; //row colour counter

		while ($LocQtyRow=DB_fetch_array($LocStockResult)) {

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k=1;
			}

			if($NumRows == 0){
				printf("<td><a target='_blank' href='StockStatus.php?StockID=%s'>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td>", strtoupper($myrows["StockID"]),strtoupper($myrows["StockID"]),$myrows['Description'], 0);
			} else {
				printf("<td><a target='_blank' href='StockStatus.php?StockID=%s'>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td>", strtoupper($myrows["StockID"]),strtoupper($myrows["StockID"]),$myrows['Description'], number_format($LocQtyRow["NewQOH"],$myrows['DecimalPlaces']));
				$TotalQuantity += $LocQtyRow['NewQOH'];
			}
			$j++;
			if ($j == 12){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		}

	}//end of while loop
	echo "<TR><TD>" . _('Total Quantity:') . " " . $TotalQuantity . "</TD></TR></TABLE>";
}

include("includes/footer.inc");
?>
