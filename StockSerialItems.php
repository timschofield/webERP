<?php
/* $Revision: 1.2 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Stock Of Controlled Items');
include('includes/header.inc');


if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} else {
	prnMsg( _('This page must be called with parameters specifying the item to show the serial references and quantities. It cannot be displayed without the proper parameters being passed'),'error');
	include('includes/footer.inc');
	exit;
}

$result = DB_query("SELECT Description, Units, MBflag, DecimalPlaces, Serialised, Controlled FROM StockMaster WHERE StockID='$StockID'",$db, _('Could not retrieve the requested item because'),_('The SQL used to retrieve the items was'));

$myrow = DB_fetch_row($result);

$DecimalPlaces = $myrow[3];
$Serialised = $myrow[4];
$Controlled = $myrow[5];

echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (" . _('In units of') . ' ' . $myrow[1] . ')</FONT>';

if ($myrow[2]=='K' OR $myrow[2]=='A' OR $myrow[2]=='D'){

	prnMsg(_('This item is either a kitset/assembly or dummy part and cannot have a stock holding. This page cannot be displayed. Only serialised or controlled items can be displayed in this page'),'error');
	include('includes/footer.inc');
	exit;
}

if ($Serialised==1){
	echo '<BR><B>' . _('Serialised items in') . ' ';
} else {
	echo '<BR><B>' . _('Controlled items in') . ' ';
}


$result = DB_query("SELECT LocationName FROM Locations WHERE LocCode='" . $_GET['Location'] . "'",$db,_('Could not retrieve the stock location of the item because'),_('The SQL used to lookup the location was'));

$myrow = DB_fetch_row($result);
echo $myrow[0];

$sql = "SELECT SerialNo,
		Quantity
	FROM StockSerialItems
	WHERE LocCode='" . $_GET['Location'] . "'
	AND StockID = '" . $StockID . "'
	AND Quantity <>0";


$ErrMsg = _('The serial numbers/batches held cannot be retrieved because');
$LocStockResult = DB_query($sql, $db, $ErrMsg);

echo '<CENTER><TABLE CELLPADDING=2 BORDER=0>';

if ($Serialised == 1){
	$tableheader = "<TR>
			<TD class='tableheader'>" . _('Serial Number') . "</TD>
			<TD class='tableheader'>" . _('Serial Number') . "</TD>
			<TD class='tableheader'>" . _('Serial Number') . "</TD>
			</TR>";
} else {
	$tableheader = "<TR>
			<TD class='tableheader'>" . _('Batch/Bundle Ref') . "</TD>
			<TD class='tableheader'>" . _('Quantity On Hand') . "</TD>
			<TD class='tableheader'>" . _('Batch/Bundle Ref') . "</TD>
			<TD class='tableheader'>" . _('Quantity On Hand') . "</TD>
   			<TD class='tableheader'>" . _('Batch/Bundle Ref') . "</TD>
			<TD class='tableheader'>" . _('Quantity On Hand') . "</TD>

   			</TR>";
}
echo $tableheader;
$TotalQuantity =0;
$j = 1;
$Col =0;
while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($Col==0 AND $BGColor=='#EEEEEE'){
		$BGColor ='#CCCCCC';
		echo "<TR bgcolor=$BGColor>";
	} elseif ($Col==0){
		$BGColor ='#EEEEEE';
		echo "<TR bgcolor=$BGColor>";
	}

	$TotalQuantity += $myrow['Quantity'];

	if ($Serialised == 1){
		printf('<td>%s</td>',
		$myrow['SerialNo']
		);
	} else {
		printf("<td>%s</td>
			<td ALIGN=RIGHT>%s</td>",
			$myrow['SerialNo'],
			number_format($myrow['Quantity'],$DecimalPlaces)
			);
	}
	$j++;
	If ($j == 36){
		$j=1;
		echo $tableheader;
	}
//end of page full new headings if
	$Col++;
	if ($Col==3){
		echo '</TR>';
		$Col=0;
	}
}
//end of while loop

echo '</TABLE><HR>';
echo '<BR><B>' . _('Total quantity') . ': ' . number_format($TotalQuantity, $DecimalPlaces) . '<BR>';

echo '</form>';
include('includes/footer.inc');

?>