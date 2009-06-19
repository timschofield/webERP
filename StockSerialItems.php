<?php
/* $Revision: 1.8 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Stock Of Controlled Items');
include('includes/header.inc');


if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} else {
	prnMsg( _('This page must be called with parameters specifying the item to show the serial references and quantities') . '. ' . _('It cannot be displayed without the proper parameters being passed'),'error');
	include('includes/footer.inc');
	exit;
}

$result = DB_query("SELECT description,
			units,
			mbflag,
			decimalplaces,
			serialised,
			controlled
		FROM stockmaster
		WHERE stockid='$StockID'",
		$db,
		_('Could not retrieve the requested item because'));

$myrow = DB_fetch_row($result);

$DecimalPlaces = $myrow[3];
$Serialised = $myrow[4];
$Controlled = $myrow[5];

echo "<br><font color=BLUE size=3><b>$StockID - $myrow[0] </b>  (" . _('In units of') . ' ' . $myrow[1] . ')</font>';

if ($myrow[2]=='K' OR $myrow[2]=='A' OR $myrow[2]=='D'){

	prnMsg(_('This item is either a kitset or assembly or a dummy part and cannot have a stock holding') . '. ' . _('This page cannot be displayed') . '. ' . _('Only serialised or controlled items can be displayed in this page'),'error');
	include('includes/footer.inc');
	exit;
}

if ($Serialised==1){
	echo '<br><b>' . _('Serialised items in') . ' ';
} else {
	echo '<br><b>' . _('Controlled items in') . ' ';
}


$result = DB_query("SELECT locationname
			FROM locations
			WHERE loccode='" . $_GET['Location'] . "'",
			$db,
			_('Could not retrieve the stock location of the item because'),
			_('The SQL used to lookup the location was'));

$myrow = DB_fetch_row($result);
echo $myrow[0];

$sql = "SELECT serialno,
		quantity
	FROM stockserialitems
	WHERE loccode='" . $_GET['Location'] . "'
	AND stockid = '" . $StockID . "'
	AND quantity <>0";


$ErrMsg = _('The serial numbers/batches held cannot be retrieved because');
$LocStockResult = DB_query($sql, $db, $ErrMsg);

echo '<table cellpadding=2 BORDER=0>';

if ($Serialised == 1){
	$tableheader = "<tr>
			<th>" . _('Serial Number') . "</th>
			<th>" . _('Serial Number') . "</th>
			<th>" . _('Serial Number') . "</th>
			</tr>";
} else {
	$tableheader = "<tr>
			<th>" . _('Batch/Bundle Ref') . "</th>
			<th>" . _('Quantity On Hand') . "</th>
			<th>" . _('Batch/Bundle Ref') . "</th>
			<th>" . _('Quantity On Hand') . "</th>
   			<th>" . _('Batch/Bundle Ref') . "</th>
			<th>" . _('Quantity On Hand') . "</th>

   			</tr>";
}
echo $tableheader;
$TotalQuantity =0;
$j = 1;
$Col =0;
$BGColor ='#CCCCCC';
while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($Col==0 AND $BGColor=='#EEEEEE'){
		$BGColor ='#CCCCCC';
		echo '<tr class="EvenTableRows">';
	} elseif ($Col==0){
		$BGColor ='#EEEEEE';
		echo '<tr class="OddTableRows">';
	}

	$TotalQuantity += $myrow['quantity'];

	if ($Serialised == 1){
		printf('<td>%s</td>',
		$myrow['serialno']
		);
	} else {
		printf("<td>%s</td>
			<td align=right>%s</td>",
			$myrow['serialno'],
			number_format($myrow['quantity'],$DecimalPlaces)
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
		echo '</tr>';
		$Col=0;
	}
}
//end of while loop

echo '</table><hr>';
echo '<div class="centre"><br><b>' . _('Total quantity') . ': ' . number_format($TotalQuantity, $DecimalPlaces) . '<br></div>';

echo '</form>';
include('includes/footer.inc');

?>