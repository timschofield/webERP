<?php
/* $Revision: 1.3 $ */

$PageSecurity = 4;

include('includes/session.inc');
$title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');
include('includes/DateFunctions.inc');

if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
}

$result = DB_query("SELECT Description, Units FROM StockMaster WHERE StockID='$StockID'", $db);
$myrow = DB_fetch_row($result);

echo '<BR><FONT COLOR=BLUE SIZE=3><B>' . $StockID . ' - ' . $myrow[0] . '</B>  (' . _('In Units of') . ' ' . $myrow[1] . ')</FONT>';
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Stock Code') . ":<INPUT TYPE=TEXT NAME='StockID' SIZE=21 VALUE='$StockID' MAXLENGTH=20>";
echo "     <INPUT TYPE=SUBMIT NAME='Show' VALUE='" . _('Show Re-Order Levels') . "'><HR>";

$sql = "SELECT LocStock.LocCode,
		Locations.LocationName,
		LocStock.Quantity,
		LocStock.ReorderLevel
	FROM LocStock,
		Locations
	WHERE LocStock.LocCode=Locations.LocCode
	AND LocStock.StockID = '" . $StockID . "'
	ORDER BY LocStock.LocCode";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');

$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo "<CENTER><TABLE CELLPADDING=2 BORDER=2>";

$TableHeader = "<TR>
		<TD CLASS='tableheader'>" . _('Location') . "</TD>
		<TD CLASS='tableheader'>" . _('Quantity On Hand') . "</TD>
		<TD CLASS='tableheader'>" . _('Re-Order Level') . "</TD>
		</TR>";

echo $TableHeader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo "<TR BGCOLOR='#CCCCCC'>";
		$k=0;
	} else {
		echo "<TR BGCOLOR='#EEEEEE'>";
		$k=1;
	}

	if (isset($_POST['UpdateData']) AND is_numeric($_POST[$myrow['LocCode']]) AND $_POST[$myrow['LocCode']]>0){

	   $myrow['ReorderLevel'] = $_POST[$myrow['LocCode']];
	   $sql = 'UPDATE LocStock SET ReorderLevel = ' . $_POST[$myrow['LocCode']] . "
	   		WHERE StockID = '" . $StockID . "'
			AND LocCode = '"  . $myrow['LocCode'] ."'";
	   $UpdateReorderLevel = DB_query($sql, $db);

	}

	printf("<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD><INPUT TYPE=TEXT NAME=%s MAXLENGTH=10 SIZE=10 VALUE=%s></TD>",
		$myrow['LocationName'],
		number_format($myrow['Quantity']),
		$myrow['LocCode'],
		$myrow['ReorderLevel']);
	$j++;
	If ($j == 12){
		$j=1;
		echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE><INPUT TYPE=SUBMIT NAME='UpdateData' VALUE='" . _('Update') . "'><HR>";
echo "<A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>" . _('Show Stock Movements') . '</A>';
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "StockID=$StockID'>" . _('Show Stock Usage') . '</A>';
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID'>" . _('Search Outstanding Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</A>';

echo '</FORM>';
include('includes/footer.inc');
?>