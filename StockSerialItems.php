<?php
/* $Id$*/

//$PageSecurity = 2;

include('includes/session.inc');
$title = _('Stock Of Controlled Items');
include('includes/header.inc');

echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') .
'" alt="" /><b>' . $title. '</b></p>';

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
		WHERE stockid='".$StockID."'",
		$db,
		_('Could not retrieve the requested item because'));

$myrow = DB_fetch_row($result);

$Description = $myrow[0];
$UOM = $myrow[1];
$DecimalPlaces = $myrow[3];
$Serialised = $myrow[4];
$Controlled = $myrow[5];

if ($myrow[2]=='K' OR $myrow[2]=='A' OR $myrow[2]=='D'){

	prnMsg(_('This item is either a kitset or assembly or a dummy part and cannot have a stock holding') . '. ' . _('This page cannot be displayed') . '. ' . _('Only serialised or controlled items can be displayed in this page'),'error');
	include('includes/footer.inc');
	exit;
}

$result = DB_query("SELECT locationname
			FROM locations
			WHERE loccode='" . $_GET['Location'] . "'",
			$db,
			_('Could not retrieve the stock location of the item because'),
			_('The SQL used to lookup the location was'));

$myrow = DB_fetch_row($result);

$sql = "SELECT serialno,
		quantity
	FROM stockserialitems
	WHERE loccode='" . $_GET['Location'] . "'
	AND stockid = '" . $StockID . "'
	AND quantity <>0";


$ErrMsg = _('The serial numbers/batches held cannot be retrieved because');
$LocStockResult = DB_query($sql, $db, $ErrMsg);

echo '<table cellpadding=2 class=selection>';

if ($Serialised==1){
	echo '<tr<th colspan=3><font color=navy size=2>' . _('Serialised items in') . ' ';
} else {
	echo '<tr<th colspan=3><font color=navy size=2>' . _('Controlled items in') . ' ';
}
echo $myrow[0]. '</font></th></tr>';

echo "<tr><th colspan=3><font color=navy size=2>".$StockID ."-". $Description ."</b>  (" . _('In units of') . ' ' . $UOM . ')</font></th></tr>';

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
			<td class=number>%s</td>",
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

echo '</table><br />';
echo '<div class="centre"><br><b>' . _('Total quantity') . ': ' . number_format($TotalQuantity, $DecimalPlaces) . '<br></div>';

echo '</form>';
include('includes/footer.inc');

?>