<?php

/* $Revision: 1.8 $ */


$PageSecurity = 4;

include('includes/session.inc');
$title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}


echo "<A HREF='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</A><BR>';

$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='$StockID'", $db);
$myrow = DB_fetch_row($result);

echo '<BR><FONT COLOR=BLUE SIZE=3><B>' . $StockID . ' - ' . $myrow[0] . '</B>  (' . _('In Units of') . ' ' . $myrow[1] . ')</FONT>';
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Stock Code') . ":<INPUT TYPE=TEXT NAME='StockID' SIZE=21 VALUE='$StockID' MAXLENGTH=20>";
echo "     <INPUT TYPE=SUBMIT NAME='Show' VALUE='" . _('Show Re-Order Levels') . "'><HR>";

$sql = "SELECT locstock.loccode,
		locations.locationname,
		locstock.quantity,
		locstock.reorderlevel
	FROM locstock,
		locations
	WHERE locstock.loccode=locations.loccode
	AND locstock.stockid = '" . $StockID . "'
	ORDER BY locstock.loccode";

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

	if (isset($_POST['UpdateData']) AND is_numeric($_POST[$myrow['loccode']]) AND $_POST[$myrow['loccode']]>0){

	   $myrow['reorderlevel'] = $_POST[$myrow['loccode']];
	   $sql = 'UPDATE locstock SET reorderlevel = ' . $_POST[$myrow['loccode']] . "
	   		WHERE stockid = '" . $StockID . "'
			AND loccode = '"  . $myrow['loccode'] ."'";
	   $UpdateReorderLevel = DB_query($sql, $db);

	}

	printf("<TD>%s</TD>
		<TD ALIGN=RIGHT>%s</TD>
		<TD><INPUT TYPE=TEXT NAME=%s MAXLENGTH=10 SIZE=10 VALUE=%s></TD>",
		$myrow['locationname'],
		number_format($myrow['quantity']),
		$myrow['loccode'],
		$myrow['reorderlevel']);
	$j++;
	If ($j == 12){
		$j=1;
		echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE><INPUT TYPE=SUBMIT NAME='UpdateData' VALUE='" . _('Update') . "'><HR>";
echo "<A HREF='$rootpath/StockMovements.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Movements') . '</A>';
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Usage') . '</A>';
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Outstanding Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</A>';

echo '</FORM>';
include('includes/footer.inc');
?>