<?php

/* $Revision: 1.11 $ */


$PageSecurity = 4;

include('includes/session.inc');
$title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}


echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</a><br>';

$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='$StockID'", $db);
$myrow = DB_fetch_row($result);

echo '<div class="centre"><br><font color=BLUE size=3><b>' . $StockID . ' - ' . $myrow[0] . '</b>  (' . _('In Units of') . ' ' . $myrow[1] . ')</font>';
echo "<form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";
echo _('Stock Code') . ":<input type=TEXT name='StockID' size=21 VALUE='$StockID' maxlength=20>";
echo "     <input type=submit name='Show' VALUE='" . _('Show Re-Order Levels') . "'><hr></div>";

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

echo "<table cellpadding=2 BORDER=2>";

$TableHeader = "<tr>
		<th>" . _('Location') . "</th>
		<th>" . _('Quantity On Hand') . "</th>
		<th>" . _('Re-Order Level') . "</th>
		</tr>";

echo $TableHeader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	if (isset($_POST['UpdateData']) AND is_numeric($_POST[$myrow['loccode']]) AND $_POST[$myrow['loccode']]>=0){

	   $myrow['reorderlevel'] = $_POST[$myrow['loccode']];
	   $sql = 'UPDATE locstock SET reorderlevel = ' . $_POST[$myrow['loccode']] . "
	   		WHERE stockid = '" . $StockID . "'
			AND loccode = '"  . $myrow['loccode'] ."'";
	   $UpdateReorderLevel = DB_query($sql, $db);

	}

	printf("<td>%s</td>
		<td align=right>%s</td>
		<td><input type=TEXT class='number' name=%s maxlength=10 size=10 VALUE=%s></td>",
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

echo "</table><div class='centre'><input type=submit name='UpdateData' VALUE='" . _('Update') . "'><hr>";
echo "<a href='$rootpath/StockMovements.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Movements') . '</a>';
echo "<br><a href='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Usage') . '</a>';
echo "<br><a href='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Outstanding Sales Orders') . '</a>';
echo "<br><a href='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</a>';

echo '</div></form>';
include('includes/footer.inc');
?>