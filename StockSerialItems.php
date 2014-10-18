<?php
/* $Id: StockSerialItems.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Stock Of Controlled Items');
include('includes/header.inc');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') .
'" alt="" /><b>' . $Title. '</b>
	</p>';

if (isset($_GET['StockID'])){
	if (ContainsIllegalCharacters ($_GET['StockID'])){
		prnMsg(_('The stock code sent to this page appears to be invalid'),'error');
		include('includes/footer.inc');
		exit;
	}
	$StockID = trim(mb_strtoupper($_GET['StockID']));
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
							controlled,
							perishable
						FROM stockmaster
						WHERE stockid='".$StockID."'",
						$db,
						_('Could not retrieve the requested item because'));

$myrow = DB_fetch_array($result);

$Description = $myrow['description'];
$UOM = $myrow['units'];
$DecimalPlaces = $myrow['decimalplaces'];
$Serialised = $myrow['serialised'];
$Controlled = $myrow['controlled'];
$Perishable = $myrow['perishable'];

if ($myrow['mbflag']=='K' OR $myrow['mbflag']=='A' OR $myrow['mbflag']=='D'){

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
				quantity,
				expirationdate
			FROM stockserialitems
			WHERE loccode='" . $_GET['Location'] . "'
			AND stockid = '" . $StockID . "'
			AND quantity <>0";


$ErrMsg = _('The serial numbers/batches held cannot be retrieved because');
$LocStockResult = DB_query($sql, $db, $ErrMsg);

echo '<table class="selection">';

if ($Serialised==1){
	echo '<tr><th colspan="5"><font color="navy" size="2">' . _('Serialised items in') . ' ';
} else {
	echo '<tr><th colspan="11"><font color="navy" size="2">' . _('Controlled items in') . ' ';
}
echo $myrow[0]. '</font></th></tr>';

echo '<tr>
		<th colspan="11"><font color="navy" size="2">' . $StockID .'-'. $Description  . '</b>  (' . _('In units of') . ' ' . $UOM . ')</font></th>
	</tr>';

if ($Serialised == 1 and $Perishable==0){
	$tableheader = '<tr>
						<th>' . _('Serial Number') . '</th>
						<th></th>
						<th>' . _('Serial Number') . '</th>
						<th></th>
						<th>' . _('Serial Number') . '</th>
					</tr>';
} else if ($Serialised == 1 and $Perishable==1){
	$tableheader = '<tr>
			<th>' . _('Serial Number') . '</th>
			<th>' . _('Expiry Date') . '</th>
			<th>' . _('Serial Number') . '</th>
			<th>' . _('Expiry Date') . '</th>
			<th>' . _('Serial Number') . '</th>
			<th>' . _('Expiry Date') . '</th>
			</tr>';
} else if ($Serialised == 0 and $Perishable==0){
	$tableheader = '<tr>
						<th>' . _('Batch/Bundle Ref') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
						<th></th>
						<th>' . _('Batch/Bundle Ref') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
						<th></th>
						<th>' . _('Batch/Bundle Ref') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
					</tr>';
} else if ($Serialised == 0 and $Perishable==1){
	$tableheader = '<tr>
						<th>' . _('Batch/Bundle Ref') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
						<th>' . _('Expiry Date') . '</th>
						<th></th>
						<th>' . _('Batch/Bundle Ref') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
						<th>' . _('Expiry Date') . '</th>
						<th></th>
			   			<th>' . _('Batch/Bundle Ref') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
						<th>' . _('Expiry Date') . '</th>
			   		</tr>';
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

	if ($Serialised == 1 and $Perishable==0){
		echo '<td>' . $myrow['serialno'] . '</td>';
		echo '<th></th>';
	} else if ($Serialised == 1 and $Perishable==1) {
		echo '<td>' . $myrow['serialno'] . '</td>
				<td>' . ConvertSQLDate($myrow['expirationdate']). '</td>';
	} else if ($Serialised == 0 and $Perishable==0) {
		echo '<td>' . $myrow['serialno'] . '</td>
			<td class="number">' . locale_number_format($myrow['quantity'],$DecimalPlaces) . '</td>';
		echo '<th></th>';
	} else if ($Serialised == 0 and $Perishable==1){
		echo '<td>' . $myrow['serialno'] . '</td>
			<td class="number">' . locale_number_format($myrow['quantity'],$DecimalPlaces). '</td>
			<td>' . ConvertSQLDate($myrow['expirationdate']). '</td>
			<th></th>';
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
echo '<div class="centre"><br /><b>' . _('Total quantity') . ': ' . locale_number_format($TotalQuantity, $DecimalPlaces) . '<br /></div>';

echo '</form>';
include('includes/footer.inc');
?>
